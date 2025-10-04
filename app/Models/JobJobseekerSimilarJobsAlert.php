<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobJobseekerSimilarJobsAlert extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'search_string',
        'alert_start_date',
        'alert_till_date',
        'last_send_date',
        'status',
    ];
}
