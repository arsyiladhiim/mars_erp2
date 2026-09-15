<?php

namespace App\Models\Helpdesk;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class KbArticle extends Model
{
    protected $table = 'helpdesk_kb_articles';

    protected $fillable = ['title', 'category', 'content', 'author_id', 'is_published'];

    protected $casts = ['is_published' => 'boolean'];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
