<?php

namespace App\Models\Master;

use App\Models\Core\Branch;
use App\Models\Core\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use SoftDeletes;

    protected $table = 'master_warehouses';

    protected $fillable = ['company_id', 'branch_id', 'code', 'name', 'address', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
