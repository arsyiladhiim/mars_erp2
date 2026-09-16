<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Models\Ai\AiConversation;
use App\Models\Ai\AiProvider;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\StreamHandler;
use GuzzleHttp\HandlerStack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Full-page "ERP AI" chat (own layout, outside the Filament panel). Generic
 * chat only — no ERP data/tool-calling yet (see docs/PRD.md §28, §52; that
 * integration is deferred to the backend-hardening phase).
 */
class ChatController extends Controller
{
    public function index()
    {
        return view('ai.chat', [
            'conversations' => Auth::user()->aiConversations()->latest()->get(),
            'activeConversation' => null,
            'messages' => collect(),
            'hasActiveProvider' => AiProvider::active() !== null,
        ]);
    }

    public function show(AiConversation $conversation)
    {
        abort_unless($conversation->user_id === Auth::id(), 403);

        return view('ai.chat', [
            'conversations' => Auth::user()->aiConversations()->latest()->get(),
            'activeConversation' => $conversation,
            'messages' => $conversation->messages,
            'hasActiveProvider' => AiProvider::active() !== null,
        ]);
    }

    public function store(Request $request)
    {
        $provider = AiProvider::active();

        if (! $provider) {
            return response()->json([
                'message' => 'Belum ada AI Provider yang aktif. Atur salah satu di Administration → AI Providers.',
            ], 422);
        }

        $conversation = Auth::user()->aiConversations()->create([
            'ai_provider_id' => $provider->id,
        ]);

        return response()->json([
            'id' => $conversation->id,
            'title' => $conversation->title,
        ]);
    }

    public function streamMessage(Request $request, AiConversation $conversation): StreamedResponse
    {
        abort_unless($conversation->user_id === Auth::id(), 403);

        $data = $request->validate(['content' => 'required|string|max:8000']);

        $provider = $conversation->aiProvider ?? AiProvider::active();

        abort_if(! $provider, 422, 'No active AI provider configured.');

        $conversation->messages()->create(['role' => 'user', 'content' => $data['content']]);

        if (blank($conversation->title)) {
            $conversation->update(['title' => str($data['content'])->limit(60)->toString()]);
        }

        $history = $conversation->messages()
            ->orderBy('id')
            ->get(['role', 'content'])
            ->map(fn ($m) => ['role' => $m->role, 'content' => $m->content])
            ->all();

        return response()->stream(function () use ($provider, $history, $conversation) {
            $assistantBuffer = '';

            // A dedicated Guzzle client on the PHP-streams handler (not cURL) is required
            // here: Guzzle's default cURL handler's `curl_exec()` blocks until the ENTIRE
            // upstream response is received even when the "stream" request option is set,
            // which defeats token-by-token proxying. StreamHandler opens a real PHP stream
            // that ->read() can drain incrementally as bytes actually arrive.
            $client = new GuzzleClient(['handler' => HandlerStack::create(new StreamHandler())]);

            $headers = ['Accept' => 'text/event-stream'];

            if (filled($provider->api_key)) {
                $headers['Authorization'] = 'Bearer '.$provider->api_key;
            }

            try {
                $response = $client->post(rtrim($provider->base_url, '/').'/chat/completions', [
                    'headers' => $headers,
                    'json' => [
                        'model' => $provider->default_model,
                        'messages' => $history,
                        'stream' => true,
                    ],
                    'stream' => true,
                    'timeout' => 120,
                    'connect_timeout' => 10,
                ]);
            } catch (\Throwable $e) {
                echo 'data: '.json_encode(['error' => $e->getMessage()])."\n\n";
                $this->flushOutput();

                return;
            }

            $body = $response->getBody();
            $buffer = '';
            $done = false;

            while (! $body->eof() && ! $done) {
                $chunk = $body->read(1024);

                if ($chunk === '') {
                    continue;
                }

                $buffer .= $chunk;

                while (($pos = strpos($buffer, "\n\n")) !== false) {
                    $rawEvent = substr($buffer, 0, $pos);
                    $buffer = substr($buffer, $pos + 2);

                    foreach (explode("\n", $rawEvent) as $line) {
                        $line = trim($line);

                        if (! str_starts_with($line, 'data:')) {
                            continue;
                        }

                        $payload = trim(substr($line, 5));

                        if ($payload === '[DONE]') {
                            $done = true;

                            break;
                        }

                        $json = json_decode($payload, true);
                        $delta = $json['choices'][0]['delta']['content'] ?? '';
                        $assistantBuffer .= $delta;

                        echo "data: {$payload}\n\n";
                    }

                    $this->flushOutput();

                    if ($done) {
                        break;
                    }
                }
            }

            if ($assistantBuffer !== '') {
                $conversation->messages()->create(['role' => 'assistant', 'content' => $assistantBuffer]);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            // Prevents the docker-compose nginx container from buffering the whole
            // response before sending it — without this, streaming silently breaks.
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function flushOutput(): void
    {
        if (ob_get_level() > 0) {
            @ob_flush();
        }

        @flush();
    }
}
