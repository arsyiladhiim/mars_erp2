<?php

namespace App\Models\Productivity;

use App\Models\Core\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Memo extends Model
{
    protected $table = 'productivity_memos';

    protected $fillable = [
        'title', 'content', 'visibility', 'department_id', 'author_id',
        'related_type', 'related_id', 'tags',
    ];

    protected $casts = ['tags' => 'array'];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
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
