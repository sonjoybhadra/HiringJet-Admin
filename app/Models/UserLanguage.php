<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLanguage extends Model
{
    //

    /**
     * Get the language details of associated user.
    */
    public function language(): BelongsTo
    {
        return $this->BelongsTo(Language::class, 'language_id');
    }
}
