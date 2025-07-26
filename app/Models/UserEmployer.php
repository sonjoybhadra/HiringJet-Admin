<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserEmployer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'country_code',
        'phone',
        'business_id',
        'designation_id',
        'industry_id',
        'country_id',
        'city_id',
        'state_id',
        'address',
        'address_line_2',
        'pincode',
        'landline',
        'trade_license',
        'vat_registration',
        'logo',
        'profile_image',
        'description',
        'web_url',
        'is_active',
    ];

    public function designation(): BelongsTo
    {
        return $this->BelongsTo(Designation::class, 'designation_id');
    }

    public function business(): BelongsTo
    {
        return $this->BelongsTo(Employer::class, 'business_id');
    }

    public function industry(): BelongsTo
    {
        return $this->BelongsTo(Employer::class, 'industry_id');
    }

    public function country(): BelongsTo
    {
        return $this->BelongsTo(Country::class, 'country_id');
    }

    public function city(): BelongsTo
    {
        return $this->BelongsTo(City::class, 'city_id');
    }

}
