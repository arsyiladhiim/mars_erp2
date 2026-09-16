<?php

namespace App\Models\Concerns;

use App\Models\Audit\AuditLog;
use App\Models\Document\Document;

/**
 * Exposes read-only "Activity" (audit_logs) and "Attachments" (document_files)
 * pseudo-relations for Infolist View pages (PRD §41 document layout).
 */
trait HasAuditTrail
{
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'entity_id')
            ->where('entity_type', static::class)
            ->latest('created_at');
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'attachable');
    }
}
