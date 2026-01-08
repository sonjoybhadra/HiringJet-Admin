<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserWorkSample extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'sample_title',
        'sample_url',
        'from_month',
        'from_year',
        'to_month',
        'to_year',
        'currently_working',
        'sample_description',
        'sample_image'
    ];
}
