<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class State extends Model
{
    use SoftDeletes;

    /**
     * Get the Country details of associated city.
    */
    public function country(): BelongsTo
    {
        return $this->BelongsTo(Country::class, 'country_id');
    }
}
