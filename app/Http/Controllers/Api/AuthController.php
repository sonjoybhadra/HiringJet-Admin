<?php

namespace App\Http\Controllers\Api;

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
use App\Models\ProfileComplete;
use App\Models\UserProfile;
use App\Models\UserProfileCompletedPercentage;

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
            'email' => 'required',
            'password' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $credentials = request(['email', 'password']);
        $credentials['role_id'] = env('JOB_SEEKER_ROLE_ID')??3;
        try{
            $has_login_failed = false;
            if (! $token = auth('api')->attempt($credentials)) {
                $has_login_failed = true;
                if(!empty($request->country_code)){
                    $credentials = request(['country_code', 'password']);
                    $credentials['phone'] = $request->email;
                    $credentials['role_id'] = env('JOB_SEEKER_ROLE_ID')??3;
                    if (! $token = auth('api')->attempt($credentials)) {
                        //return $this->sendError('Unauthorized', 'Login credentials not matched.', Response::HTTP_UNAUTHORIZED);
                        $has_login_failed = true;
                    }else{
                        $has_login_failed = false;
                    }
                }
                // return $this->sendError('Unauthorized', 'Email or Password not matched.', Response::HTTP_UNAUTHORIZED);
            }

            if($has_login_failed == true){
                return $this->sendError('Unauthorized', 'Login credentials not matched.', Response::HTTP_UNAUTHORIZED);
            }
            // Set guard to "api" for the current request
            auth()->shouldUse('api');
            if(auth()->user()->status == 0){
                return $this->sendError('Unauthorized', 'Your account is not active. Please contact to the admin.', Response::HTTP_UNAUTHORIZED);
            }

            return $this->sendResponse([
                                        'token_type' => 'bearer',
                                        'token' => $token,
                                        'user' => $this->getUserDetails(),
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
        $data = [];
        if(auth()->user()->role_id == env('JOB_SEEKER_ROLE_ID')){
            $data = User::where('id', auth()->user()->id)
                    ->with('user_profile')
                    ->with('user_skills')
                    ->with('user_employments')
                    ->with('user_education')
                    // ->with('user_profile_completed_percentages')
                    ->with('user_languages')
                    ->with('user_certification')
                    ->with('user_online_profile')
                    ->with('user_work_sample')
                    ->with('user_it_skill')
                    ->with('user_cv')
                    ->first();

            $profileComplete = ProfileComplete::select('id', 'name', 'percentage')->get()->toArray();
            $profile_completed_percentages = [];
            $total_completed_percentage = 0;
            foreach($profileComplete as $value){
                $has_user_data = UserProfileCompletedPercentage::where('user_id', auth()->user()->id)
                                                                ->where('profile_completes_id', $value['id'])
                                                                ->first();
                $value['completed_percentage'] = $has_user_data ? (int)$value['percentage'] : 0;
                $value['has_completed'] = $has_user_data ? 1 : 0;
                if($has_user_data){
                    $total_completed_percentage += (int)$value['percentage'];
                }

                array_push($profile_completed_percentages, $value);
            }
            $data->user_profile->profile_completed_percentage = $total_completed_percentage;

            UserProfile::where('user_id', auth()->user()->id)
                        ->where('profile_completed_percentage', '!=', $total_completed_percentage)
                        ->update(['profile_completed_percentage'=> $total_completed_percentage]);

            $data->user_profile_completed_percentages = $profile_completed_percentages;


            $user_employment = UserEmployment::where('user_id', auth()->user()->id)
                                                ->where('is_current_job', 1)
                                                ->with('employer')
                                                ->first();
            if(!$user_employment){
                $user_employment = UserEmployment::where('user_id', auth()->user()->id)
                                                ->latest()
                                                ->with('employer')
                                                ->first();
            }
            $data->current_designation = $user_employment ? Designation::find($user_employment->last_designation) : [];
            $data->current_company = $user_employment ? $user_employment->employer : [];
            $data->shortlisted_jobs_count = ShortlistedJob::where('user_id', auth()->user()->id)->count();
            $data->applied_jobs_count = PostJobUserApplied::where('user_id', auth()->user()->id)->count();
            $data->job_alerts_count = 0;
            $postJobObj = new PostJob();
            $jobSql = $postJobObj->get_job_search_custom_sql();
            $data->matched_jobs_count = $jobSql->count();
            // $data->matched_jobs_sql = $jobSql->toSql();
        }else if(auth()->user()->role_id == env('EMPLOYER_ROLE_ID')){
            $data = $this->getEmployerDetails();
        }

        try {
            return $this->sendResponse(
                $data,
                'User Details'
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
