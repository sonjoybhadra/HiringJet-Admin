<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportBug extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'email',
        'phone',
        'category',
        'description',
        'source'
    ];
}
