<?php

namespace App\Models\Procurement;

use App\Models\Core\Branch;
use App\Models\Core\Company;
use App\Models\Core\CostCenter;
use App\Models\Core\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseRequest extends Model
{
    use SoftDeletes;

    protected $table = 'procurement_purchase_requests';

    protected $fillable = [
        'company_id', 'branch_id', 'number', 'requester_id', 'department_id',
        'cost_center_id', 'project_id', 'required_date', 'reason', 'status',
    ];

    protected $casts = ['required_date' => 'date'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function lines()
    {
        return $this->hasMany(PurchaseRequestLine::class);
    }
}
