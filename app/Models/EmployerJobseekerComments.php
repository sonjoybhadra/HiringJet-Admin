<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployerJobseekerComments extends Model
{
    use SoftDeletes;
    /**
     * Get the role details of associated user.
    */
    public function employer(): BelongsTo
    {
        return $this->BelongsTo(User::class, 'employer_id');
    }

    /**
     * Get the role details of associated user.
    */
    public function jobseekers(): BelongsTo
    {
        return $this->BelongsTo(User::class, 'jobseeker_id');
    }
}
