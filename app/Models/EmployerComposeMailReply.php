<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployerComposeMailReply extends Model
{
    protected $table = 'employer_compose_mail_replies';

    protected $fillable = [
        'compose_email_id',
        'jobseeker_id',
        'reply_message',
    ];

}
