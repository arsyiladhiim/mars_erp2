<?php

namespace App\Models\Helpdesk;

use App\Models\Core\Department;
use App\Models\Master\BusinessPartner;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $table = 'helpdesk_tickets';

    protected $fillable = [
        'number', 'subject', 'description', 'requester_id', 'assignee_id', 'department_id',
        'category', 'priority', 'sla_hours', 'due_at', 'related_asset_type', 'related_asset_id',
        'related_customer_id', 'status',
    ];

    protected $casts = ['due_at' => 'datetime'];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function relatedAsset()
    {
        return $this->morphTo();
    }

    public function relatedCustomer()
    {
        return $this->belongsTo(BusinessPartner::class, 'related_customer_id');
    }

    public function comments()
    {
        return $this->hasMany(TicketComment::class);
    }
}
