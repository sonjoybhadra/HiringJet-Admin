<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employer extends Model
{
    use SoftDeletes;

    /**
     * Get the role details of associated user.
    */
    public function industry(): BelongsTo
    {
        return $this->BelongsTo(Industry::class, 'industry_id');
    }

    public function postJobs()
    {
        return $this->hasMany(PostJob::class, 'employer_id');
    }

    public function getEmployerId ($name){
        $employer = Employer::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
        if($employer){
            return $employer->id;
        }

        return Employer::insertGetId([
                    'name' => ucwords($name),
                    'no_of_employee' => 1,
                    'industry_id' => 0,
                    'description' => "",
                    'logo' => "",
                    'status'=> 1
                ]);
    }
}
