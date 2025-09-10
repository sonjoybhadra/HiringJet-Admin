<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserEmploymentIndustry extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'user_employment_id',
        'industry'
    ];

    public function industry(): BelongsTo
    {
        return $this->BelongsTo(Industry::class, 'industry');
    }
}
