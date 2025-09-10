<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserEmploymentFunctionalArea extends Model
{
    protected $fillable = [
        'user_id',
        'user_employment_id',
        'functional_area'
    ];

    public function emp_functional_areas(): BelongsTo
    {
        return $this->BelongsTo(FunctionalArea::class, 'functional_area');
    }
}
