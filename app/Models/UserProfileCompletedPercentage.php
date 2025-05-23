<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfileCompletedPercentage extends Model
{
    /**
     * Get the role details of associated user.
    */
    public function profile_completes(): BelongsTo
    {
        return $this->BelongsTo(ProfileComplete::class, 'profile_completes_id');
    }
}
