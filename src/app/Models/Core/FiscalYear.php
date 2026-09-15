<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class FiscalYear extends Model
{
    protected $table = 'core_fiscal_years';

    protected $fillable = ['company_id', 'code', 'start_date', 'end_date', 'status'];

    protected $casts = ['start_date' => 'date', 'end_date' => 'date'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function accountingPeriods()
    {
        return $this->hasMany(AccountingPeriod::class);
    }
}
