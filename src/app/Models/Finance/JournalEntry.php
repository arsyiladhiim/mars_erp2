<?php

namespace App\Models\Finance;

use App\Models\Core\AccountingPeriod;
use App\Models\Core\Company;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $table = 'finance_journal_entries';

    protected $fillable = [
        'company_id', 'accounting_period_id', 'number', 'entry_date', 'source_type',
        'reference_type', 'reference_id', 'memo', 'total_debit', 'total_credit',
        'status', 'reversed_journal_entry_id',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function accountingPeriod()
    {
        return $this->belongsTo(AccountingPeriod::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function reversedEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'reversed_journal_entry_id');
    }

    public function isBalanced(): bool
    {
        return bccomp((string) $this->total_debit, (string) $this->total_credit, 2) === 0;
    }
}
