<?php

namespace App\Models\Ai;

use Illuminate\Database\Eloquent\Model;

class AiMessage extends Model
{
    protected $table = 'ai_messages';

    protected $fillable = ['ai_conversation_id', 'role', 'content'];

    public function conversation()
    {
        return $this->belongsTo(AiConversation::class, 'ai_conversation_id');
    }
}
