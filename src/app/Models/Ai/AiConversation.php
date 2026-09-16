<?php

namespace App\Models\Ai;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AiConversation extends Model
{
    protected $table = 'ai_conversations';

    protected $fillable = ['user_id', 'ai_provider_id', 'title'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function aiProvider()
    {
        return $this->belongsTo(AiProvider::class);
    }

    public function messages()
    {
        return $this->hasMany(AiMessage::class)->orderBy('id');
    }
}
