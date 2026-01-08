<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserOnlineProfile extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'profile_key', 'value'];
}
