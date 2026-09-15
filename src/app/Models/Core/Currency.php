<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $table = 'core_currencies';

    protected $fillable = ['code', 'name', 'symbol', 'exchange_rate_to_base', 'is_base', 'is_active'];

    protected $casts = [
        'exchange_rate_to_base' => 'decimal:6',
        'is_base' => 'boolean',
        'is_active' => 'boolean',
    ];
}
