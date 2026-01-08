<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobCategory extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'status']; // ✅ allow mass assignment

    public function postJobs()
    {
        return $this->hasMany(PostJob::class, 'job_category');
    }
}
