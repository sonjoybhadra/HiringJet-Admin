<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserEmploymentFunctionalArea extends Model
{
    public function functional_areas(): BelongsTo
    {
        return $this->BelongsTo(FunctionalArea::class, 'functional_area');
    }
}
