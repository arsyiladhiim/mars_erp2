<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class SystemPreference extends Model
{
    protected $table = 'core_system_preferences';

    protected $fillable = ['company_id', 'key', 'value', 'type', 'group'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
