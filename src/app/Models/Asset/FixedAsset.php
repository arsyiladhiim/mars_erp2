<?php

namespace App\Models\Asset;

use App\Models\Concerns\HasAuditTrail;
use App\Models\Core\Company;
use App\Models\Core\Department;
use App\Models\Master\Warehouse;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FixedAsset extends Model
{
    use HasAuditTrail, SoftDeletes;

    protected $table = 'asset_fixed_assets';

    protected $fillable = [
        'company_id', 'code', 'name', 'asset_category_id', 'purchase_date', 'acquisition_cost',
        'useful_life_months', 'depreciation_method', 'residual_value', 'accumulated_depreciation',
        'location_warehouse_id', 'assigned_user_id', 'department_id', 'serial_number',
        'warranty_expiry', 'status',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'acquisition_cost' => 'decimal:2',
        'residual_value' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'warranty_expiry' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function locationWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'location_warehouse_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function depreciationEntries()
    {
        return $this->hasMany(AssetDepreciationEntry::class);
    }

    public function getBookValueAttribute(): float
    {
        return (float) $this->acquisition_cost - (float) $this->accumulated_depreciation;
    }
}
