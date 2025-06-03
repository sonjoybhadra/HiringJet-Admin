<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employer extends Model
{
    use SoftDeletes;

    /**
     * Get the role details of associated user.
    */
    public function industry(): BelongsTo
    {
        return $this->BelongsTo(Industry::class, 'industry_id');
    }
    
    public function postJobs()
    {
        return $this->hasMany(PostJob::class);
    }
}
