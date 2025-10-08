<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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

}
