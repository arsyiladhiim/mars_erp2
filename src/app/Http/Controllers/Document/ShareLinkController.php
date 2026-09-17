<?php

namespace App\Http\Controllers\Document;

use App\Http\Controllers\Controller;
use App\Models\Document\ShareLink;
use App\Models\Document\ShareLinkAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Public (unauthenticated) endpoint for a Document's share link. Handles the
 * common case only: an unprotected, unexpired, non-revoked link is viewed or
 * downloaded and the access is logged. Password-protected and
 * signature-required links need a real form/session flow that doesn't exist
 * yet — those are rejected with a clear message rather than silently
 * bypassing the protection.
 */
class ShareLinkController extends Controller
{
    public function show(Request $request, string $token): Response
    {
        $link = ShareLink::where('token', $token)->firstOrFail();

        if ($link->is_revoked) {
            abort(403, 'This share link has been revoked.');
        }

        if ($link->isExpired()) {
            abort(403, 'This share link has expired.');
        }

        if ($link->password_hash) {
            abort(501, 'Password-protected share links are not yet supported.');
        }

        $document = $link->document;

        if (! $document || ! Storage::exists($document->file_path)) {
            abort(404, 'The shared file could not be found.');
        }

        $action = $link->allow_download ? 'download' : 'view';

        ShareLinkAccess::create([
            'share_link_id' => $link->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'action' => $action,
        ]);

        return $link->allow_download
            ? Storage::download($document->file_path, $document->title)
            : Storage::response($document->file_path);
    }
}
