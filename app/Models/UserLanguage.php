<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLanguage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'language_id',
        'can_read',
        'can_write',
        'can_speak',
        'proficiency_level',
        'is_default',
    ];
    /**
     * Get the language details of associated user.
    */
    public function language(): BelongsTo
    {
        return $this->BelongsTo(Language::class, 'language_id');
    }
}
