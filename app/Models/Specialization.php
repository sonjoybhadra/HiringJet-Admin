<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Specialization extends Model
{
    use SoftDeletes;

    /**
     * Get the role details of associated user.
    */
    public function qualification(): BelongsTo
    {
        return $this->BelongsTo(Qualification::class, 'qualification_id');
    }

    /**
     * Get the role details of associated user.
    */
    public function course(): BelongsTo
    {
        return $this->BelongsTo(Course::class, 'course_id');
    }

    public function getSpecializationId ($name, $course_id, $qualification_id){
        $specialization = Specialization::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
        if($specialization){
            return $specialization->id;
        }

        return Specialization::insertGetId([
                    'qualification_id' => $qualification_id,
                    'course_id' => $course_id,
                    'name' => ucwords($name),
                    'status'=> 1
                ]);
    }

}
