<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    use SoftDeletes;

    /**
     * Get the Country details of associated city.
    */
    public function country(): BelongsTo
    {
        return $this->BelongsTo(Country::class, 'country_id');
    }

    public function getCityId ($name, $country_id){
        $city = City::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
        if($city){
            return $city->id;
        }

        return City::insertGetId([
                    'name' => ucwords($name),
                    'country_id' => $country_id,
                    'status'=> 1
                ]);
    }

}
