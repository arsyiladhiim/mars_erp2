<?php

namespace App\Models\Concerns;

use App\Models\Audit\AuditLog;
use App\Models\Document\Document;

/**
 * Exposes read-only "Activity" (audit_logs) and "Attachments" (document_files)
 * pseudo-relations for Infolist View pages (PRD §41 document layout), and
 * records create/update/delete + status-change events to the immutable
 * audit_logs table (PRD §31, Key Business Rule #4/#8).
 */
trait HasAuditTrail
{
    protected static function bootHasAuditTrail(): void
    {
        static::created(function ($model) {
            AuditLog::record('created', static::class, $model->getKey(), null, $model->toArray());
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            unset($changes['updated_at']);

            if (empty($changes)) {
                return;
            }

            $action = array_key_exists('status', $changes) ? 'status_changed' : 'updated';

            AuditLog::record(
                $action,
                static::class,
                $model->getKey(),
                array_intersect_key($model->getOriginal(), $changes),
                $changes,
            );
        });

        static::deleted(function ($model) {
            $isSoftDelete = method_exists($model, 'trashed') && $model->trashed();

            AuditLog::record(
                $isSoftDelete ? 'soft_deleted' : 'deleted',
                static::class,
                $model->getKey(),
                $model->toArray(),
                null,
            );
        });
    }

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
