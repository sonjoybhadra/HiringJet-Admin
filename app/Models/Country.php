<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use SoftDeletes;

    public function getCountryId ($name){
        $country = Country::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
        if($country){
            return $country->id;
        }

        return Country::insertGetId([
                    'name'=> $name,
                    'country_code'=> $name,
                    'country_flag'=> "",
                    'status'=> 1
                ]);
    }

}
