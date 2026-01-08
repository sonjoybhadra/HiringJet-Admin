<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestCallbackExperttalk extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'country_code',
        'phone',
        'preferred_time',
        'particulars',
        'created_by'
    ];
}
