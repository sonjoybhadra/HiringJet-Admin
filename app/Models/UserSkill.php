<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserSkill extends Model
{
    use SoftDeletes;
    /**
     * Get the key_skills details of associated ID.
    */
    public function key_skills(): BelongsToMany
    {
        return $this->BelongsToMany(Keyskill::class, 'keyskill_id');
    }
}
