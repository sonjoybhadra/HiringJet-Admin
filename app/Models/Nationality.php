<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Nationality extends Model
{
    use SoftDeletes;

    public function postJobs()
    {
        return $this->hasMany(PostJob::class, 'nationality');
    }
}
