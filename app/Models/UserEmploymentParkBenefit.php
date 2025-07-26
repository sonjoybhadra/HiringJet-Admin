<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserEmploymentParkBenefit extends Model
{
    public function emp_perk_benefit(): BelongsTo
    {
        return $this->BelongsTo(PerkBenefit::class, 'perk_benefit');
    }
}
