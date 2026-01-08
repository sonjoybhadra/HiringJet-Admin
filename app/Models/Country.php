<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'country_code',
        'country_flag',
        'currency_code',
        'country_short_code',
        'aed_multiplier',
        'status'
    ];

    public function getCountryId ($name){
        $country = Country::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
        if($country){
            return $country->id;
        }

        return Country::insertGetId([
                    'name'=> ucwords($name),
                    'country_code'=> $name,
                    'country_flag'=> "",
                    'status'=> 1
                ]);
    }

    /**
     * Convert salary to AED for search and filter
     * @request Courrency ID, salary
     * return multiplier int.
    */
    public function convertSalary($currency_id, $salary)
    {
        if(empty($currency_id) || empty($salary)){
            $return = 0.0;
        }else{
            $currency = Country::find($currency_id);
            if($currency){
                $return = round($salary * $currency->aed_multiplier, 3);
            }else{
                $return = 0.0;
            }
        }

        return $return;
    }

}
