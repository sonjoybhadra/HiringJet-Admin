<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Specialization extends Model
{
    use SoftDeletes;

    /**
     * Get the role details of associated user.
    */
    public function qualification(): BelongsTo
    {
        return $this->BelongsTo(Qualification::class, 'qualification_id');
    }

    /**
     * Get the role details of associated user.
    */
    public function course(): BelongsTo
    {
        return $this->BelongsTo(Course::class, 'course_id');
    }
}
