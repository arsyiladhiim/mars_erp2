<?php

namespace App\Models\Core;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

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
}
