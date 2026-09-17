<?php

namespace App\Models\Core;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use RuntimeException;

class AccountingPeriod extends Model
{
    protected $table = 'core_accounting_periods';

    protected $fillable = [
        'fiscal_year_id', 'name', 'period_number', 'start_date', 'end_date',
        'status', 'closed_at', 'closed_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'closed_at' => 'datetime',
    ];

    public function fiscalYear()
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function isLocked(): bool
    {
        return in_array($this->status, ['closed', 'locked'], true);
    }

    /**
     * The period covering a given date for a company, if one has been set up.
     */
    public static function forDate(Carbon|string $date, int $companyId): ?self
    {
        $date = Carbon::parse($date)->toDateString();

        return static::query()
            ->whereHas('fiscalYear', fn ($q) => $q->where('company_id', $companyId))
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->first();
    }

    /**
     * Guard used by every posting entry point (PRD §11.7, Key Business Rule
     * #5: "Closed accounting periods are locked"). If no period has been
     * configured at all for the date, posting is allowed — an unconfigured
     * calendar must never silently block the whole ERP — but a period that
     * *does* exist and is closed/locked always blocks.
     */
    public static function assertOpenForPosting(Carbon|string $date, int $companyId): void
    {
        $period = static::forDate($date, $companyId);

        if ($period?->isLocked()) {
            throw new RuntimeException(
                "Accounting period \"{$period->name}\" is {$period->status} and can no longer receive postings."
            );
        }
    }
}
