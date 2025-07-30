<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class State extends Model
{
    use SoftDeletes;

    /**
     * Get the Country details of associated city.
    */
    public function country(): BelongsTo
    {
        return $this->BelongsTo(Country::class, 'country_id');
    }

    public function getStateId ($name, $country_id){
        $state = State::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
        if($state){
            return $state->id;
        }

        $country = Country::find($country_id);
        return State::insertGetId([
                    'name' => $name,
                    'country_id' => $country_id,
                    'country_code' => $country->country_code,
                    'country_name' => $country->name,
                    'state_code' => $name,
                    'is_active'=> 1
                ]);
    }

}
