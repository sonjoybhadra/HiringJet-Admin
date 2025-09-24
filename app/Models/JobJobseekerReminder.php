<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobJobseekerReminder extends Model
{
    use SoftDeletes;

    protected $fillable = ['job_id', 'jobseeker_id', 'employer_id', 'reminder_count'];
}
