<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployerTag extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'tag_name',
        'owner_id',
        'status'
    ];
}
