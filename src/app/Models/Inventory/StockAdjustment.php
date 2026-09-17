<?php

namespace App\Models\Inventory;

use App\Models\Concerns\GeneratesDocumentNumber;
use App\Models\Concerns\HasAuditTrail;
use App\Models\Core\Company;
use App\Models\Master\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockAdjustment extends Model
{
    use GeneratesDocumentNumber, HasAuditTrail, SoftDeletes;

    protected $table = 'inventory_stock_adjustments';

    protected $fillable = [
        'company_id', 'number', 'warehouse_id', 'adjustment_date', 'reason', 'notes', 'status',
    ];

    protected $casts = ['adjustment_date' => 'date'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function lines()
    {
        return $this->hasMany(StockAdjustmentLine::class);
    }

    public static function documentType(): string
    {
        return 'stock_adjustment';
    }
}
