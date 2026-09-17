<?php

namespace App\Models\Inventory;

use App\Models\Concerns\GeneratesDocumentNumber;
use App\Models\Concerns\HasAuditTrail;
use App\Models\Core\Company;
use App\Models\Master\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTransfer extends Model
{
    use GeneratesDocumentNumber, HasAuditTrail, SoftDeletes;

    protected $table = 'inventory_stock_transfers';

    protected $fillable = [
        'company_id', 'number', 'from_warehouse_id', 'to_warehouse_id',
        'transfer_date', 'reason', 'status',
    ];

    protected $casts = ['transfer_date' => 'date'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function lines()
    {
        return $this->hasMany(StockTransferLine::class);
    }

    public static function documentType(): string
    {
        return 'stock_transfer';
    }
}
