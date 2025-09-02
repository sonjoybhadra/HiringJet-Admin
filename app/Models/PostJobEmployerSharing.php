<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostJobEmployerSharing extends Model
{
    protected $fillable = ['job_id', 'owner_id', 'sharing_user_id'];
}
