<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployerBrand extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_name',
        'company_logo',
        'info',
        'industry_id',
        'contact_person_id',
        'contact_person_designation_id',
        'web_url',
        'address',
        'country',
        'zip_code',
        'status'
    ];
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
    public function contact_person(): BelongsTo
    {
        return $this->BelongsTo(User::class, 'contact_person_id');
    }
    /**
     * Get the role details of associated designation.
    */
    public function contact_person_designation(): BelongsTo
    {
        return $this->BelongsTo(Designation::class, 'contact_person_designation_id');
    }
}
