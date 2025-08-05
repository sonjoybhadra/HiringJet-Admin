<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployerEmailtemplate extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'template_name',
        'from_email_user_id',
        'designation_id',
        'experience_max',
        'experience_min',
        'country_id',
        'city_id',
        'currency_id',
        'salary_max',
        'salary_min',
        'message',
        'owner_id'
    ];
}
