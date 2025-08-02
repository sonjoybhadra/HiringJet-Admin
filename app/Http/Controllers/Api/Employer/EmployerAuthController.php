<?php

namespace App\Http\Controllers\Api\Employer;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Validator;
// use JWTAuth;
use App\Models\User;
use App\Models\UserEmployment;
use App\Models\Designation;
use App\Models\ShortlistedJob;
use App\Models\PostJobUserApplied;
use App\Models\PostJob;

class EmployerAuthController extends BaseApiController
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
        $credentials['role_id'] = env('EMPLOYER_ROLE_ID')??2;
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
                                        'user' => $this->getEmployerDetails(),
                                        'expires_in' => config('jwt.ttl') * 60,
                                        'completed_steps'=> [
                                            '0' => 'Registration step 1 completed but OTP verification is pending',
                                            '1' => 'OTP verification is done but setup profile is pending',
                                            '2' => 'Registration process is completed'
                                        ]
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
        $data = User::where('id', auth()->user()->id)
                    ->with('user_employer_details')
                    ->with('user_cv')
                    ->first();

        // Convert to array to manipulate keys
        $array = (array) $data;

        if (array_key_exists('user_employer_details', $array)) {
            $array['user_profile'] = $array['user_employer_details'];
            unset($array['user_employer_details']);
        }

        // Convert back to object
        $data = (object) $array;
        try {
            return $this->sendResponse(
                $data,
                'Employer Details'
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
                                        'user' => $this->getEmployerDetails(),
                                        'expires_in' => config('jwt.ttl') * 60,
                                    ], 'Password updated successfully done.');
    }

    public function loginWithGoogle(Request $request)
    {
        try{
            $client = new \Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]); // Your Google Client ID
            $payload = $client->verifyIdToken($request->token);

            if ($payload) {
                $email = $payload['email'];

                // Find or create the user
                $user = User::firstOrFail(
                                ['email' => $email]
                            );
                if($user->status == 0){
                    return $this->sendError('Unauthorized', 'Your account is not active. Please contact to the admin.', Response::HTTP_UNAUTHORIZED);
                }
                // Create JWT token
                $token = JWTAuth::fromUser($user);
                // Set guard to "api" for the current request
                auth()->setUser($user);

                return $this->sendResponse([
                                            'token_type' => 'bearer',
                                            'token' => $token,
                                            'user' => $this->getUserDetails(),
                                            'expires_in' => config('jwt.ttl') * 60,
                                        ], 'Login successfully done.');
            } else {
                return response()->json(['error' => 'Invalid token'], 401);
            }
        }catch (JWTException $e) {
            return $this->sendError('Error', 'Login failed.',  Response::HTTP_UNAUTHORIZED);
        }
    }

    public function loginWithLinkedIn(Request $request)
    {
        $code = $request->code;
        try{
            $response = Http::asForm()->post(env('LINKEDIN_AUTH_URL'), [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => 'http://localhost:3000/linkedin/callback',
                'client_id' => env('LINKEDIN_CLIENT_ID'),
                'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
            ]);

            if (!$response->ok()) {
                return $this->sendError('Authentication Error', 'Failed to get access token', 400);
            }

            $accessToken = $response->json()['access_token'];

            // Get user profile
            $profile = Http::withToken($accessToken)->get('https://api.linkedin.com/v2/me');
            $emailData = Http::withToken($accessToken)->get('https://api.linkedin.com/v2/emailAddress?q=members&projection=(elements*(handle~))');

            if (!$profile->ok() || !$emailData->ok()) {
                return $this->sendError('Authentication Error', 'Failed to fetch LinkedIn user', 400);
            }

            $email = $emailData->json()['elements'][0]['handle~']['emailAddress'];

            $user = User::firstOrCreate(
                ['email' => $email]
            );

            // Find or create the user
            $user = User::firstOrFail(
                            ['email' => $email]
                        );
            if($user->status == 0){
                return $this->sendError('Unauthorized', 'Your account is not active. Please contact to the admin.', Response::HTTP_UNAUTHORIZED);
            }
            // Create JWT token
            $token = JWTAuth::fromUser($user);
            // Set guard to "api" for the current request
            auth()->setUser($user);

            return $this->sendResponse([
                                        'token_type' => 'bearer',
                                        'token' => $token,
                                        'user' => $this->getUserDetails(),
                                        'expires_in' => config('jwt.ttl') * 60,
                                    ], 'Login successfully done.');
        }catch (JWTException $e) {
            return $this->sendError('Error', 'Login failed.',  Response::HTTP_UNAUTHORIZED);
        }
    }

}
