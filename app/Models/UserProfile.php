<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    //
    /**
     * Get the role details of associated user.
    */
    public function marital_statuse(): BelongsTo
    {
        return $this->BelongsTo(MaritalStatus::class, 'merital_status_id');
    }

    public function country(): BelongsTo
    {
        return $this->BelongsTo(Country::class, 'country_id');
    }

    public function city(): BelongsTo
    {
        return $this->BelongsTo(City::class, 'city_id');
    }

    public function pasport_country(): BelongsTo
    {
        return $this->BelongsTo(Country::class, 'pasport_country_id');
    }

}
