<?php

namespace App\Models\Procurement;

use App\Models\Master\Item;
use App\Models\Master\Uom;
use Illuminate\Database\Eloquent\Model;

class RfqLine extends Model
{
    protected $table = 'procurement_rfq_lines';

    protected $fillable = ['rfq_id', 'item_id', 'description', 'quantity', 'uom_id'];

    protected $casts = ['quantity' => 'decimal:4'];

    public function rfq()
    {
        return $this->belongsTo(Rfq::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function uom()
    {
        return $this->belongsTo(Uom::class);
    }
}
