<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function city(): BelongsTo
    {
        return $this->BelongsTo(City::class, 'city_id');
    }

    public function currency(): BelongsTo
    {
        return $this->BelongsTo(Currency::class, 'currency_id');
    }

    public function work_level(): BelongsTo
    {
        return $this->BelongsTo(CurrentWorkLevel::class, 'work_level');
    }

    public function notice_period(): BelongsTo
    {
        return $this->BelongsTo(Availability::class, 'notice_period');
    }

    public function skills(): HasMany
    {
        return $this->HasMany(UserEmploymentSkill::class, 'user_employment_id');
    }
    public function industrys(): HasMany
    {
        return $this->HasMany(UserEmploymentIndustry::class, 'user_employment_id');
    }
    public function functional_areas(): HasMany
    {
        return $this->HasMany(UserEmploymentFunctionalArea::class, 'user_employment_id');
    }
    public function park_benefits(): HasMany
    {
        return $this->HasMany(UserEmploymentParkBenefit::class, 'user_employment_id');
    }

}
