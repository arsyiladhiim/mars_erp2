<?php

namespace App\Models\Productivity;

use App\Models\Core\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    protected $table = 'productivity_todos';

    protected $fillable = [
        'title', 'description', 'assignee_id', 'created_by', 'department_id',
        'related_type', 'related_id', 'due_date', 'priority', 'checklist', 'status',
    ];

    protected $casts = [
        'due_date' => 'date',
        'checklist' => 'array',
    ];

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function related()
    {
        return $this->morphTo();
    }
}
