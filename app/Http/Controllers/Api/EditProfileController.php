<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\UserProfile;

class EditProfileController extends BaseApiController
{
    /**
     * Post profile summery.
    */
    public function updateProfileData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|max:100',
            'last_name' => 'required|max:100',
            // 'email' => 'required|email|max:100|unique:users,email,'.auth()->user()->id,
            'country_code' => 'required|max:5',
            'phone' => 'required|max:15|unique:users,phone,'.auth()->user()->id,
            'city' => 'required|integer',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            $image_path = "";
            if (request()->hasFile('profile_image')) {
                $file = request()->file('profile_image');
                $fileName = md5($file->getClientOriginalName() .'_'. time()) . "." . $file->getClientOriginalExtension();
                Storage::disk('public')->put('uploads/user/profile_image/'.$fileName, file_get_contents($file));
                $image_path = 'public/storage/uploads/user/profile_image/'.$fileName;
            }
            User::where('id', auth()->user()->id)->update([
                'first_name'=> $request->first_name,
                'last_name'=> $request->last_name,
                // 'email'=> $request->email,
                'country_code' => $request->country_code,
                'phone'=> $request->phone
            ]);

            $update_data = [
                'first_name'=> $request->first_name,
                'last_name'=> $request->last_name,
                // 'email'=> $request->email,
                'country_code'=> $request->country_code,
                'phone' => $request->phone,
                'whatsapp_country_code' => $request->is_whatsapp == 1 ? ($request->country_code) : NULL, //[0/1]
                'whatsapp_number' => $request->is_whatsapp == 1 ? ($request->phone) : NULL, //[0/1]
                'city_id'=> $request->city
            ];
            if($image_path != ""){
                $update_data['profile_image'] = $image_path;

                $this->calculate_profile_completed_percentage(auth()->user()->id, 'upload-photo'); //Profile image completes
            }
            UserProfile::where('user_id', auth()->user()->id)->update($update_data);

            if($request->is_whatsapp == 1){
                $this->calculate_profile_completed_percentage(auth()->user()->id, 'whatsapp'); //WhatsApp completes
            }
            if(@empty($request->city)){
                $this->calculate_profile_completed_percentage(auth()->user()->id, 'current-location'); //current location completes
            }

            return $this->sendResponse($this->getUserDetails(), 'Profile data updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
