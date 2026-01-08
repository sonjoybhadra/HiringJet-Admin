<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use SoftDeletes;

    /**
     * Get the role details of associated user.
    */
    public function qualification(): BelongsTo
    {
        return $this->BelongsTo(Qualification::class, 'qualification_id');
    }

    public function getCourseId ($name, $qualification_id){
        $course = Course::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
        if($course){
            return $course->id;
        }

        return Course::insertGetId([
                    'qualification_id' => $qualification_id,
                    'name' => ucwords($name),
                    'status'=> 1
                ]);
    }

}
