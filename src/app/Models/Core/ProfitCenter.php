<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProfitCenter extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'core_profit_centers';

    protected $fillable = ['company_id', 'code', 'name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
