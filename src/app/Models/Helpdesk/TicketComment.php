<?php

namespace App\Models\Helpdesk;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class TicketComment extends Model
{
    protected $table = 'helpdesk_ticket_comments';

    protected $fillable = ['ticket_id', 'user_id', 'comment', 'attachment_path'];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
