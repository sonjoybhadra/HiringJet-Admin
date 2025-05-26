<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\SiteAuthService;
use Illuminate\Validation\Rule;
use App\Models\GeneralSetting;
use App\Models\UserActivity;
use Illuminate\Http\Request;

use App\Models\EmailLog;
use App\Helpers\Helper;
use App\Models\User;
use Carbon\Carbon;
use Session;



class AuthController extends Controller
{
     protected $siteAuthService;
    function __construct()
    {
        $this->siteAuthService = new SiteAuthService();
    }
    public function showLogin()
    {
        return view('maincontents.signin');
    }

    public function login(Request $request)
    {
        $authData = $request->validate([
                    'email'     => ['required', 'email'],
                    'password'  => ['required'],
                ]);

                // Add extra conditions to the authData array
                $authData['status']  = 1;
                $authData['role_id'] = 1;

                if (Auth::attempt($authData)) {
                    $request->session()->regenerate();

                    // Store selected user info in session array
                    $user = Auth::user();
                    session([
                            'user_data'     => [
                                'user_id'       => $user->id,
                                'name'          => $user->first_name . ' ' . $user->last_name,
                                'email'         => $user->email,
                                'role_id'       => $user->role_id,
                                'is_user_login' => 1,
                            ]
                    ]);
                    /* user activity */
                        $activityData = [
                            'user_email'        => $user->email,
                            'user_name'         => $user->first_name . ' ' . $user->last_name,
                            'user_type'         => 'ADMIN',
                            'ip_address'        => $request->ip(),
                            'activity_type'     => 1,
                            'activity_details'  => 'Login Success',
                            'platform_type'     => 'WEB',
                        ];
                        UserActivity::insert($activityData);
                    /* user activity */
                    return redirect('dashboard/')->with('success_message', 'Sign-in successfull');
                }
                /* user activity */
                    $activityData = [
                        'user_email'        => $postData['email'],
                        'user_name'         => 'Master Admin',
                        'user_type'         => 'ADMIN',
                        'ip_address'        => $request->ip(),
                        'activity_type'     => 0,
                        'activity_details'  => 'Invalid Email Or Password',
                        'platform_type'     => 'WEB',
                    ];
                    UserActivity::insert($activityData);
                /* user activity */
                return redirect()->back()->with('error_message', 'Invalid credentials or access denied.');
    }

    public function dashboard()
    {
        $title              = 'Dashboard';
        $data = $this->siteAuthService->admin_after_login_layout( $title, 'dashboard', []);
        return view('maincontents.dashboard', $data);
    }

    public function logout(Request $request)
    {
             $user_email                             = Auth::user()->email;
             $user_name                              = Auth::user()->first_name . ' ' . Auth::user()->last_name;
            /* user activity */
                $activityData = [
                    'user_email'        => $user_email,
                    'user_name'         => $user_name,
                    'user_type'         => 'ADMIN',
                    'ip_address'        => $request->ip(),
                    'activity_type'     => 2,
                    'activity_details'  => 'You Are Successfully Logged Out',
                    'platform_type'     => 'WEB',
                ];
                UserActivity::insert($activityData);
            /* user activity */
            $request->session()->forget(['user_data']);
            Auth::guard('user')->logout();
            return redirect('/')->with('success_message', 'You Are Successfully Logged Out');

    }
}
