<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\GeneralSetting;
use App\Models\Role;
use App\Models\Module;
use App\Models\UserActivity;
use App\Services\SiteAuthService;
use App\Helpers\Helper;
use Auth;
use Session;
use Hash;

class RoleController extends Controller
{
    protected $siteAuthService;
    public function __construct()
    {
        $this->siteAuthService = new SiteAuthService();
        $this->data = array(
            'title'             => 'Role',
            'controller'        => 'RoleController',
            'controller_route'  => 'role',
            'primary_key'       => 'id',
        );
    }
    /* list */
        public function list(){
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' List';
            $page_name                      = 'role.list';
            $data                           = $this->siteAuthService ->admin_after_login_layout($title,$page_name,$data);
            $data['rows']                   = Role::where('status', '!=', 3)->orderBy('id', 'DESC')->get();
            return view('maincontents.' . $page_name, $data);
        }
    /* list */
    /* add */
        public function add(Request $request){
            $data['module']           = $this->data;
            if($request->isMethod('post')){
                $postData = $request->all();
                $rules = [
                    'role_name'           => 'required',
                ];
                if($this->validate($request, $rules)){
                    /* user activity */
                        $activityData = [
                            'user_email'        => session('user_data')['email'],
                            'user_name'         => session('user_data')['name'],
                            'user_type'         => 'ADMIN',
                            'ip_address'        => $request->ip(),
                            'activity_type'     => 3,
                            'activity_details'  => $postData['role_name'] . ' ' . $this->data['title'] . ' Added',
                            'platform_type'     => 'WEB',
                        ];
                        UserActivity::insert($activityData);
                    /* user activity */
                    $fields = [
                        'role_name'         => strip_tags($postData['role_name']),
                        'module_id'         => ((!empty($postData['module_id']))?json_encode($postData['module_id']):''),
                    ];
                    Role::insert($fields);
                    return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' Inserted Successfully !!!');
                } else {
                    return redirect()->back()->with('error_message', 'All Fields Required !!!');
                }
            }
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' Add';
            $page_name                      = 'role.add-edit';
            $data['row']                    = [];
            $data['modules']                = Module::select('id', 'name')->where('status', '=', 1)->get();
            $data                           = $this->siteAuthService ->admin_after_login_layout($title,$page_name,$data);
            return view('maincontents.' . $page_name, $data);
        }
    /* add */
    /* edit */
        public function edit(Request $request, $id){
            $data['module']                 = $this->data;
            $id                             = Helper::decoded($id);
            $title                          = $this->data['title'].' Update';
            $page_name                      = 'role.add-edit';
            $data['row']                    = Role::where($this->data['primary_key'], '=', $id)->first();
            $data['modules']                = Module::select('id', 'name')->where('status', '=', 1)->get();
            if($request->isMethod('post')){
                $postData = $request->all();
                $rules = [
                    'role_name'           => 'required',
                ];
                if($this->validate($request, $rules)){
                    $fields = [
                        'role_name'         => strip_tags($postData['role_name']),
                        'module_id'         => ((!empty($postData['module_id']))?json_encode($postData['module_id']):''),
                    ];
                    Role::where($this->data['primary_key'], '=', $id)->update($fields);
                    /* user activity */
                        $activityData = [
                            'user_email'        => session('user_data')['email'],
                            'user_name'         => session('user_data')['name'],
                            'user_type'         => 'ADMIN',
                            'ip_address'        => $request->ip(),
                            'activity_type'     => 3,
                            'activity_details'  => $postData['role_name'] . ' ' . $this->data['title'] . ' Updated',
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
            $model                          = Role::find($id);
            $fields = [
                'status'             => 3,
                'deleted_at'         => date('Y-m-d H:i:s'),
            ];
            Role::where($this->data['primary_key'], '=', $id)->update($fields);
            /* user activity */
                $activityData = [
                    'user_email'        => session('user_data')['email'],
                    'user_name'         => session('user_data')['name'],
                    'user_type'         => 'ADMIN',
                    'ip_address'        => $request->ip(),
                    'activity_type'     => 3,
                    'activity_details'  => $model->role_name . ' ' . $this->data['title'] . ' Deleted',
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
            $model                          = Role::find($id);
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
                        'activity_details'  => $model->role_name . ' ' . $this->data['title'] . ' Deactivated',
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
                        'activity_details'  => $model->role_name . ' ' . $this->data['title'] . ' Activated',
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
