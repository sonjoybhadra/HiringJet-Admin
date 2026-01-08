<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Keyskill extends Model
{
    use SoftDeletes;

    public function getKeyskillsId ($name){
        $designation = Keyskill::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
        if($designation){
            return $designation->id;
        }

        return Keyskill::insertGetId([
                    'name'=> $name,
                    'status'=> 1
                ]);
    }
}
