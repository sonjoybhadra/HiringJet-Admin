<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployerEmailtemplate extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'template_name',
        'from_email_user_id',
        'designation_id',
        'experience_max',
        'experience_min',
        'country_id',
        'city_id',
        'currency_id',
        'salary_max',
        'salary_min',
        'message',
        'owner_id'
    ];

    /**
     * Get the role details of associated user.
    */
    public function from_email_user(): BelongsTo
    {
        return $this->BelongsTo(User::class, 'from_email_user_id');
    }

    /**
     * Get the role details of associated user.
    */
    public function designations(): BelongsTo
    {
        return $this->BelongsTo(Designation::class, 'designation_id');
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
    /**
     * Get the role details of associated user.
    */
    public function currency(): BelongsTo
    {
        return $this->BelongsTo(Country::class, 'currency_id');
    }
}
