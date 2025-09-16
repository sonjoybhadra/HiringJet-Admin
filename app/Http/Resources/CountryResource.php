<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'name'               => ucwords($this->name), // Capitalize first letter of each word
            'country_code'       => $this->country_code,
            'country_flag'       => $this->country_flag,
            'country_short_code' => $this->country_short_code,
        ];
    }
}
