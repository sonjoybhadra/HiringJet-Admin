<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserItSkill extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'itkill_id',
        'version',
        'last_used',
        'exp_year',
        'exp_month'
    ];
    /**
     * Get the role details of associated user.
    */
    public function it_skills(): BelongsTo
    {
        return $this->BelongsTo(ItSkill::class, 'itkill_id');
    }
}
