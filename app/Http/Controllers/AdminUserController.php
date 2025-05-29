<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\GeneralSetting;
use App\Models\Role;
use App\Models\User;
use App\Models\UserActivity;
use App\Services\SiteAuthService;
use App\Helpers\Helper;
use Auth;
use Session;
use Hash;

class AdminUserController extends Controller
{
    protected $siteAuthService;
    public function __construct()
    {
        $this->siteAuthService = new SiteAuthService();
        $this->data = array(
            'title'             => 'Admin User',
            'controller'        => 'AdminUserController',
            'controller_route'  => 'admin-user',
            'primary_key'       => 'id',
            'table_name'        => 'users',
        );
    }
    /* list */
        public function list(){
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' List';
            $page_name                      = 'admin-user.list';
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
                    'role_id'               => 'required',
                    'first_name'            => 'required',
                    'last_name'             => 'required',
                    'email'                 => 'required',
                    'country_code'          => 'required',
                    'phone'                 => 'required',
                    'password'              => 'required',
                ];
                if($this->validate($request, $rules)){
                    /* user activity */
                        $activityData = [
                            'user_email'        => session('user_data')['email'],
                            'user_name'         => session('user_data')['name'],
                            'user_type'         => 'ADMIN',
                            'ip_address'        => $request->ip(),
                            'activity_type'     => 3,
                            'activity_details'  => $postData['first_name'] . ' ' . $this->data['title'] . ' Added',
                            'platform_type'     => 'WEB',
                        ];
                        UserActivity::insert($activityData);
                    /* user activity */
                    $fields = [
                        'role_id'               => strip_tags($postData['role_id']),
                        'first_name'            => strip_tags($postData['first_name']),
                        'last_name'             => strip_tags($postData['last_name']),
                        'email'                 => strip_tags($postData['email']),
                        'country_code'          => strip_tags($postData['country_code']),
                        'phone'                 => strip_tags($postData['phone']),
                        'password'              => Hash::make(strip_tags($postData['password'])),
                        'status'                => ((array_key_exists("status",$postData))?1:0),
                    ];
                    User::insert($fields);
                    return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' Inserted Successfully !!!');
                } else {
                    return redirect()->back()->with('error_message', 'All Fields Required !!!');
                }
            }
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' Add';
            $page_name                      = 'admin-user.add-edit';
            $data['row']                    = [];
            $data['roles']                  = Role::select('id', 'role_name')->where('status', '=', 1)->get();
            $data                           = $this->siteAuthService ->admin_after_login_layout($title,$page_name,$data);
            return view('maincontents.' . $page_name, $data);
        }
    /* add */
    /* edit */
        public function edit(Request $request, $id){
            $data['module']                 = $this->data;
            $id                             = Helper::decoded($id);
            $title                          = $this->data['title'].' Update';
            $page_name                      = 'admin-user.add-edit';
            $data['row']                    = User::where($this->data['primary_key'], '=', $id)->first();
            $data['roles']                  = Role::select('id', 'role_name')->where('status', '=', 1)->get();

            if($request->isMethod('post')){
                $postData = $request->all();
                $rules = [
                    'role_id'               => 'required',
                    'first_name'            => 'required',
                    'last_name'             => 'required',
                    'email'                 => 'required',
                    'country_code'          => 'required',
                    'phone'                 => 'required',
                ];
                if($this->validate($request, $rules)){
                    if($postData['password'] != ''){
                        $fields = [
                            'role_id'               => strip_tags($postData['role_id']),
                            'first_name'            => strip_tags($postData['first_name']),
                            'last_name'             => strip_tags($postData['last_name']),
                            'email'                 => strip_tags($postData['email']),
                            'country_code'          => strip_tags($postData['country_code']),
                            'phone'                 => strip_tags($postData['phone']),
                            'password'              => Hash::make(strip_tags($postData['password'])),
                            'status'                => ((array_key_exists("status",$postData))?1:0),
                        ];
                    } else {
                        $fields = [
                            'role_id'               => strip_tags($postData['role_id']),
                            'first_name'            => strip_tags($postData['first_name']),
                            'last_name'             => strip_tags($postData['last_name']),
                            'email'                 => strip_tags($postData['email']),
                            'country_code'          => strip_tags($postData['country_code']),
                            'phone'                 => strip_tags($postData['phone']),
                            'status'                => ((array_key_exists("status",$postData))?1:0),
                        ];
                    }
                    User::where($this->data['primary_key'], '=', $id)->update($fields);
                    /* user activity */
                        $activityData = [
                            'user_email'        => session('user_data')['email'],
                            'user_name'         => session('user_data')['name'],
                            'user_type'         => 'ADMIN',
                            'ip_address'        => $request->ip(),
                            'activity_type'     => 3,
                            'activity_details'  => $postData['first_name'] . ' ' . $this->data['title'] . ' Updated',
                            'platform_type'     => 'WEB',
                        ];
                        UserActivity::insert($activityData);
                    /* user activity */
                    return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' Updated Successfully !!!');
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
            $model                          = User::find($id);
            $fields = [
                'status'             => 3,
                'deleted_at'         => date('Y-m-d H:i:s'),
            ];
            User::where($this->data['primary_key'], '=', $id)->update($fields);
            /* user activity */
                $activityData = [
                    'user_email'        => session('user_data')['email'],
                    'user_name'         => session('user_data')['name'],
                    'user_type'         => 'ADMIN',
                    'ip_address'        => $request->ip(),
                    'activity_type'     => 3,
                    'activity_details'  => $model->first_name . ' ' . $this->data['title'] . ' Deleted',
                    'platform_type'     => 'WEB',
                ];
                UserActivity::insert($activityData);
            /* user activity */
            return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' Deleted Successfully !!!');
        }
    /* delete */
    /* change status */
        public function change_status(Request $request, $id){
            $id                             = Helper::decoded($id);
            $model                          = User::find($id);
            if ($model->status == 1)
            {
                $model->status  = 0;
                $msg            = 'Deactivated';
                /* user activity */
                    $activityData = [
                        'user_email'        => session('user_data')['email'],
                        'user_name'         => session('user_data')['name'],
                        'user_type'         => 'ADMIN',
                        'ip_address'        => $request->ip(),
                        'activity_type'     => 3,
                        'activity_details'  => $model->first_name . ' ' . $this->data['title'] . ' Deactivated',
                        'platform_type'     => 'WEB',
                    ];
                    UserActivity::insert($activityData);
                /* user activity */
            } else {
                $model->status  = 1;
                $msg            = 'Activated';
                /* user activity */
                    $activityData = [
                        'user_email'        => session('user_data')['email'],
                        'user_name'         => session('user_data')['name'],
                        'user_type'         => 'ADMIN',
                        'ip_address'        => $request->ip(),
                        'activity_type'     => 3,
                        'activity_details'  => $model->question . ' ' . $this->data['title'] . ' Activated',
                        'platform_type'     => 'WEB',
                    ];
                    UserActivity::insert($activityData);
                /* user activity */
            }            
            $model->save();
            return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' '.$msg.' Successfully !!!');
        }
    /* change status */
}
