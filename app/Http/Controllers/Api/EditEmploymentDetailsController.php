<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserEmployment;
use App\Models\UserEducation;

class EditEmploymentDetailsController extends BaseApiController
{
    /**
     * Get educational details.
    */
    public function getEmploymentDetails()
    {
        try {
            return $this->sendResponse(
                UserEmployment::where('user_id', auth()->user()->id)
                                ->with('employer')
                                ->with('countrie')
                                ->with('citie')
                                ->with('BelongsTo')
                                ->first()
            );
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    /**
     * Post personal details.
    */
    public function updatePersonalDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gender'=> 'required|string',
            'merital_status'=> 'required|string',
            'cast_category'=> 'required|string',
            'dob'=> 'required|date',
            // 'differently_abled'=> 'required',
            'career_break'=> 'required',
            'usa_working_permit'=> 'required|boolean',
            'other_working_permit_country'=> 'required|integer',
            'address'=> 'required|string',
            'city'=> 'required|integer',
            'pincode'=> 'required|integer',
            'language' => 'required|array'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            UserEmployment::where('id', auth()->user()->id)->update([
                'date_of_birth'=> $request->dob,
                'gender'=> $request->gender,
                'merital_status'=> $request->merital_status,
                'cast_category'=> $request->cast_category,
                'career_break'=> $request->career_break,
                'usa_working_permit'=> $request->career_break,
                'pasport_country_id'=> $request->other_working_permit_country,
                'address'=> $request->address,
                'city_id'=> $request->city,
                'pincode'=> $request->pincode,
                'alt_email'=> $request->alt_email,
                'alt_country_code'=> $request->alt_country_code,
                'alt_phone'=> $request->alt_phone,
                'diverse_background'=> $request->diverse_background,
            ]);

            return $this->sendResponse([], 'Professional details updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    /**
     * Post personal details.
    */
    public function postPersonalDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gender'=> 'required|string',
            'merital_status'=> 'required|string',
            'cast_category'=> 'required|string',
            'dob'=> 'required|date',
            // 'differently_abled'=> 'required',
            'career_break'=> 'required',
            'usa_working_permit'=> 'required|boolean',
            'other_working_permit_country'=> 'required|integer',
            'address'=> 'required|string',
            'city'=> 'required|integer',
            'pincode'=> 'required|integer',
            'language' => 'required|array'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            UserEmployment::insert([
                'user_id'=> auth()->user()->id,
                'date_of_birth'=> $request->dob,
                'gender'=> $request->gender,
                'merital_status'=> $request->merital_status,
                'cast_category'=> $request->cast_category,
                'career_break'=> $request->career_break,
                'usa_working_permit'=> $request->career_break,
                'pasport_country_id'=> $request->other_working_permit_country,
                'address'=> $request->address,
                'city_id'=> $request->city,
                'pincode'=> $request->pincode,
                'alt_email'=> $request->alt_email,
                'alt_country_code'=> $request->alt_country_code,
                'alt_phone'=> $request->alt_phone,
                'diverse_background'=> $request->diverse_background,
            ]);

            return $this->sendResponse([], 'Professional details added successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

}
