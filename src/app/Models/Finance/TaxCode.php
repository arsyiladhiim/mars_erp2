<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;

class TaxCode extends Model
{
    protected $table = 'finance_tax_codes';

    protected $fillable = ['code', 'name', 'rate', 'type', 'is_active'];

    protected $casts = [
        'rate' => 'decimal:3',
        'is_active' => 'boolean',
    ];
}
