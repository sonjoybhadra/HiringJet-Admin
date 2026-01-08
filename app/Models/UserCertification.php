<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserCertification extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'certification_name',
        'certification_provider',
        'certification_url',
        'from_month',
        'from_year',
        'to_month',
        'to_year',
        'has_expire',
        'certification_image'
    ];
}
