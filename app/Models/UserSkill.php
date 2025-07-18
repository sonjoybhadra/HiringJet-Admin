<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserSkill extends Model
{
    use SoftDeletes;
    /**
     * Get the key_skills details of associated ID.
    */
    public function key_skills(): BelongsTo
    {
        return $this->BelongsTo(Keyskill::class, 'keyskill_id');
    }
}
