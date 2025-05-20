<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Validator;
use JWTAuth;
use App\Models\User;
use App\Models\UserDetails;

class AuthController extends BaseApiController
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
    */
    public function __construct()
    {
        // $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     * @resuest string $email, string $password
    */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $credentials = request(['email', 'password']);
        $credentials['role_id'] = env('JOB_SEEKER_ROLE_ID');
        try{
            if (! $token = auth('api')->attempt($credentials)) {
                return $this->sendError('Unauthorized', 'Email or Password not matched.', Response::HTTP_UNAUTHORIZED);
            }
            // Set guard to "api" for the current request
            auth()->shouldUse('api');
            if(auth()->user()->status == 0){
                return $this->sendError('Unauthorized', 'Your account is not active. Please contact to the admin.', Response::HTTP_UNAUTHORIZED);
            }

            return $this->sendResponse([
                                        'token_type' => 'bearer',
                                        'token' => $token,
                                        'user' => auth()->user(),
                                        'expires_in' => config('jwt.ttl') * 60,
                                    ], 'Login successfully done.');
        } catch (JWTException $e) {
            return $this->sendError('Error', 'Login failed.',  Response::HTTP_UNAUTHORIZED);
        }
    }

    public function logout(Request $request)
    {
        try {
            Auth::logout();
            return $this->sendResponse('', 'Logged out successfully.');
        } catch (JWTException $exception) {
            return $this->sendError('Error', 'Sorry, the user cannot be logged out.',  Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display auth details.
    */
    public function getUser()
    {
        try {
            return $this->sendResponse(
                User::where('id', auth()->user()->id)
                                ->with('user_profile')
                                // ->with('user_education')
                                ->with('user_skills')
                                ->first()
            );
        } catch (JWTException $exception) {
            return $this->sendError('Error', 'Sorry, something went wrong, unable to fetch user details.',  Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Change Password.
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function change_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:6',
            'c_password' => 'required|same:password'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            User::where('id', auth()->user()->id)
                    ->update([
                        'password' => Hash::make($request->password),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
            return $this->sendResponse([], 'Password update successfully done.');
        }catch (\Exception $exception) {
            return $this->sendError('Error', 'Sorry!! Something went wrong. Unable to update password.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update_profile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country' => 'required',
            'name' => 'required|max:50',
            // 'last_name' => 'required|max:50',
            'email' => 'required|email|max:100|unique:users,email,'.auth()->user()->id,
            // 'country_code' => 'required|max:5',
            'phone' => 'required|max:15|unique:users,phone,'.auth()->user()->id
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            $image_path = "";
            if (request()->hasFile('image')) {
                $file = request()->file('image');
                $fileName = md5($file->getClientOriginalName() .'_'. time()) . "." . $file->getClientOriginalExtension();
                Storage::disk('public')->put('uploads/images/'.$fileName, file_get_contents($file));
                $image_path = 'storage/uploads/images/'.$fileName;
            }

            User::where('id', auth()->user()->id)->update([
                'name'=> $request->name,
                'email'=> $request->email,
                // 'country_code' => $request->country_code,
                'phone'=> $request->phone,
                'profile_image' => $image_path
            ]);

            return $this->sendResponse([], 'Profile updated successfully.');

        } catch (JWTException $e) {
            return $this->sendError('Error', 'Sorry!! Unable to update profile.');
        }
    }

}
