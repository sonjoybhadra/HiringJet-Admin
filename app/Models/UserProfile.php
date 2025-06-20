<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'country_code',
        'phone',
        'phone_verified_at',
        'whatsapp_country_code',
        'whatsapp_number',
        'profile_image',
        'date_of_birth',
        'gender',
        'merital_status_id',
        'differently_abled',
        'career_break',
        'nationality_id',
        'cast_category',
        'usa_working_permit',
        'other_working_permit_country',
        'pasport_country_id',
        'country_id',
        'city_id',
        'address',
        'pincode',
        'resume_headline',
        'alt_email',
        'alt_country_code',
        'alt_phone',
        'diverse_background',
        'is_experienced',
        'currently_employed',
        'profile_summery',
        'preferred_designation',
        'preferred_location',
        'preferred_industry',
        'availability_id',
        'religion_id',
        'profile_completed_percentage',
        'completed_steps',
        'is_active',
    ];
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

    public function availabilitie(): BelongsTo
    {
        return $this->BelongsTo(Availability::class, 'availability_id');
    }

    public function nationality(): BelongsTo
    {
        return $this->BelongsTo(Nationality::class, 'nationality_id');
    }

    public function religion(): BelongsTo
    {
        return $this->BelongsTo(Religion::class, 'religion_id');
    }

}
