<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostJobUserApplied extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'job_id', 'status', 'created_at'];

    /**
     * Get the job details of associated relation.
    */
    public function job_details(): BelongsTo
    {
        return $this->BelongsTo(PostJob::class, 'job_id');
    }
}
