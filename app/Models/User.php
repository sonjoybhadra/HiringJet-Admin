<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
    */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to add to the JWT.
     *
     * @return array
    */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Get the role details of associated user.
    */
    public function user_role(): BelongsTo
    {
        return $this->BelongsTo(Role::class, 'role_id');
    }

    /**
     * Get the profile details of associated user.
    */
    public function user_profile(): HasOne
    {
        return $this->hasOne(UserProfile::class, 'user_id');
    }

    /**
     * Get the profile details of associated user.
    */
    public function user_education(): HasMany
    {
        return $this->hasMany(UserEducation::class, 'user_id')->orderBy('created_at', 'asc');
    }
    /**
     * Get the profile details of associated user.
    */
    public function user_skills(): HasMany
    {
        return $this->hasMany(UserSkill::class, 'user_id')->orderBy('created_at', 'asc');
    }
    /**
     * Get the profile details of associated user.
    */
    public function user_cv(): HasMany
    {
        return $this->hasMany(UserResume::class, 'user_id')->orderBy('created_at', 'desc');
    }
    /**
     * Get the profile details of associated user.
    */
    public function user_profile_completed_percentages(): HasMany
    {
        return $this->hasMany(UserProfileCompletedPercentage::class, 'user_id')->orderBy('created_at', 'desc');
    }
    /**
     * Get the profile details of associated user.
    */
    public function user_languages(): HasMany
    {
        return $this->hasMany(UserLanguage::class, 'user_id')->orderBy('created_at', 'asc');
    }

    /**
     * Get the profile details of associated user.
    */
    public function user_employments(): HasMany
    {
        return $this->hasMany(UserEmployment::class, 'user_id')->orderBy('created_at', 'asc');
    }


}
