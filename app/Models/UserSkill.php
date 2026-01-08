<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserSkill extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'keyskill_id', 'proficiency_level', 'years_of_experience', 'is_primary', 'is_active'];

    /**
     * Get the key_skills details of associated ID.
    */
    public function key_skills(): BelongsTo
    {
        return $this->BelongsTo(Keyskill::class, 'keyskill_id');
    }
}
