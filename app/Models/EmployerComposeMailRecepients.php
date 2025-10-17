<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployerComposeMailRecepients extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'compose_email_id',
        'jobseeker_id',
        'employer_id',
        'reply_status',
        'view_status'
    ];

    /**
     * Get the role details of associated user.
    */
    public function jobseeker(): BelongsTo
    {
        return $this->BelongsTo(User::class, 'jobseeker_id');
    }

    public function composeEmail()
    {
        return $this->belongsTo(EmployerComposeMail::class, 'compose_email_id');
    }

}
