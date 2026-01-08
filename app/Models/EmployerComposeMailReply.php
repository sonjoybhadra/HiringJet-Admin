<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployerComposeMailReply extends Model
{
    protected $table = 'employer_compose_mail_replies';

    protected $fillable = [
        'compose_email_id',
        'sender_id',
        'receiver_id',
        'reply_message',
    ];

    // Sender user
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Receiver user
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    // Composed email
    public function composeEmail()
    {
        return $this->belongsTo(EmployerComposeMail::class, 'compose_email_id');
    }
}
