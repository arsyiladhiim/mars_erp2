<?php

namespace App\Models\Maintenance;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    protected $table = 'maintenance_requests';

    protected $fillable = [
        'number', 'maintainable_type', 'maintainable_id', 'type', 'title', 'description',
        'technician_id', 'scheduled_date', 'completed_date', 'cost', 'parts_used', 'status',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_date' => 'date',
        'cost' => 'decimal:2',
    ];

    public function maintainable()
    {
        return $this->morphTo();
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}
