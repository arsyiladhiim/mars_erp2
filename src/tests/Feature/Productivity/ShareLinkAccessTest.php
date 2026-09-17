<?php

namespace Tests\Feature\Productivity;

use App\Models\Document\Document;
use App\Models\Document\ShareLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShareLinkAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function makeDocumentAndLink(array $linkOverrides = []): ShareLink
    {
        Storage::fake();
        Storage::put('documents/report.pdf', 'fake pdf contents');

        $document = Document::create([
            'title' => 'Report.pdf', 'document_type' => 'report',
            'file_path' => 'documents/report.pdf', 'attachable_type' => 'App\\Models\\Core\\Company', 'attachable_id' => 1,
        ]);

        return ShareLink::create(array_merge(['document_id' => $document->id], $linkOverrides));
    }

    public function test_a_valid_view_only_link_is_viewable_and_logs_an_access(): void
    {
        $link = $this->makeDocumentAndLink(['allow_download' => false]);

        $response = $this->get(route('share-links.show', $link->token));

        $response->assertOk();
        $this->assertSame(1, $link->accesses()->count());
        $this->assertSame('view', $link->accesses()->first()->action);
    }

    public function test_a_download_allowed_link_logs_a_download_access(): void
    {
        $link = $this->makeDocumentAndLink(['allow_download' => true]);

        $this->get(route('share-links.show', $link->token))->assertOk();

        $this->assertSame('download', $link->accesses()->first()->action);
    }

    public function test_a_revoked_link_is_rejected(): void
    {
        $link = $this->makeDocumentAndLink(['is_revoked' => true]);

        $this->get(route('share-links.show', $link->token))->assertForbidden();
        $this->assertSame(0, $link->accesses()->count());
    }

    public function test_an_expired_link_is_rejected(): void
    {
        $link = $this->makeDocumentAndLink(['expires_at' => now()->subDay()]);

        $this->get(route('share-links.show', $link->token))->assertForbidden();
    }

    public function test_a_password_protected_link_is_not_yet_supported(): void
    {
        $link = $this->makeDocumentAndLink(['password_hash' => bcrypt('secret')]);

        $this->get(route('share-links.show', $link->token))->assertStatus(501);
        $this->assertSame(0, $link->accesses()->count());
    }

    public function test_an_unknown_token_returns_404(): void
    {
        $this->get(route('share-links.show', 'not-a-real-token'))->assertNotFound();
    }
}
