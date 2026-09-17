<?php

namespace App\Models\Document;

use Illuminate\Database\Eloquent\Model;

class ShareLinkAccess extends Model
{
    const UPDATED_AT = null;

    protected $table = 'document_share_link_accesses';

    protected $fillable = ['share_link_id', 'ip_address', 'user_agent', 'action'];

    public function shareLink()
    {
        return $this->belongsTo(ShareLink::class);
    }
}
