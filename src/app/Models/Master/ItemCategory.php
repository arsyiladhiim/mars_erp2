<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class ItemCategory extends Model
{
    protected $table = 'master_item_categories';

    protected $fillable = ['parent_id', 'code', 'name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function parent()
    {
        return $this->belongsTo(ItemCategory::class, 'parent_id');
    }
}
