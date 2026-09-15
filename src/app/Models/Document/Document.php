<?php

namespace App\Models\Document;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use SoftDeletes;

    protected $table = 'document_files';

    protected $fillable = [
        'title', 'document_type', 'attachable_type', 'attachable_id', 'file_path',
        'mime_type', 'size_bytes', 'version', 'parent_document_id', 'metadata',
        'retention_until', 'uploaded_by',
    ];

    protected $casts = [
        'metadata' => 'array',
        'retention_until' => 'date',
    ];

    public function attachable()
    {
        return $this->morphTo();
    }

    public function parent()
    {
        return $this->belongsTo(Document::class, 'parent_document_id');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function shareLinks()
    {
        return $this->hasMany(ShareLink::class);
    }

    public function signatures()
    {
        return $this->hasMany(DocumentSignature::class);
    }
}
