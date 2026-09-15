<?php

namespace App\Models\Finance;

use App\Models\Core\CostCenter;
use Illuminate\Database\Eloquent\Model;

class JournalEntryLine extends Model
{
    protected $table = 'finance_journal_entry_lines';

    protected $fillable = [
        'journal_entry_id', 'chart_of_account_id', 'cost_center_id', 'description', 'debit', 'credit',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class);
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class);
    }
}
