<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserEmploymentIndustry extends Model
{
    public function industry(): BelongsTo
    {
        return $this->BelongsTo(Industry::class, 'industry');
    }
}
