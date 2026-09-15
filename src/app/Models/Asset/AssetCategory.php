<?php

namespace App\Models\Asset;

use Illuminate\Database\Eloquent\Model;

class AssetCategory extends Model
{
    protected $table = 'asset_categories';

    protected $fillable = ['code', 'name', 'depreciation_method', 'useful_life_months'];
}
