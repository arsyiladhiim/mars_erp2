<?php

namespace App\Models\Document;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ShareLink extends Model
{
    protected $table = 'document_share_links';

    protected $fillable = [
        'document_id', 'token', 'password_hash', 'expires_at', 'allow_download',
        'requires_signature', 'is_revoked', 'created_by',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'allow_download' => 'boolean',
        'requires_signature' => 'boolean',
        'is_revoked' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $link) {
            $link->token ??= Str::random(48);
        });
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
