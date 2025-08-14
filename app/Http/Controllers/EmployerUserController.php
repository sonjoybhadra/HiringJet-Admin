<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\GeneralSetting;
use App\Models\Employer;
use App\Models\UserEmployer;
use App\Models\EmployerBrand;
use App\Models\EmployerCvFolder;
use App\Models\EmployerCvProfile;
use App\Models\EmployerTag;
use App\Models\Industry;
use App\Models\Designation;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\UserActivity;
use App\Services\SiteAuthService;
use App\Helpers\Helper;
use App\Models\User;
use Auth;
use Session;
use DB;

use App\Mail\SignupOtp;
use App\Mail\RegistrationSuccess;

class EmployerUserController extends Controller
{
    protected $siteAuthService;
    public function __construct()
    {
        $this->siteAuthService = new SiteAuthService();
        $this->data = array(
            'title'             => 'Employer User',
            'controller'        => 'EmployerUserController',
            'controller_route'  => 'employer-user',
            'primary_key'       => 'id',
            'table_name'        => 'user_employers',
        );
    }
    /* list */
        public function list(){
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' List';
            $page_name                      = 'employer-user.list';
            $data                           = $this->siteAuthService ->admin_after_login_layout($title,$page_name,$data);
            return view('maincontents.' . $page_name, $data);
        }
        public function verified(){
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' Verified List';
            $page_name                      = 'employer-user.verified';
            $data                           = $this->siteAuthService ->admin_after_login_layout($title,$page_name,$data);
            return view('maincontents.' . $page_name, $data);
        }
        public function nonVerified(){
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' Non-Verified List';
            $page_name                      = 'employer-user.non-verified';
            $data                           = $this->siteAuthService ->admin_after_login_layout($title,$page_name,$data);
            return view('maincontents.' . $page_name, $data);
        }
        public function decline(){
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' Declined List';
            $page_name                      = 'employer-user.decline';
            $data                           = $this->siteAuthService ->admin_after_login_layout($title,$page_name,$data);
            return view('maincontents.' . $page_name, $data);
        }
    /* list */
    /* add */
        public function add(Request $request){
            $data['module']           = $this->data;
            if($request->isMethod('post')){
                $postData = $request->all();
                $rules = [
                    'first_name'            => 'required|string|max:100',
                    'last_name'             => 'required|string|max:100',
                    'email'                 => 'required|email|max:150|unique:users',
                    'country_code'          => 'required|max:5',
                    'phone'                 => 'required|max:15|unique:users',
                    'password'              => 'required|min:6',
                    'confirm_password'      => 'required|same:password',
                    'business_id'           => 'required|integer',
                    'designation_id'        => 'required|integer',
                ];
                if($this->validate($request, $rules)){
                    $first_name = $postData['first_name'];
                    $last_name = $postData['last_name'];
                    $name = $first_name . ' ' .$last_name;
                    $checkEmployer = User::where('first_name', '=', $first_name)->where('last_name', '=', $last_name)->count();
                    if($checkEmployer <= 0){
                        $checkEmail = User::where('email', '=', $postData['email'])->count();
                        if($checkEmail > 0){
                            return redirect()->back()->with('error_message', 'Email already exists !!!');
                        } else {
                            $checkPhone = User::where('phone', '=', $postData['phone'])->count();
                            if($checkPhone > 0){
                                return redirect()->back()->with('error_message', 'Phone number already exists !!!');
                            } else {
                                if($postData['password'] != $postData['confirm_password']){
                                    return redirect()->back()->with('error_message', 'Password & confirm passowrd does not matched !!!');
                                } else {
                                    $otp            = mt_rand(1111, 9999);
                                    $otp_mail_hash  = base64_encode($otp);

                                    $fields = [
                                        'role_id'                   => 2,
                                        'first_name'                => strip_tags($postData['first_name']),
                                        'last_name'                 => strip_tags($postData['last_name']),
                                        'email'                     => strip_tags($postData['email']),
                                        'country_code'              => strip_tags($postData['country_code']),
                                        'phone'                     => strip_tags($postData['phone']),
                                        'password'                  => Hash::make($request->password),
                                        // 'confirm_password'          => Hash::make($request->confirm_password),
                                        'status'                    => 0,
                                        'remember_token'            => $otp_mail_hash,
                                        'email_verified_at'         => date('Y-m-d H:i:s'),
                                        'created_at'                => date('Y-m-d H:i:s'),
                                        'updated_at'                => date('Y-m-d H:i:s'),
                                    ];
                                    // Helper::pr($fields);
                                    $user_id = User::insertGetId($fields);

                                    if($user_id){
                                        UserEmployer::insert([
                                            'user_id'           => $user_id,
                                            'first_name'        => $request->first_name,
                                            'last_name'         => $request->last_name,
                                            'email'             => $request->email,
                                            'country_code'      => $request->country_code,
                                            'phone'             => $request->phone,
                                            'business_id'       => $request->business_id,
                                            'designation_id'    => $request->designation_id,
                                            'completed_steps'   => 0
                                        ]);

                                        $full_name = $request->first_name.' '.$request->last_name;
                                        $message = 'Registration step 1 has successfully done. Please verify activation OTP.';
                                        Mail::to($request->email)->send(new SignupOtp($full_name, $otp, $message, 'Signup OTP'));

                                        return redirect($this->data['controller_route'] . "/verify-otp/" . Helper::encoded($user_id))->with('success_message', 'Registration step 1 has done. Please verify OTP already send in your registered email.');
                                    }else{
                                        return redirect()->back()->with('error_message', 'Sorry!! Unable to signup.');
                                    }
                                }
                            }
                        }
                    } else {
                        return redirect()->back()->with('error_message', 'Employer user already exists with this name');
                    }
                } else {
                    return redirect()->back()->with('error_message', 'All Fields Required !!!');
                }
            }
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' Add';
            $page_name                      = 'employer-user.add-edit';
            $data['row']                    = [];
            $data['business']               = Employer::select('id', 'name')->where('status', '=', 1)->orderBy('name', 'ASC')->get();
            $data['designations']           = Designation::select('id', 'name')->where('status', '=', 1)->orderBy('name', 'ASC')->get();
            $data['countries']              = Country::select('country_code', 'name')->where('status', '=', 1)->orderBy('name', 'ASC')->get();

            $data                           = $this->siteAuthService ->admin_after_login_layout($title,$page_name,$data);
            return view('maincontents.' . $page_name, $data);
        }
    /* add */
    /* edit */
        public function edit(Request $request, $id){
            $data['module']                 = $this->data;
            $id                             = Helper::decoded($id);
            $title                          = $this->data['title'].' Update';
            $page_name                      = 'employer-user.add-edit';
            $data['row']                    = Employer::where('id', '=', $id)->first();
            $data['business']               = Employer::select('id', 'name')->where('status', '=', 1)->orderBy('name', 'ASC')->get();
            $data['designations']           = Designation::select('id', 'name')->where('status', '=', 1)->orderBy('name', 'ASC')->get();
            $data['countries']              = Country::select('country_code', 'name')->where('status', '=', 1)->orderBy('name', 'ASC')->get();

            if($request->isMethod('post')){
                $postData = $request->all();
                $rules = [
                    'first_name'            => 'required',
                    'last_name'             => 'required',
                    'email'                 => 'required',
                    'country_code'          => 'required',
                    'phone'                 => 'required',
                    'business_id'           => 'required',
                    'designation_id'        => 'required',
                ];
                if($this->validate($request, $rules)){
                    $name = $postData['name'];
                    $checkEmployer = Employer::where('name', '=', $name)->where('id', '!=', $id)->count();
                    if($checkEmployer <= 0){
                        /* logo */
                            $upload_folder = 'employer';
                            $imageFile      = $request->file('logo');
                            if($imageFile != ''){
                                $imageName      = $imageFile->getClientOriginalName();
                                $uploadedFile   = $this->upload_single_file('logo', $imageName, $upload_folder, 'image');
                                if($uploadedFile['status']){
                                    $logo = $uploadedFile['newFilename'];
                                    $logoLink = '/uploads/' . $upload_folder . '/' . $logo;
                                } else {
                                    return redirect()->back()->with(['error_message' => $uploadedFile['message']]);
                                }
                            } else {
                                $logo = $data['row']->logo;
                                $logoLink = $logo;
                            }
                        /* logo */
                        $fields = [
                            'name'                  => strip_tags($postData['name']),
                            'description'           => strip_tags($postData['description']),
                            'industry_id'           => strip_tags($postData['industry_id']),
                            'no_of_employee'        => strip_tags($postData['no_of_employee']),
                            'logo'                  => $logoLink,
                            'status'                => ((array_key_exists("status",$postData))?1:0),
                            'created_by'            => session('user_data')['user_id'],
                        ];
                        Employer::where($this->data['primary_key'], '=', $id)->update($fields);
                        /* user activity */
                            $activityData = [
                                'user_email'        => session('user_data')['email'],
                                'user_name'         => session('user_data')['name'],
                                'user_type'         => 'ADMIN',
                                'ip_address'        => $request->ip(),
                                'activity_type'     => 3,
                                'activity_details'  => $postData['name'] . ' ' . $this->data['title'] . ' Updated',
                                'platform_type'     => 'WEB',
                            ];
                            UserActivity::insert($activityData);
                        /* user activity */
                        return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' Updated Successfully !!!');
                    } else {
                        return redirect()->back()->with('error_message', 'Employer already exists with this name');
                    }
                } else {
                    return redirect()->back()->with('error_message', 'All Fields Required !!!');
                }
            }
            $data                           = $this->siteAuthService ->admin_after_login_layout($title,$page_name,$data);
            return view('maincontents.' . $page_name, $data);
        }
    /* edit */
    /* delete */
        public function delete(Request $request, $id){
            $id                             = Helper::decoded($id);
            $model                          = UserEmployer::find($id);
            $business_id                    = (($model)?$model->business_id:0);
            $getBusiness                    = Employer::where('id', '=', $business_id)->first();
            Employer::where('id', '=', $business_id)->update(['status' => 3]);
            $fields = [
                'status'             => 3,
                'deleted_at'         => date('Y-m-d H:i:s'),
            ];
            Employer::where($this->data['primary_key'], '=', $id)->update($fields);
            /* user activity */
                $activityData = [
                    'user_email'        => session('user_data')['email'],
                    'user_name'         => session('user_data')['name'],
                    'user_type'         => 'ADMIN',
                    'ip_address'        => $request->ip(),
                    'activity_type'     => 3,
                    'activity_details'  => $model->name . ' ' . $this->data['title'] . ' Deleted',
                    'platform_type'     => 'WEB',
                ];
                UserActivity::insert($activityData);
            /* user activity */
            return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' Deleted Successfully !!!');
        }
    /* delete */
    /* change status */
        public function change_status(Request $request, $id, $statusNo){
            $id                             = Helper::decoded($id);
            $model                          = UserEmployer::find($id);
            $business_id                    = (($model)?$model->business_id:0);
            $getBusiness                    = Employer::where('id', '=', $business_id)->first();
            if($getBusiness){
                Employer::where('id', '=', $business_id)->update(['status' => $statusNo]);
                if ($statusNo == 2)
                { 
                    $msg            = 'Declined';
                    $redirectRoute  = 'decline';
                    /* user activity */
                        $activityData = [
                            'user_email'        => session('user_data')['email'],
                            'user_name'         => session('user_data')['name'],
                            'user_type'         => 'ADMIN',
                            'ip_address'        => $request->ip(),
                            'activity_type'     => 3,
                            'activity_details'  => $model->name . ' ' . $this->data['title'] . ' Declined',
                            'platform_type'     => 'WEB',
                        ];
                        UserActivity::insert($activityData);
                    /* user activity */
                } elseif ($statusNo == 4) {
                    $msg            = 'Verified';
                    $redirectRoute  = 'verified';
                    /* user activity */
                        $activityData = [
                            'user_email'        => session('user_data')['email'],
                            'user_name'         => session('user_data')['name'],
                            'user_type'         => 'ADMIN',
                            'ip_address'        => $request->ip(),
                            'activity_type'     => 3,
                            'activity_details'  => $model->name . ' ' . $this->data['title'] . ' Verified',
                            'platform_type'     => 'WEB',
                        ];
                        UserActivity::insert($activityData);
                    /* user activity */
                }
            }
            return redirect($this->data['controller_route'] . "/" . $redirectRoute)->with('success_message', $this->data['title'].' '.$msg.' Successfully !!!');
        }
    /* change status */
    /* resend otp */
        public function resendOtp(Request $request, $id)
        {
            $id                             = Helper::decoded($id);
            $user = User::where('id', '=', $id)->first();
            if(!$user){
                return redirect()->back()->with(['error_message' => 'User not found']);
            } else {
                $otp = mt_rand(1111, 9999);
                $otp_mail_hash = base64_encode($otp);

                $user->remember_token = $otp_mail_hash;
                $user->save();

                $full_name = $user->first_name.' '.$user->last_name;
                $message = 'Registration step 1 has successfully done. Please verify activation OTP.';
                Mail::to($request->email)->send(new SignupOtp($full_name, $otp, $message, 'Signup OTP'));

                return redirect($this->data['controller_route'] . "/verify-otp/" . Helper::encoded($id))->with(['success_message' => 'OTP resend successfully. Please verify OTP already send in your registered email.']);
            }
        }
    /* resend otp */
    /* verify otp */
        public function verifyOtp(Request $request, $id){
            $data['module']                 = $this->data;
            $id                             = Helper::decoded($id);
            $page_name                      = 'employer-user.verify-otp';
            $data['row']                    = UserEmployer::where('id', '=', $id)->first();
            $business_id                    = (($data['row'])?$data['row']->business_id:0);
            $data['id']                     = $id;
            $title                          = 'Verify OTP : ' . (($data['row'])?$data['row']->first_name . '' . $data['row']->last_name:'');

            if($request->isMethod('post')){
                $postData = $request->all();
                $rules = [
                    'otp'            => 'required',
                ];
                if($this->validate($request, $rules)){
                    $user_employer              = UserEmployer::where('id', '=', $id)->first();
                    $user_id                    = (($user_employer)?$user_employer->user_id:0);
                    $business_id                = (($user_employer)?$user_employer->business_id:0);

                    $user                       = User::where('id', '=', $user_id)->first();
                    $remember_token             = (($user)?base64_decode($user->remember_token):'');

                    if($remember_token == $postData['otp']){
                        $user_obj = User::find($id);
                        $user_obj->status = 1;
                        $user_obj->remember_token = '';
                        $user_obj->email_verified_at = date('Y-m-d H:i:s');
                        $user_obj->save();

                        UserEmployer::where('user_id', $id)->update([
                            'completed_steps'=> 1,
                        ]);
                        Employer::where('id', $business_id)->update([
                            'status'=> 1,
                        ]);

                        $full_name  = $user->first_name.' '.$user->last_name;
                        $message    = 'Your account verification has successfully completed. Now you can continue and complete your profile.';
                        Mail::to($user->email)->send(new RegistrationSuccess($user->email, $full_name, $message));

                        return redirect($this->data['controller_route'] . "/non-verified")->with('success_message', 'Your account verification has successfully done. Now you can continue and complete your profile.');
                    } else {
                        return redirect()->back()->with('error_message', 'OTP mismatched !!!');
                    }
                } else {
                    return redirect()->back()->with('error_message', 'All Fields Required !!!');
                }
            }
            $data                           = $this->siteAuthService ->admin_after_login_layout($title,$page_name,$data);
            return view('maincontents.' . $page_name, $data);
        }
    /* verify otp */
    /* profile */
        public function profile(Request $request, $id){
            $data['module']                 = $this->data;
            $id                             = Helper::decoded($id);
            $page_name                      = 'employer-user.profile';
            $data['id']                     = $id;
            $data['row']                    = DB::table('users')
                                                ->join('user_employers', 'user_employers.user_id', '=', 'users.id')
                                                ->select('users.*', 'user_employers.*')
                                                ->where('users.id', '=', $id)
                                                ->first();

            $name                           = (($data['row'])?$data['row']->first_name.' '.$data['row']->last_name:'');
            $phone                          = (($data['row'])?$data['row']->phone:'');
            $title                          = $this->data['title'].' Profile : '.$name.' ('.$phone.')';
            
            $data                           = $this->siteAuthService ->admin_after_login_layout($title,$page_name,$data);
            return view('maincontents.' . $page_name, $data);
        }
    /* profile */
    /* create business */
        public function createBusiness(Request $request, $id){
            $data['module']                 = $this->data;
            $id                             = Helper::decoded($id);
            $page_name                      = 'employer-user.create-business';
            $data['id']                     = $id;
            $data['row']                    = DB::table('users')
                                                ->join('user_employers', 'user_employers.user_id', '=', 'users.id')
                                                ->select('users.*', 'user_employers.*')
                                                ->where('user_employers.id', '=', $id)
                                                ->first();

            $name                           = (($data['row'])?$data['row']->first_name.' '.$data['row']->last_name:'');
            $phone                          = (($data['row'])?$data['row']->phone:'');
            $title                          = $this->data['title'].' Create Business : '.$name.' ('.$phone.')';

            $data['industries']             = Industry::select('id', 'name')->where('status', '=', 1)->orderBy('name', 'ASC')->get();
            $data['countries']              = Country::select('id', 'name', 'country_code')->where('status', '=', 1)->orderBy('name', 'ASC')->get();

            $data['selectedCountryId']      = (($data['row'])?$data['row']->country_id:''); // e.g. India
            $data['selectedStateId']        = (($data['row'])?$data['row']->state_id:'');   // e.g. Maharashtra
            $data['selectedCityId']         = (($data['row'])?$data['row']->city_id:'');   // e.g. Mumbai

            if($request->isMethod('post')){
                if($data['row']->country_id == ''){
                    $validator = Validator::make($request->all(), [
                        'address' => 'required|string|max:255',
                        'country' => 'required',
                        'state' => 'required',
                        'city' => 'required',
                        'pincode' => 'required|string|max:10',
                        'country_code' => 'nullable|required|max:5',
                        'landline' => 'nullable|string|max:20',
                        'trade_license' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',// Max:5MB
                        'vat_registration' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',// Max:5MB
                        'logo' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',// Max:5MB
                        'description' => 'required|string',
                        'industrie_id' => 'required|integer',
                        'web_url' => 'required'
                    ]);
                } else {
                    $validator = Validator::make($request->all(), [
                        'address' => 'required|string|max:255',
                        'country' => 'required',
                        'state' => 'required',
                        'city' => 'required',
                        'pincode' => 'required|string|max:10',
                        'country_code' => 'nullable|required|max:5',
                        'landline' => 'nullable|string|max:20',
                        'trade_license' => 'image|mimes:jpeg,png,jpg,webp|max:5120',// Max:5MB
                        'vat_registration' => 'image|mimes:jpeg,png,jpg,webp|max:5120',// Max:5MB
                        'logo' => 'image|mimes:jpeg,png,jpg,webp|max:5120',// Max:5MB
                        'description' => 'required|string',
                        'industrie_id' => 'required|integer',
                        'web_url' => 'required'
                    ]);
                }
                

                // if($validator->fails()){
                //     // return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
                // }

                try{
                    $profile_image = $trade_license = $vat_registration = $logo = "";
                    if (request()->hasFile('profile_image')) {
                        $file = request()->file('profile_image');
                        $fileName = md5($file->getClientOriginalName() .'_'. time()) . "." . $file->getClientOriginalExtension();
                        Storage::disk('public')->put('uploads/employer/profile_image/'.$fileName, file_get_contents($file));
                        $profile_image = 'public/storage/uploads/employer/profile_image/'.$fileName;
                    } else {
                        $profile_image = $data['row']->profile_image;
                    }
                    if (request()->hasFile('trade_license')) {
                        $file = request()->file('trade_license');
                        $fileName = md5($file->getClientOriginalName() .'_'. time()) . "." . $file->getClientOriginalExtension();
                        Storage::disk('public')->put('uploads/employer/trade_license/'.$fileName, file_get_contents($file));
                        $trade_license = 'public/storage/uploads/employer/trade_license/'.$fileName;
                    } else {
                        $trade_license = $data['row']->trade_license;
                    }
                    if (request()->hasFile('vat_registration')) {
                        $file = request()->file('vat_registration');
                        $fileName = md5($file->getClientOriginalName() .'_'. time()) . "." . $file->getClientOriginalExtension();
                        Storage::disk('public')->put('uploads/employer/vat_registration/'.$fileName, file_get_contents($file));
                        $vat_registration = 'public/storage/uploads/employer/vat_registration/'.$fileName;
                    } else {
                        $vat_registration = $data['row']->vat_registration;
                    }
                    if (request()->hasFile('logo')) {
                        $file = request()->file('logo');
                        $fileName = md5($file->getClientOriginalName() .'_'. time()) . "." . $file->getClientOriginalExtension();
                        Storage::disk('public')->put('uploads/employer/logo/'.$fileName, file_get_contents($file));
                        $logo = 'public/storage/uploads/employer/logo/'.$fileName;
                    } else {
                        $logo = $data['row']->logo;
                    }

                    // $city = new City();
                    // $country = new Country();
                    // $state = new State();
                    // $country_id = $country->getCountryId($request->country);
                    // $state_id = $state->getStateId($request->state, $country_id);
                    // $city_id = $city->getCityId($request->city, $country_id);

                    UserEmployer::where('id', $id)->update([
                        'country_id'        => $request->country,
                        'city_id'           => $request->city,
                        'state_id'          => $request->state,
                        'address'           => $request->address,
                        'address_line_2'    => $request->address_line_2,
                        'pincode'           => $request->pincode,
                        'landline'          => $request->landline,
                        'industrie_id'      => $request->industrie_id,
                        'profile_image'     => $profile_image,
                        'trade_license'     => $trade_license,
                        'vat_registration'  => $vat_registration,
                        'logo'              => $logo,
                        'description'       => $request->description,
                        'web_url'           => $request->web_url,
                        'employe_type'      => 'company',
                        'completed_steps'   => 2,
                        'created_at'        => date('Y-m-d H:i:s'),
                        'updated_at'        => date('Y-m-d H:i:s'),
                    ]);

                    $business_id                          = (($data['row'])?$data['row']->business_id:'');
                    Employer::where('id', $business_id)->update([
                        'country_id'        => $request->country,
                        'city_id'           => $request->city,
                        'state_id'          => $request->state,
                        'address'           => $request->address,
                        'address_line_2'    => $request->address_line_2,
                        'pincode'           => $request->pincode,
                        'landline'          => $request->landline,
                        'trade_license'     => $trade_license,
                        'vat_registration'  => $vat_registration,
                        'logo'              => $logo,
                        'description'       => $request->description,
                        'web_url'           => $request->web_url,
                        'employe_type'      => 'company',
                    ]);

                    return redirect($this->data['controller_route'] . "/create-business/" . Helper::encoded($id))->with(['success_message' => 'Setup company profile has successfully done.']);

                } catch (\Exception $e) {
                    return redirect($this->data['controller_route'] . "/create-business/" . Helper::encoded($id))->with(['error_message' => 'Sorry!! Unable to complete setup profile.']);
                }
            }
            
            $data                           = $this->siteAuthService ->admin_after_login_layout($title,$page_name,$data);
            return view('maincontents.' . $page_name, $data);
        }
        public function getStates(Request $request)
        {
            $states = \App\Models\State::where('country_id', $request->country_id)->pluck('name', 'id');
            return response()->json($states);
        }

        public function getCities(Request $request)
        {
            $cities = \App\Models\City::where('country_id', $request->country_id)->pluck('name', 'id');
            return response()->json($cities);
        }
    /* create business */
}
