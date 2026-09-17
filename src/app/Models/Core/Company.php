<?php

namespace App\Models\Core;

use App\Models\Concerns\HasAuditTrail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasAuditTrail, HasFactory, SoftDeletes;

    protected $table = 'core_companies';

    protected $fillable = [
        'code', 'name', 'legal_name', 'tax_id', 'email', 'phone',
        'address', 'logo_path', 'base_currency', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }
}
