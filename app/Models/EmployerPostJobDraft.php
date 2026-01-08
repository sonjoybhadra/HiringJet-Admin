<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployerPostJobDraft extends Model
{
    protected $fillable = [
        'user_id',
        'job_no',
        'position_name',
        'request_json'
    ];
}
