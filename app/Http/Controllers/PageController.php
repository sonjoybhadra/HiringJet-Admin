<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\GeneralSetting;
use App\Models\Page;
use App\Models\UserActivity;
use App\Services\SiteAuthService;
use App\Helpers\Helper;
use Auth;
use Session;
use Hash;
use DB;

class PageController extends Controller
{
    protected $siteAuthService;
    public function __construct()
    {
        $this->siteAuthService = new SiteAuthService();
        $this->data = array(
            'title'             => 'CMS Page',
            'controller'        => 'PageController',
            'controller_route'  => 'page',
            'primary_key'       => 'id',
            'table_name'        => 'pages',
        );
    }
    /* list */
        public function list(){
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' List';
            $page_name                      = 'page.list';
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
                    'page_name'                 => 'required',
                    'page_banner_image'         => 'required',
                    'page_image'                => 'required',
                ];
                if($this->validate($request, $rules)){
                    /* user activity */
                        $activityData = [
                            'user_email'        => session('user_data')['email'],
                            'user_name'         => session('user_data')['name'],
                            'user_type'         => 'ADMIN',
                            'ip_address'        => $request->ip(),
                            'activity_type'     => 3,
                            'activity_details'  => $postData['page_name'] . ' ' . $this->data['title'] . ' Added',
                            'platform_type'     => 'WEB',
                        ];
                        UserActivity::insert($activityData);
                    /* user activity */
                    /* page banner image */
                        $upload_folder = 'page';
                        $imageFile      = $request->file('page_banner_image');
                        if($imageFile != ''){
                            $imageName      = $imageFile->getClientOriginalName();
                            $uploadedFile   = $this->upload_single_file('page_banner_image', $imageName, $upload_folder, 'image');
                            if($uploadedFile['status']){
                                $page_banner_image = $uploadedFile['newFilename'];
                            } else {
                                return redirect()->back()->with(['error_message' => $uploadedFile['message']]);
                            }
                        } else {
                            return redirect()->back()->with(['error_message' => 'Please Upload ' . $this->data['title'] . ' Banner Image']);
                        }
                    /* page banner image */
                    /* page image */
                        $upload_folder = 'page';
                        $imageFile      = $request->file('page_image');
                        if($imageFile != ''){
                            $imageName      = $imageFile->getClientOriginalName();
                            $uploadedFile   = $this->upload_single_file('page_image', $imageName, $upload_folder, 'image');
                            if($uploadedFile['status']){
                                $page_image = $uploadedFile['newFilename'];
                            } else {
                                return redirect()->back()->with(['error_message' => $uploadedFile['message']]);
                            }
                        } else {
                            return redirect()->back()->with(['error_message' => 'Please Upload ' . $this->data['title'] . ' Banner Image']);
                        }
                    /* page image */
                    $fields = [
                        'page_name'                 => strip_tags($postData['page_name']),
                        'page_slug'                 => Helper::clean(strip_tags($postData['page_name'])),
                        'page_content'              => $postData['page_content'],
                        'page_banner_image'         => 'uploads/' . $upload_folder . '/' . $page_banner_image,
                        'page_image'                => 'uploads/' . $upload_folder . '/' . $page_image,
                        'meta_title'                => strip_tags($postData['meta_title']),
                        'meta_keywords'             => strip_tags($postData['meta_keywords']),
                        'meta_description'          => strip_tags($postData['meta_description']),
                        'status'                    => ((array_key_exists("status",$postData))?1:0),
                    ];
                    Page::insert($fields);
                    return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' Inserted Successfully !!!');
                } else {
                    return redirect()->back()->with('error_message', 'All Fields Required !!!');
                }
            }
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' Add';
            $page_name                      = 'page.add-edit';
            $data['row']                    = [];
            $data                           = $this->siteAuthService ->admin_after_login_layout($title,$page_name,$data);
            return view('maincontents.' . $page_name, $data);
        }
    /* add */
    /* edit */
        public function edit(Request $request, $id){
            $data['module']                 = $this->data;
            $id                             = Helper::decoded($id);
            $title                          = $this->data['title'].' Update';
            $page_name                      = 'page.add-edit';
            $data['row']                    = Page::where('id', '=', $id)->first();
            if($request->isMethod('post')){
                $postData = $request->all();
                $rules = [
                    'page_name'                 => 'required',
                ];
                if($this->validate($request, $rules)){
                    /* page banner image */
                        $upload_folder = 'page';
                        $imageFile      = $request->file('page_banner_image');
                        if($imageFile != ''){
                            $imageName      = $imageFile->getClientOriginalName();
                            $uploadedFile   = $this->upload_single_file('page_banner_image', $imageName, $upload_folder, 'image');
                            if($uploadedFile['status']){
                                $page_banner_image = $uploadedFile['newFilename'];
                                $pageBannerImageLink = 'uploads/' . $upload_folder . '/' . $page_banner_image;
                            } else {
                                return redirect()->back()->with(['error_message' => $uploadedFile['message']]);
                            }
                        } else {
                            $page_banner_image = $data['row']->page_banner_image;
                            $pageBannerImageLink = $page_banner_image;
                        }
                    /* page banner image */
                    /* page image */
                        $upload_folder = 'page';
                        $imageFile      = $request->file('page_image');
                        if($imageFile != ''){
                            $imageName      = $imageFile->getClientOriginalName();
                            $uploadedFile   = $this->upload_single_file('page_image', $imageName, $upload_folder, 'image');
                            if($uploadedFile['status']){
                                $page_image = $uploadedFile['newFilename'];
                                $pageImageLink = 'uploads/' . $upload_folder . '/' . $page_image;
                            } else {
                                return redirect()->back()->with(['error_message' => $uploadedFile['message']]);
                            }
                        } else {
                            $page_image = $data['row']->page_image;
                            $pageImageLink = $page_image;
                        }
                    /* page image */
                    $fields = [
                        'page_name'                 => strip_tags($postData['page_name']),
                        'page_slug'                 => Helper::clean(strip_tags($postData['page_name'])),
                        'page_content'              => $postData['page_content'],
                        'page_banner_image'         => $pageBannerImageLink,
                        'page_image'                => $pageImageLink,
                        'status'                    => ((array_key_exists("status",$postData))?1:0),
                        'meta_title'                => strip_tags($postData['meta_title']),
                        'meta_keywords'             => strip_tags($postData['meta_keywords']),
                        'meta_description'          => strip_tags($postData['meta_description']),
                    ];
                    Page::where($this->data['primary_key'], '=', $id)->update($fields);
                    /* user activity */
                        $activityData = [
                            'user_email'        => session('user_data')['email'],
                            'user_name'         => session('user_data')['name'],
                            'user_type'         => 'ADMIN',
                            'ip_address'        => $request->ip(),
                            'activity_type'     => 3,
                            'activity_details'  => $postData['page_name'] . ' ' . $this->data['title'] . ' Updated',
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
            $model                          = Page::find($id);
            $fields = [
                'status'             => 3,
                'deleted_at'         => date('Y-m-d H:i:s'),
            ];
            Page::where($this->data['primary_key'], '=', $id)->update($fields);
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
            $model                          = Page::find($id);
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
}
