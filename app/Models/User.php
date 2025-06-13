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
        'linkedin_id',
        'google_id',
        'provider',
        'provider_id',
        'first_name',
        'last_name',
        'email_verified_at',
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

     // Helper methods
        public function hasLinkedInAccount()
        {
            return !empty($this->linkedin_id);
        }

        public function hasGoogleAccount()
        {
            return !empty($this->google_id);
        }

        public function getSocialProvider()
        {
            return $this->provider;
        }

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
        return $this->hasOne(UserProfile::class, 'user_id')
                    ->with('marital_statuse')
                    ->with('country')
                    ->with('city')
                    ->with('pasport_country')
                    ->with('other_working_permit_country')
                    ->with('availabilitie')
                    ->with('nationality')
                    ->with('religion');
    }

    /**
     * Get the profile details of associated user.
    */
    public function user_education(): HasMany
    {
        return $this->hasMany(UserEducation::class, 'user_id')
                    ->with('qualification')
                    ->with('course')
                    ->with('location')
                    ->with('university')
                    ->with('specialization')
                    ->orderBy('created_at', 'asc');
    }
    /**
     * Get the profile details of associated user.
    */
    public function user_skills(): HasMany
    {
        return $this->hasMany(UserSkill::class, 'user_id')
                    ->with('key_skills')
                    ->orderBy('created_at', 'asc');
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
        return $this->hasMany(UserLanguage::class, 'user_id')
                    ->with('language')
                    ->orderBy('created_at', 'asc');
    }

    /**
     * Get the profile details of associated user.
    */
    public function user_employments(): HasMany
    {
        return $this->hasMany(UserEmployment::class, 'user_id')
                    ->with('employer')
                    ->with('country')
                    ->with('city')
                    ->with('currency')
                    ->with('work_level')
                    ->with('notice_period')
                    ->with('skills')
                    ->with('industrys')
                    ->with('functionalareas')
                    ->with('parkbenefits')
                    ->orderBy('created_at', 'asc');
    }
    /**
     * Get the profile details of associated user.
    */
    public function user_certification(): HasMany
    {
        return $this->hasMany(UserCertification::class, 'user_id')->orderBy('created_at', 'asc');
    }

    public function user_online_profile(): HasMany
    {
        return $this->hasMany(UserOnlineProfile::class, 'user_id')->orderBy('created_at', 'asc');
    }

    public function user_work_sample(): HasMany
    {
        return $this->hasMany(UserWorkSample::class, 'user_id')->orderBy('created_at', 'asc');
    }

    public function user_it_skill(): HasMany
    {
        return $this->hasMany(UserItSkill::class, 'user_id')
                    ->with('it_skills')
                    ->orderBy('created_at', 'asc');
    }


}
