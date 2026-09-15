<?php

namespace App\Models\Document;

use Illuminate\Database\Eloquent\Model;

class DocumentSignature extends Model
{
    protected $table = 'document_signatures';

    protected $fillable = [
        'document_id', 'share_link_id', 'signer_name', 'signer_email', 'document_hash',
        'signature_image_path', 'ip_address', 'user_agent', 'status', 'signed_at',
    ];

    protected $casts = ['signed_at' => 'datetime'];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function shareLink()
    {
        return $this->belongsTo(ShareLink::class);
    }
}
