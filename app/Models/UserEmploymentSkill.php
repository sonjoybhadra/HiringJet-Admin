<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserEmploymentSkill extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'user_employment_id',
        'keyskill_id'
    ];

    public function skill(): BelongsTo
    {
        return $this->BelongsTo(Keyskill::class, 'keyskill_id');
    }
}
