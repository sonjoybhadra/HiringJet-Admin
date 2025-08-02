<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployerCvFolder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'user_employer_id',
        'folder_name',
        'owner_id',
        'status',
    ];

    /**
     * Get the profile details of associated user.
    */
    public function profile_cv(): HasMany
    {
        return $this->hasMany(EmployerCvProfile::class, 'cv_folders_id');
    }

}
