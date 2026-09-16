<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ERP AI — MarsERP</title>
    <link rel="icon" href="{{ asset('images/marserp-favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/ai-chat.css') }}">
</head>
<body>
    <div class="app">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="{{ asset('images/marserp-icon-256.png') }}" alt="MarsERP">
                <span>ERP AI</span>
            </div>

            <button type="button" id="new-chat-btn" class="new-chat-btn">+ Percakapan Baru</button>

            <nav id="conversation-list" class="conversation-list">
                @forelse ($conversations as $conversation)
                    <a
                        href="{{ route('ai.conversations.show', $conversation) }}"
                        class="conversation-item {{ $activeConversation && $activeConversation->id === $conversation->id ? 'active' : '' }}"
                    >{{ $conversation->title ?: 'Percakapan #'.$conversation->id }}</a>
                @empty
                    <span style="color:#64748B; font-size:0.8rem; padding: 0.5rem 0.75rem;">Belum ada percakapan.</span>
                @endforelse
            </nav>

            <div class="sidebar-footer">
                <a href="{{ url('/admin') }}">&larr; Kembali ke MarsERP Admin</a>
            </div>
        </aside>

        <main class="main">
            <header class="main-header">
                <h1>{{ $activeConversation->title ?? 'ERP AI Assistant' }}</h1>
                <span class="provider-badge">
                    {{ \App\Models\Ai\AiProvider::active()?->name ?? 'Tidak ada provider aktif' }}
                </span>
            </header>

            <div id="messages" class="messages">
                @forelse ($messages as $message)
                    <div class="msg {{ $message->role }}">{{ $message->content }}</div>
                @empty
                    <div class="empty-state">
                        <h2>Mulai percakapan</h2>
                        <p>Tanyakan apa saja ke AI Assistant MarsERP. Balasan akan muncul secara langsung (streaming).</p>
                    </div>
                @endforelse
            </div>

            <div class="composer">
                <div id="composer-error" class="composer-error"></div>
                <form id="composer-form" class="composer-form">
                    <textarea
                        id="composer-input"
                        rows="1"
                        placeholder="Tulis pesan… (Enter untuk kirim, Shift+Enter baris baru)"
                        @if (! $hasActiveProvider) disabled @endif
                    ></textarea>
                    <button type="submit" id="send-btn" class="send-btn" aria-label="Kirim">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                    </button>
                </form>
                @unless ($hasActiveProvider)
                    <div style="margin-top:0.5rem; font-size:0.78rem; color:#B91C1C;">
                        Belum ada AI Provider yang aktif. Atur salah satu di Administration &rarr; AI Providers.
                    </div>
                @endunless
            </div>
        </main>
    </div>

    <script>
        window.__AI_CHAT__ = {
            conversationId: @json($activeConversation->id ?? null),
            hasActiveProvider: @json($hasActiveProvider),
        };
    </script>
    <script src="{{ asset('js/ai-chat.js') }}"></script>
</body>
</html>
