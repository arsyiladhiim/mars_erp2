<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NumberSeries extends Model
{
    protected $table = 'core_number_series';

    protected $fillable = [
        'company_id', 'branch_id', 'document_type', 'prefix', 'format',
        'next_number', 'padding', 'reset_yearly', 'is_active',
    ];

    protected $casts = [
        'reset_yearly' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Atomically reserve and format the next document number.
     */
    public function generateNumber(): string
    {
        return DB::transaction(function () {
            $series = self::where('id', $this->id)->lockForUpdate()->first();

            $number = str_pad((string) $series->next_number, $series->padding, '0', STR_PAD_LEFT);
            $series->increment('next_number');

            return str_replace(
                ['{PREFIX}', '{YEAR}', '{NUMBER}'],
                [$series->prefix, now()->year, $number],
                $series->format
            );
        });
    }
}
