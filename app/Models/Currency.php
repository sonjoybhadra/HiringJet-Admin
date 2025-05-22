<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Currency extends Model
{
    use SoftDeletes;

    /**
     * Get the role details of associated user.
    */
    public function country(): BelongsTo
    {
        return $this->BelongsTo(Country::class, 'country_id');
    }
}
