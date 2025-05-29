<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserEmployment extends Model
{
    protected $fillable = ['user_id', 'total_experience_years', 'total_experience_months', 'last_designation', 'employer_id', 'country_id', 'city_id', 'currency_id', 'current_salary', 'working_since_from_year', 'working_since_from_month', 'working_since_to_year', 'working_since_to_month'];

    /**
     * Get the role details of associated user.
    */
    public function employer(): BelongsTo
    {
        return $this->BelongsTo(Employer::class, 'employer_id');
    }

    public function countrie(): BelongsTo
    {
        return $this->BelongsTo(Country::class, 'country_id');
    }

    public function citie(): BelongsTo
    {
        return $this->BelongsTo(City::class, 'city_id');
    }

    public function currency(): BelongsTo
    {
        return $this->BelongsTo(Currency::class, 'currency_id');
    }

}
