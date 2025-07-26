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

class EditEmployerProfileController extends BaseApiController
{
    /**
     * Post profile summery.
    */
    public function updateProfileData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',// Max:5MB
            'country_id' => 'required|integer',
            'city_id' => 'required|integer',
            'state_id' => 'required|integer',
            'address' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'pincode' => 'required|string|max:10',
            'landline' => 'nullable|string|max:20',
            'industry_id' => 'required|integer',
            'trade_license' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',// Max:5MB
            'vat_registration' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',// Max:5MB
            'logo' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',// Max:5MB
            'description' => 'required|string',
            'web_url' => 'required|url'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            $image_path = $trade_license = $vat_registration = $logo = "";
            if (request()->hasFile('profile_image')) {
                $file = request()->file('profile_image');
                $fileName = md5($file->getClientOriginalName() .'_'. time()) . "." . $file->getClientOriginalExtension();
                Storage::disk('public')->put('uploads/employer/profile_image/'.$fileName, file_get_contents($file));
                $image_path = 'public/storage/uploads/employer/profile_image/'.$fileName;
            }
            if (request()->hasFile('trade_license')) {
                $file = request()->file('trade_license');
                $fileName = md5($file->getClientOriginalName() .'_'. time()) . "." . $file->getClientOriginalExtension();
                Storage::disk('public')->put('uploads/employer/trade_license/'.$fileName, file_get_contents($file));
                $trade_license = 'public/storage/uploads/employer/trade_license/'.$fileName;
            }
            if (request()->hasFile('vat_registration')) {
                $file = request()->file('vat_registration');
                $fileName = md5($file->getClientOriginalName() .'_'. time()) . "." . $file->getClientOriginalExtension();
                Storage::disk('public')->put('uploads/employer/vat_registration/'.$fileName, file_get_contents($file));
                $vat_registration = 'public/storage/uploads/employer/vat_registration/'.$fileName;
            }
            if (request()->hasFile('logo')) {
                $file = request()->file('logo');
                $fileName = md5($file->getClientOriginalName() .'_'. time()) . "." . $file->getClientOriginalExtension();
                Storage::disk('public')->put('uploads/employer/logo/'.$fileName, file_get_contents($file));
                $logo = 'public/storage/uploads/employer/logo/'.$fileName;
            }

            UserEmployer::where('user_id', auth()->user()->id)->update([
                'country_id'=> $request->country_id,
                'city_id'=> $request->city_id,
                'state_id'=> $request->state_id,
                'address'=> $request->address,
                'address_line_2'=> $request->address_line_2,
                'pincode' => $request->pincode,
                'landline'=> $request->landline,
                'industry_id'=> $request->industry_id,
                'trade_license'=> $trade_license,
                'vat_registration'=> $vat_registration,
                'logo'=> $logo,
                'description'=> $request->description,
                'web_url'=> $request->web_url
            ]);

            return $this->sendResponse($this->getUserDetails(), 'Setup company profile has successfully done.');

        } catch (\Exception $e) {
            return $this->sendError('Error', 'Sorry!! Unable to complete setup profile.');
        }
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'existing_password' => 'required|min:6',
            'password' => 'required|min:6',
            'c_password' => 'required|same:password',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $credentials = array(
            'email' => auth()->user()->email,
            'password' => $request->existing_password,
            'status'=> 1
        );
        $credentials['role_id'] = env('EMPLOYER_ROLE_ID');

        if (! $token = auth('api')->attempt($credentials)) {
            return $this->sendError('Current OTP Error', 'Current OTP not matched', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        User::where('id', Auth()->user()->id)
                ->update([
                    'password' => Hash::make($request->password)
                ]);

        $full_name = auth()->user()->first_name.' '.auth()->user()->last_name;
        Mail::to(auth()->user()->email)->send(new NotificationEmail('Password updated successfully done.', $full_name, 'Your password has been updated successfully. New password is: '.$request->password));

        return $this->sendResponse([
                                        'token_type' => 'bearer',
                                        'token' => $token,
                                        'user' => $this->getUserDetails(),
                                        'expires_in' => config('jwt.ttl') * 60,
                                    ], 'Password updated successfully done.');
    }

}
