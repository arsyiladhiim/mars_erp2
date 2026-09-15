<?php

namespace App\Models\Master;

use App\Models\Finance\TaxCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;

    protected $table = 'master_items';

    protected $fillable = [
        'sku', 'name', 'item_category_id', 'brand', 'uom_id', 'barcode', 'type',
        'costing_method', 'standard_cost', 'average_cost', 'selling_price',
        'minimum_stock', 'maximum_stock', 'reorder_point', 'is_batch_tracked',
        'is_serial_tracked', 'has_expiry', 'tax_code_id', 'image_path', 'is_active',
    ];

    protected $casts = [
        'standard_cost' => 'decimal:2',
        'average_cost' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'minimum_stock' => 'decimal:4',
        'maximum_stock' => 'decimal:4',
        'reorder_point' => 'decimal:4',
        'is_batch_tracked' => 'boolean',
        'is_serial_tracked' => 'boolean',
        'has_expiry' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(ItemCategory::class, 'item_category_id');
    }

    public function uom()
    {
        return $this->belongsTo(Uom::class);
    }

    public function taxCode()
    {
        return $this->belongsTo(TaxCode::class);
    }
}
