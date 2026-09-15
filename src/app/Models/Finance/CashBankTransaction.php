<?php

namespace App\Models\Finance;

use App\Models\Core\Company;
use Illuminate\Database\Eloquent\Model;

class CashBankTransaction extends Model
{
    protected $table = 'finance_cash_bank_transactions';

    protected $fillable = [
        'company_id', 'bank_account_id', 'number', 'type', 'transaction_date', 'amount',
        'description', 'is_reconciled', 'reconciled_date', 'status',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'is_reconciled' => 'boolean',
        'reconciled_date' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }
}
