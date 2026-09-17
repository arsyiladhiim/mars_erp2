<?php

namespace App\Models\Procurement;

use App\Models\Concerns\Approvable;
use App\Models\Concerns\GeneratesDocumentNumber;
use App\Models\Concerns\HasAuditTrail;
use App\Models\Core\Branch;
use App\Models\Core\Company;
use App\Models\Core\CostCenter;
use App\Models\Core\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseRequest extends Model
{
    use Approvable, GeneratesDocumentNumber, HasAuditTrail, SoftDeletes;

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

    public static function documentType(): string
    {
        return 'purchase_request';
    }

    public function getGrandTotalAttribute(): float
    {
        return (float) $this->lines->sum(fn (PurchaseRequestLine $line) => $line->quantity * $line->estimated_price);
    }
}
