<?php

namespace App\Http\Controllers\Api\Employer;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

use App\Mail\NotificationEmail;
use App\Models\User;
use App\Models\UserEmployer;

class EditEmployerProfileController extends BaseApiController
{
    /**
     * Post profile summery.
    */
    public function updateProfileData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|max:100',
            'last_name' => 'required|max:100',
            // 'email' => 'required|email|max:100|unique:users',
            'country_code' => 'required|max:5',
            'phone' => 'required|max:15|unique:users,phone,'.auth()->user()->id,
            /* 'password' => 'required|min:6',
            'c_password' => 'required|same:password', */
            'business_id' => 'required|integer',
            'designation_id' => 'required|integer',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            $image_path = "";
            if (request()->hasFile('profile_image')) {
                $file = request()->file('profile_image');
                $fileName = md5($file->getClientOriginalName() .'_'. time()) . "." . $file->getClientOriginalExtension();
                Storage::disk('public')->put('uploads/employer/profile_image/'.$fileName, file_get_contents($file));
                $image_path = 'public/storage/uploads/employer/profile_image/'.$fileName;
            }
            User::where('id', auth()->user()->id)->update([
                'first_name'=> $request->first_name,
                'last_name'=> $request->last_name,
                // 'email'=> $request->email,
                'country_code' => $request->country_code,
                'phone'=> $request->phone,
            ]);

            $update_data = [
                'first_name'=> $request->first_name,
                'last_name'=> $request->last_name,
                // 'email'=> $request->email,
                'country_code'=> $request->country_code,
                'phone' => $request->phone,
                'business_id'=> $request->business_id,
                'designation_id'=> $request->designation_id,
                'profile_image'=> $image_path
            ];

            UserEmployer::where('user_id', auth()->user()->id)->update($update_data);

            return $this->sendResponse($this->getUserDetails(), 'Profile data updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function removeProfilePicture(){
        try{
            $has_data = UserEmployer::where('user_id', auth()->user()->id)->first();
            if($has_data){
                $data_path = str_replace("public/storage/", "", $has_data->profile_image);
                UserEmployer::find($has_data->id)->update(['profile_image'=> NULL]);
                Storage::disk('public')->delete($data_path);
            }

            return $this->sendResponse([], 'Profile image deleted successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }


}
