<?php

namespace App\Models\Asset;

use Illuminate\Database\Eloquent\Model;

class AssetDepreciationEntry extends Model
{
    protected $table = 'asset_depreciation_entries';

    protected $fillable = [
        'fixed_asset_id', 'period_date', 'depreciation_amount',
        'accumulated_after', 'book_value_after', 'status',
    ];

    protected $casts = [
        'period_date' => 'date',
        'depreciation_amount' => 'decimal:2',
        'accumulated_after' => 'decimal:2',
        'book_value_after' => 'decimal:2',
    ];

    public function fixedAsset()
    {
        return $this->belongsTo(FixedAsset::class);
    }
}
