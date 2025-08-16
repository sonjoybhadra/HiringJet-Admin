<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Designation extends Model
{
    use SoftDeletes;

    public function postJobs()
    {
        return $this->hasMany(PostJob::class, 'designation');
    }

    public function getDesignationId ($name){
        $designation = Designation::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
        if($designation){
            return $designation->id;
        }

        return Designation::insertGetId([
                    'name'=> $name,
                    'status'=> 1
                ]);
    }
}
