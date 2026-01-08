<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobJobseekerSimilarJobsAlert extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'search_string',
        'alert_start_date',
        'alert_till_date',
        'last_send_date',
        'status',
    ];

    public function jobseeker(): BelongsTo
    {
        return $this->BelongsTo(User::class, 'user_id');
    }
}
