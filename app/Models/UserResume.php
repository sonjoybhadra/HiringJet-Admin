<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserResume extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'cv', 'is_default'];
}
