<?php

namespace App\Models\Finance;

use App\Models\Core\Company;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    protected $table = 'finance_chart_of_accounts';

    protected $fillable = [
        'company_id', 'code', 'name', 'account_type', 'parent_id',
        'is_control_account', 'control_type', 'is_tax_account',
        'requires_cost_center', 'is_active',
    ];

    protected $casts = [
        'is_control_account' => 'boolean',
        'is_tax_account' => 'boolean',
        'requires_cost_center' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function parent()
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }
}
