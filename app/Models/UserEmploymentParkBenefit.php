<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserEmploymentParkBenefit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'user_employment_id',
        'perk_benefit'
    ];

    public function emp_perk_benefit(): BelongsTo
    {
        return $this->BelongsTo(PerkBenefit::class, 'perk_benefit');
    }
}
