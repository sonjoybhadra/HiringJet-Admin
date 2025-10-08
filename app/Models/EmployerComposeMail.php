<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
     * Get the profile details of associated user.
    */
    public function recepients(): HasOne
    {
        return $this->hasOne(EmployerComposeMailRecepients::class, 'compose_email_id')
                    ->with(['jobseeker:id,first_name,last_name,email,country_code,phone']);

    }
}
