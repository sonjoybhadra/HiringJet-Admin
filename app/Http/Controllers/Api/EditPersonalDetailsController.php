<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserResume;
use App\Models\ProfileComplete;
use App\Models\UserLanguage;
use App\Models\UserProfileCompletedPercentage;

class EditPersonalDetailsController extends BaseApiController
{
    //
    /**
     * Get personal details.
    */
    public function getPersonalDetails()
    {
        try {
            return $this->sendResponse([
                'personal_details'=> UserProfile::where('user_id', auth()->user()->id)
                                            ->with('marital_statuse')
                                            ->with('city')
                                            ->with('pasport_country')
                                            ->first(),
                'user_languages'=> UserLanguage::where('user_id', auth()->user()->id)
                                            ->with('language')
                                            ->get()
            ]);
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
            'country_id'=> 'required|integer',
            'city'=> 'required|integer',
            'pincode'=> 'required|integer',
            'religion_id'=> 'required|integer',
            'language' => 'required|array'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            UserProfile::where('user_id', auth()->user()->id)->update([
                'date_of_birth'=> $request->dob,
                'gender'=> $request->gender,
                'merital_status_id'=> $request->merital_status,
                'cast_category'=> $request->cast_category,
                'career_break'=> $request->career_break,
                'usa_working_permit'=> $request->career_break,
                'pasport_country_id'=> $request->other_working_permit_country,
                'address'=> $request->address,
                'country_id'=> $request->country_id,
                'city_id'=> $request->city,
                'pincode'=> $request->pincode,
                'religion_id'=> $request->religion_id,
                'alt_email'=> $request->alt_email,
                'alt_country_code'=> $request->alt_country_code,
                'alt_phone'=> $request->alt_phone,
                'diverse_background'=> $request->diverse_background,
            ]);

            if(!empty($request->language)){
                UserLanguage::where('user_id', auth()->user()->id)->delete();
                foreach($request->language as $index => $language){
                    UserLanguage::insert([
                        'user_id'=> auth()->user()->id,
                        'language_id'=> $language,
                        'can_read'=> $request->can_read[$index],
                        'can_write'=> $request->can_write[$index],
                        'can_speak' => $request->can_speak[$index],
                        'proficiency_level'=> $request->proficiency_level[$index],
                        'is_default'=> true
                    ]);
                }
            }

            return $this->sendResponse([
                'personal_details'=> UserProfile::where('user_id', auth()->user()->id)
                                            ->with('marital_statuse')
                                            ->with('city')
                                            ->with('pasport_country')
                                            ->first(),
                'user_languages'=> UserLanguage::where('user_id', auth()->user()->id)
                                            ->with('language')
                                            ->get()
            ], 'Personal details updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

}
