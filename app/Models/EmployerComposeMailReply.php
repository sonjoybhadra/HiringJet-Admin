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

    /**
     * Get the jobseeker that owns the reply
     */
    public function jobseeker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'jobseeker_id')
                    ->select('id', 'first_name', 'last_name', 'email', 'country_code', 'phone');
    }

    /**
     * Get the compose email that owns the reply
     */
    public function composeEmail()
    {
        return $this->belongsTo(EmployerComposeMail::class, 'compose_email_id');
    }
}
