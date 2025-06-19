<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserLanguage;

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
            'religion_id'=> 'required|integer',
            'merital_status'=> 'required|string',
            'dob'=> 'required|date',
            'nationality'=> 'required|integer',
            'country_id'=> 'required|integer',
            'city'=> 'required|integer',
            'cast_category'=> 'required|string',
            'differently_abled'=> 'required|boolean',
            'career_break'=> 'required|boolean',
            'usa_working_permit'=> 'required|boolean',
            'other_working_permit_country'=> 'required|array',
            'address'=> 'required|string',
            'pincode'=> 'required|integer',
            'language' => 'required|array',
            'career_break_reason'=> 'required_if:career_break,1|string'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            UserProfile::where('user_id', auth()->user()->id)->update([
                'gender'=> $request->gender,
                'religion_id'=> $request->religion_id,
                'merital_status_id'=> $request->merital_status,
                'date_of_birth'=> $request->dob,
                'nationality_id'=> $request->nationality,
                'country_id'=> $request->country_id,
                'city_id'=> $request->city,
                'cast_category'=> $request->cast_category,
                'differently_abled'=> $request->differently_abled,
                'career_break'=> $request->career_break,
                'career_break_reason'=> $request->career_break_reason,
                'usa_working_permit'=> $request->career_break,
                'other_working_permit_country'=> !empty($request->other_working_permit_country) ? json_encode($request->other_working_permit_country) : NULL,
                'address'=> $request->address,
                'pincode'=> $request->pincode,
                'alt_email'=> $request->alt_email,
                'alt_country_code'=> $request->alt_country_code,
                'alt_phone'=> $request->alt_phone,
                'diverse_background'=> $request->diverse_background,
                'has_driving_license'=> $request->has_driving_license,
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
            $this->calculate_profile_completed_percentage(auth()->user()->id, 'personal-details'); //Personal details completes
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
