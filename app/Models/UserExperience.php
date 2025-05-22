<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserExperience extends Model
{
    /**
     * Get the role details of associated user.
    */
    public function employers(): BelongsTo
    {
        return $this->BelongsTo(Employer::class, 'company_id');
    }

    /**
     * Get the role details of associated user.
    */
    public function industry(): BelongsTo
    {
        return $this->BelongsTo(Industry::class, 'industry_id');
    }

    /**
     * Get the role details of associated user.
    */
    public function countries(): BelongsTo
    {
        return $this->BelongsTo(Country::class, 'country_id');
    }

    /**
     * Get the role details of associated user.
    */
    public function cities(): BelongsTo
    {
        return $this->BelongsTo(City::class, 'city_id');
    }

}
