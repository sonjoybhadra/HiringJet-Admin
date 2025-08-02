<?php

namespace App\Http\Controllers\Api\Employer;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

use App\Mail\NotificationEmail;

use App\Models\User;
use App\Models\UserEmployer;
use App\Models\City;
use App\Models\Country;
use App\Models\State;

class EditEmployerBusinessInfoController extends BaseApiController
{
    public function updateBusinessData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address' => 'required|string|max:255',
            'country' => 'required|string',
            'state' => 'required|string',
            'city' => 'required|string',
            // 'address_line_2' => 'nullable|string|max:255',
            'pincode' => 'required|string|max:10',
            'country_code' => 'nullable|required|max:5',
            'landline' => 'nullable|string|max:20',
            'trade_license' => 'nullable|required|image|mimes:jpeg,png,jpg,webp|max:5120',// Max:5MB
            'vat_registration' => 'nullable|required|image|mimes:jpeg,png,jpg,webp|max:5120',// Max:5MB
            'logo' => 'nullable|required|image|mimes:jpeg,png,jpg,webp|max:5120',// Max:5MB
            'description' => 'required|string',
            'industrie_id' => 'required|integer',
            'web_url' => 'required|url',
            'employe_type' => 'required|in:company,agency'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            $city = new City();
            $country = new Country();
            $state = new State();
            $country_id = $country->getCountryId($request->country);
            $state_id = $state->getStateId($request->state, $country_id);
            $city_id = $city->getCityId($request->city, $country_id);

            $profile_data = [
                'country_id'=> $country_id,
                'city_id'=> $city_id,
                'state_id'=> $state_id,
                'address'=> $request->address,
                'address_line_2'=> $request->address_line_2,
                'pincode' => $request->pincode,
                'landline'=> $request->landline,
                'industrie_id'=> $request->industrie_id,
                'description'=> $request->description,
                'web_url'=> $request->web_url,
                'employe_type'=> $request->employe_type,
                'completed_steps'=> 2,
            ];
            if (request()->hasFile('profile_image')) {
                $file = request()->file('profile_image');
                $fileName = md5($file->getClientOriginalName() .'_'. time()) . "." . $file->getClientOriginalExtension();
                Storage::disk('public')->put('uploads/employer/profile_image/'.$fileName, file_get_contents($file));
                $profile_data['profile_image'] = 'public/storage/uploads/employer/profile_image/'.$fileName;
            }
            if (request()->hasFile('trade_license')) {
                $file = request()->file('trade_license');
                $fileName = md5($file->getClientOriginalName() .'_'. time()) . "." . $file->getClientOriginalExtension();
                Storage::disk('public')->put('uploads/employer/trade_license/'.$fileName, file_get_contents($file));
                $profile_data['trade_license'] = 'public/storage/uploads/employer/trade_license/'.$fileName;
            }
            if (request()->hasFile('vat_registration')) {
                $file = request()->file('vat_registration');
                $fileName = md5($file->getClientOriginalName() .'_'. time()) . "." . $file->getClientOriginalExtension();
                Storage::disk('public')->put('uploads/employer/vat_registration/'.$fileName, file_get_contents($file));
                $profile_data['vat_registration'] = 'public/storage/uploads/employer/vat_registration/'.$fileName;
            }
            if (request()->hasFile('logo')) {
                $file = request()->file('logo');
                $fileName = md5($file->getClientOriginalName() .'_'. time()) . "." . $file->getClientOriginalExtension();
                Storage::disk('public')->put('uploads/employer/logo/'.$fileName, file_get_contents($file));
                $profile_data['logo'] = 'public/storage/uploads/employer/logo/'.$fileName;
            }

            UserEmployer::where('user_id', auth()->user()->id)->update($profile_data);

            return $this->sendResponse($this->getUserDetails(), 'Business profile has successfully updated.');

        } catch (\Exception $e) {
            return $this->sendError('Error', 'Sorry!! Unable to update business profile.');
        }
    }

}
