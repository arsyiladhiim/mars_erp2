<?php

namespace App\Models\Concerns;

use App\Models\Core\NumberSeries;

/**
 * Auto-assigns `number` from the company's configured Number Series
 * (PRD §36) on creation, unless the model already carries an explicit value
 * (e.g. a test or an import). Host models must implement documentType()
 * and have a `company_id` column.
 *
 * Deliberately company-wide, not per-branch: `number` columns carry a
 * single global unique constraint, so a per-branch counter would let two
 * branches independently generate the identical formatted number (e.g. two
 * "PO-2026-000001"s) and collide. One counter per (company, document_type)
 * avoids that regardless of how many branches are creating documents.
 */
trait GeneratesDocumentNumber
{
    protected static function bootGeneratesDocumentNumber(): void
    {
        static::creating(function ($model) {
            if (filled($model->number)) {
                return;
            }

            $model->number = NumberSeries::next($model->company_id, static::documentType());
        });
    }

    abstract public static function documentType(): string;
}
