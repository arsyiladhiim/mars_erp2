<?php

namespace App\Models\Finance;

use App\Models\Core\Company;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $table = 'finance_bank_accounts';

    protected $fillable = [
        'company_id', 'chart_of_account_id', 'account_name', 'account_number',
        'bank_name', 'branch', 'currency', 'opening_balance', 'is_active',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class);
    }
}
