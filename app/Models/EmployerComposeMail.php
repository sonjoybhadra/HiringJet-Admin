<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployerComposeMail extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'from_email',
        'designation_id',
        'experience_max',
        'experience_min',
        'country_id',
        'city_id',
        'currency_id',
        'salary_max',
        'salary_min',
        'subject',
        'message',
        'questions',
        'answers',
        'status',
        'created_at',
        'updated_at'
    ];

    /**
     * The employer who sent the mail.
     */
    public function employer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')
                    ->select('id', 'first_name', 'last_name', 'email', 'country_code', 'phone');
    }

    /**
     * All recipients of this composed mail.
     */
    public function recepients(): HasMany
    {
        return $this->hasMany(EmployerComposeMailRecepients::class, 'compose_email_id')
                    ->with(['jobseeker:id,first_name,last_name,email,country_code,phone']);
    }

    /**
     * All replies to this composed mail.
     */
   public function replies()
    {
        return $this->hasMany(EmployerComposeMailReply::class, 'compose_email_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')
                    ->select('id', 'first_name', 'last_name', 'email', 'country_code', 'phone');
    }
}
