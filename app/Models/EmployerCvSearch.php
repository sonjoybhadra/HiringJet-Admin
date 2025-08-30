<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployerCvSearch extends Model
{
    use SoftDeletes;
    /**
     * Get the role details of associated user.
    */
    public function employer(): BelongsTo
    {
        return $this->BelongsTo(User::class, 'employer_id');
    }
}
