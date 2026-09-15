<?php

namespace App\Models\Asset;

use App\Models\Core\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItAsset extends Model
{
    use SoftDeletes;

    protected $table = 'asset_it_assets';

    protected $fillable = [
        'fixed_asset_id', 'asset_tag', 'device_type', 'brand', 'model', 'serial_number',
        'imei', 'ip_address', 'mac_address', 'os', 'software', 'warranty_expiry',
        'assigned_user_id', 'department_id', 'location', 'status',
    ];

    protected $casts = [
        'software' => 'array',
        'warranty_expiry' => 'date',
    ];

    public function fixedAsset()
    {
        return $this->belongsTo(FixedAsset::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
