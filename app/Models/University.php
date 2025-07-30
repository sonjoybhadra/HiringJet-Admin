<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class University extends Model
{
    use SoftDeletes;

    public function getUniversityId ($name){
        $university = City::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
        if($university){
            return $university->id;
        }

        return University::insertGetId([
                    'name' => $name,
                    'status'=> 1
                ]);
    }
}
