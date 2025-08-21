<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\GeneralSetting;
use App\Models\User;
use App\Models\Newsletter;
use App\Models\UserActivity;
use App\Services\SiteAuthService;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

use Auth;
use Session;
use Hash;

use App\Mail\NewsletterContent;

class NewsletterController extends Controller
{
    protected $siteAuthService;
    public function __construct()
    {
        $this->siteAuthService = new SiteAuthService();
        $this->data = array(
            'title'             => 'Newsletter',
            'controller'        => 'NewsletterController',
            'controller_route'  => 'newsletter',
            'primary_key'       => 'id',
        );
    }
    /* list */
        public function list(){
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' List';
            $page_name                      = 'newsletter.list';
            $data['rows']                   = Newsletter::where('status', '!=', 3)->orderBy('id', 'DESC')->get();
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
                    'title'                 => 'required',
                    'description'           => 'required',
                ];
                if($this->validate($request, $rules)){
                    /* user activity */
                        $activityData = [
                            'user_email'        => session('user_data')['email'],
                            'user_name'         => session('user_data')['name'],
                            'user_type'         => 'ADMIN',
                            'ip_address'        => $request->ip(),
                            'activity_type'     => 3,
                            'activity_details'  => $postData['title'] . ' ' . $this->data['title'] . ' Added',
                            'platform_type'     => 'WEB',
                        ];
                        UserActivity::insert($activityData);
                    /* user activity */
                    if($postData['to_users'] == 0){
                        $actual_users = $postData['all_users'];
                    } elseif($postData['to_users'] == 1){
                        $actual_users = $postData['jobseeker_users'];
                    } elseif($postData['to_users'] == 2){
                        $actual_users = $postData['employer_users'];
                    }
                    $fields = [
                        'title'             => strip_tags($postData['title']),
                        'description'       => strip_tags($postData['description']),
                        'to_users'          => strip_tags($postData['to_users']),
                        'users'             => json_encode($actual_users),
                        'status'            => ((array_key_exists("status",$postData))?1:0),
                    ];
                    Newsletter::insert($fields);
                    return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' Inserted Successfully !!!');
                } else {
                    return redirect()->back()->with('error_message', 'All Fields Required !!!');
                }
            }
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' Add';
            $page_name                      = 'newsletter.add-edit';
            $data['row']                    = [];
            $data['all_users']              = User::select('id', 'first_name', 'last_name')->where('status', '=', 1)->orderBy('first_name', 'ASC')->get();
            $data['jobseeker_users']        = User::select('id', 'first_name', 'last_name')->where('status', '=', 1)->where('role_id', '=', 3)->orderBy('first_name', 'ASC')->get();
            $data['employer_users']         = User::select('id', 'first_name', 'last_name')->where('status', '=', 1)->where('role_id', '=', 2)->orderBy('first_name', 'ASC')->get();

            $data                           = $this->siteAuthService ->admin_after_login_layout($title,$page_name,$data);
            return view('maincontents.' . $page_name, $data);
        }
    /* add */
    /* edit */
        public function edit(Request $request, $id){
            $data['module']                 = $this->data;
            $id                             = Helper::decoded($id);
            $title                          = $this->data['title'].' Update';
            $page_name                      = 'newsletter.add-edit';
            $data['row']                    = Newsletter::where($this->data['primary_key'], '=', $id)->first();
            $data['all_users']              = User::select('id', 'first_name', 'last_name')->where('status', '=', 1)->orderBy('first_name', 'ASC')->get();
            $data['jobseeker_users']        = User::select('id', 'first_name', 'last_name')->where('status', '=', 1)->where('role_id', '=', 3)->orderBy('first_name', 'ASC')->get();
            $data['employer_users']         = User::select('id', 'first_name', 'last_name')->where('status', '=', 1)->where('role_id', '=', 2)->orderBy('first_name', 'ASC')->get();

            if($request->isMethod('post')){
                $postData = $request->all();
                $rules = [
                    'title'                 => 'required',
                    'description'           => 'required',
                ];
                if($this->validate($request, $rules)){
                    if($postData['to_users'] == 0){
                        $actual_users = $postData['all_users'];
                    } elseif($postData['to_users'] == 1){
                        $actual_users = $postData['jobseeker_users'];
                    } elseif($postData['to_users'] == 2){
                        $actual_users = $postData['employer_users'];
                    }
                    $fields = [
                        'title'             => strip_tags($postData['title']),
                        'description'       => strip_tags($postData['description']),
                        'to_users'          => strip_tags($postData['to_users']),
                        'users'             => json_encode($actual_users),
                        'status'            => ((array_key_exists("status",$postData))?1:0),
                    ];
                    Newsletter::where($this->data['primary_key'], '=', $id)->update($fields);
                    /* user activity */
                        $activityData = [
                            'user_email'        => session('user_data')['email'],
                            'user_name'         => session('user_data')['name'],
                            'user_type'         => 'ADMIN',
                            'ip_address'        => $request->ip(),
                            'activity_type'     => 3,
                            'activity_details'  => $postData['title'] . ' ' . $this->data['title'] . ' Updated',
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
            $model                          = Newsletter::find($id);
            $fields = [
                'status'             => 3,
                'deleted_at'         => date('Y-m-d H:i:s'),
            ];
            Newsletter::where($this->data['primary_key'], '=', $id)->update($fields);
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
        public function change_status(Request $request, $id){
            $id                             = Helper::decoded($id);
            $model                          = Newsletter::find($id);
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
                        'activity_details'  => $model->name . ' ' . $this->data['title'] . ' Deactivated',
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
                        'activity_details'  => $model->name . ' ' . $this->data['title'] . ' Activated',
                        'platform_type'     => 'WEB',
                    ];
                    UserActivity::insert($activityData);
                /* user activity */
            }            
            $model->save();
            return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' '.$msg.' Successfully !!!');
        }
    /* change status */
    /* send mail */
        public function send(Request $request, $id)
        {
            $id             = Helper::decoded($id);
            $model          = Newsletter::find($id);
            /* mail function */
                $generalSetting             = GeneralSetting::find('1');
                $subject                    = Helper::getSettingValue('site_name').' :: '.$model->title;
                $requestData                = [
                    'title'         => $model->title,
                    'description'   => $model->description
                ];
                // $message                    = view('mails.newsletter',$requestData);
                $message = $model->description;
                $users = json_decode($model->users);
                if(!empty($users)){ for($u=0;$u<count($users);$u++){
                    $extractUser    = $users[$u];
                    $getUserInfo    = User::select('email', 'first_name', 'last_name')->where('id', '=', $extractUser)->first();
                    $to_email       = (($getUserInfo)?$getUserInfo->email:'');
                    $full_name      = (($getUserInfo)?$getUserInfo->first_name . ' ' . $getUserInfo->last_name:'');
                    if($to_email != ''){
                        // $this->sendMail(strtolower($to_email), $subject, $message);
                        Mail::to($to_email)->send(new NewsletterContent($full_name, $message, $model->title));
                    }
                } }
            /* mail function */
            $model->is_send = 1;
            $model->save();
            return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' Send Successfully !!!');
        }
    /* send mail */
}
