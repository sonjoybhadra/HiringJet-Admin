<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DemoRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'country_code',
        'phone',
        'email',
        'city',
        'organization',
        'interested_in',
        'created_at',
        'updated_at'
    ];
}
