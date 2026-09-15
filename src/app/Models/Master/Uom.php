<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Uom extends Model
{
    protected $table = 'master_uoms';

    protected $fillable = ['code', 'name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
