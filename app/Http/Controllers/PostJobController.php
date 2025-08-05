<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\GeneralSetting;
use App\Models\PostJob;
use App\Models\PostJobUserApplied;
use App\Models\ShortlistedJob;
use App\Models\Employer;
use App\Models\Country;
use App\Models\City;
use App\Models\Nationality;
use App\Models\ContractType;
use App\Models\Keyskill;
use App\Models\CurrentWorkLevel;
use App\Models\Industry;
use App\Models\JobCategory;
use App\Models\Department;
use App\Models\FunctionalArea;
use App\Models\UserActivity;
use App\Services\SiteAuthService;
use App\Helpers\Helper;
use App\Models\Designation;
use Auth;
use Session;
use Hash;
use DB;

class PostJobController extends Controller
{
    protected $siteAuthService;
    public function __construct()
    {
        $this->siteAuthService = new SiteAuthService();
        $this->data = array(
            'title'             => 'Jobs',
            'controller'        => 'PostJobController',
            'controller_route'  => 'post-job',
            'primary_key'       => 'id',
            'table_name'        => 'post_jobs',
        );
    }
    /* list */
        public function list(){
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' List';
            $page_name                      = 'post-job.list';
            $data                           = $this->siteAuthService ->admin_after_login_layout($title,$page_name,$data);
            return view('maincontents.' . $page_name, $data);
        }
        public function pendingList(){
            $data['module']                 = $this->data;
            $title                          = 'Pending From Bulk Uploads List';
            $page_name                      = 'post-job.pending-list';
            // $data['rows']                   = PostJob::where('status', '=', 0)->orderBy('id', 'DESC')->get();
            $data                           = $this->siteAuthService ->admin_after_login_layout($title,$page_name,$data);
            return view('maincontents.' . $page_name, $data);
        }
        public function rejectList(){
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' Reject List';
            $page_name                      = 'post-job.reject-list';
            // $data['rows']                   = PostJob::where('status', '=', 2)->orderBy('id', 'DESC')->get();
            $data                           = $this->siteAuthService ->admin_after_login_layout($title,$page_name,$data);
            return view('maincontents.' . $page_name, $data);
        }
    /* list */
    /* add */
        public function add(Request $request){
            $data['module']           = $this->data;
            if($request->isMethod('post')){
                $postData = $request->all();
                // Helper::pr($postData,0);
                $rules = [
                    // 'position_name'             => 'required',
                    'employer_id'               => 'required',
                    'job_type'                  => 'required',
                    // 'location_countries'        => 'required',
                    // 'location_cities'           => 'required',
                    'industry'                  => 'required',
                    'job_category'              => 'required',
                    'nationality'               => 'required',
                    'gender'                    => 'required',
                    'open_position_number'      => 'required',
                    'contract_type'             => 'required',
                    'min_exp_year'              => 'required',
                ];
                if($this->validate($request, $rules)){
                    $getDesignation = Designation::select('name')->where('id', $postData['designation'])->first();
                    if($postData['designation'] == 7037){
                        $position_name = $postData['position_name'];
                    } else {
                        $position_name = (($getDesignation)?$getDesignation->name:'');
                    }

                    /* user activity */
                        $activityData = [
                            'user_email'        => session('user_data')['email'],
                            'user_name'         => session('user_data')['name'],
                            'user_type'         => 'ADMIN',
                            'ip_address'        => $request->ip(),
                            'activity_type'     => 3,
                            'activity_details'  => $position_name . ' ' . $this->data['title'] . ' Added',
                            'platform_type'     => 'WEB',
                        ];
                        UserActivity::insert($activityData);
                    /* user activity */
                    /* job number generate */
                        $getJob = PostJob::orderBy('id', 'DESC')->first();
                        if(!empty($getJob)){
                            $sl_no              = $getJob->sl_no;
                            $next_sl_no         = $sl_no + 1;
                            $next_sl_no_string  = str_pad($next_sl_no, 7, 0, STR_PAD_LEFT);
                            $job_no             = 'HJ-J-'.$next_sl_no_string;
                        } else {
                            $next_sl_no         = 1;
                            $next_sl_no_string  = str_pad($next_sl_no, 7, 0, STR_PAD_LEFT);
                            $job_no             = 'HJ-J-'.$next_sl_no_string;
                        }
                    /* job number generate */

                    $location_countries         = [];
                    $location_country_names     = [];
                    if(array_key_exists("location_countries",$postData)){
                        $locationCountryIds = $postData['location_countries'];
                        $location_countries = json_encode($locationCountryIds);
                        if(!empty($locationCountryIds)){
                            for($k=0;$k<count($locationCountryIds);$k++){
                                $getCity = Country::select('name')->where('id', $locationCountryIds[$k])->first();
                                $location_country_names[] = (($getCity)?$getCity->name:'');
                            }
                        }
                    }

                    $location_cities    = [];
                    $location_city_names     = [];
                    if(array_key_exists("location_cities",$postData)){
                        $locationIds = $postData['location_cities'];
                        $location_cities = json_encode($locationIds);
                        if(!empty($locationIds)){
                            for($k=0;$k<count($locationIds);$k++){
                                $getCity = City::select('name')->where('id', $locationIds[$k])->first();
                                $location_city_names[] = (($getCity)?$getCity->name:'');
                            }
                        }
                    }

                    $skill_ids = [];
                    $skill_names = [];
                    if(array_key_exists("skill_ids",$postData)){
                        $skillIds = $postData['skill_ids'];
                        $skill_ids = json_encode($skillIds);
                        if(!empty($skillIds)){
                            for($k=0;$k<count($skillIds);$k++){
                                $getSkill = Keyskill::select('name')->where('id', $skillIds[$k])->first();
                                $skill_names[] = (($getSkill)?$getSkill->name:'');
                            }
                        }
                    }                    
                    
                    $fields = [
                        'sl_no'                     => $next_sl_no,
                        'job_no'                    => $job_no,
                        'position_name'             => strip_tags($position_name),
                        'employer_id'               => strip_tags($postData['employer_id']),
                        'job_type'                  => strip_tags($postData['job_type']),
                        'location_countries'        => ((!empty($location_countries))?$location_countries:''),
                        'location_country_names'    => ((!empty($location_country_names))?json_encode($location_country_names):''),
                        'location_cities'           => ((!empty($location_cities))?$location_cities:''),
                        'location_city_names'       => ((!empty($location_city_names))?json_encode($location_city_names):''),
                        'industry'                  => $postData['industry'],
                        'job_category'              => $postData['job_category'],
                        'nationality'               => $postData['nationality'],
                        'gender'                    => $postData['gender'],
                        'open_position_number'      => strip_tags($postData['open_position_number']),
                        'contract_type'             => strip_tags($postData['contract_type']),
                        'designation'               => $postData['designation'],
                        'functional_area'           => $postData['functional_area'],
                        'min_exp_year'              => $postData['min_exp_year'],
                        'max_exp_year'              => $postData['max_exp_year'],
                        'job_description'           => $postData['job_description'],
                        'requirement'               => $postData['requirement'],
                        'skill_ids'                 => ((!empty($skill_ids))?$skill_ids:''),
                        'skill_names'               => ((!empty($skill_names))?json_encode($skill_names):''),
                        // 'experience_level'          => $postData['experience_level'],
                        // 'expected_close_date'       => (($postData['expected_close_date'] != '')?date_format(date_create($postData['expected_close_date']), "Y-m-d"):null),
                        'expected_close_date'       => null,
                        'currency'                  => ((array_key_exists("currency",$postData))?$postData['currency']:''),
                        'min_salary'                => ((array_key_exists("min_salary",$postData))?(($postData['min_salary'] != '')?str_replace(',', '', $postData['min_salary']):0):0),
                        'max_salary'                => ((array_key_exists("max_salary",$postData))?(($postData['max_salary'] != '')?str_replace(',', '', $postData['max_salary']):0):0),
                        'is_salary_negotiable'      => ((array_key_exists("is_salary_negotiable",$postData))?1:0),
                        'posting_open_date'         => (($postData['posting_open_date'] != '')?date_format(date_create($postData['posting_open_date']), "Y-m-d"):null),
                        'posting_close_date'        => (($postData['posting_close_date'] != '')?date_format(date_create($postData['posting_close_date']), "Y-m-d"):null),
                        'application_through'       => strip_tags($postData['application_through']),
                        'apply_on_email'            => strip_tags($postData['apply_on_email']),
                        'apply_on_link'             => strip_tags($postData['apply_on_link']),
                        'walkin_address1'           => strip_tags($postData['walkin_address1']),
                        'walkin_address2'           => strip_tags($postData['walkin_address2']),
                        'walkin_country'            => strip_tags($postData['walkin_country']),
                        'walkin_state'              => strip_tags($postData['walkin_state']),
                        'walkin_city'               => strip_tags($postData['walkin_city']),
                        'walkin_pincode'            => strip_tags($postData['walkin_pincode']),
                        'walkin_latitude'           => strip_tags($postData['walkin_latitude']),
                        'walkin_longitude'          => strip_tags($postData['walkin_longitude']),
                        'walkin_details'            => html_entity_decode($request->walkin_details),
                        'created_by'                => session('user_data')['user_id'],
                        'updated_by'                => session('user_data')['user_id'],
                        'status'                    => 1,
                    ];
                    // Helper::pr($fields);
                    $id = PostJob::insertGetId($fields);
                    // return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' Inserted Successfully !!!');
                    return redirect($this->data['controller_route'] . "/preview/" . Helper::encoded($id))->with('success_message', $this->data['title'].' Preview !!!');
                } else {
                    return redirect()->back()->with('error_message', 'All Fields Required !!!');
                }
            }
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' Add';
            $page_name                      = 'post-job.add-edit';
            $data['row']                    = [];
            $data['employers']              = Employer::select('id', 'name')->where('status', 1)->orderBy('name', 'ASC')->get();
            $data['cities']                 = City::select('id', 'name')->where('status', 1)->orderBy('name', 'ASC')->limit(1000)->get();
            $data['nationalities']          = Nationality::select('id', 'name')->where('status', 1)->orderBy('name', 'ASC')->get();
            $data['contract_types']         = ContractType::select('id', 'name')->where('status', 1)->orderBy('name', 'ASC')->get();
            $data['keyskills']              = Keyskill::select('id', 'name')->where('status', 1)->orderBy('name', 'ASC')->get();
            $data['experiences']            = CurrentWorkLevel::select('id', 'name')->where('status', 1)->orderBy('name', 'ASC')->get();
            $data['currencies']             = Country::select('id', 'name', 'currency_code')->where('status', 1)->where('currency_code', '!=', '')->orderBy('name', 'ASC')->get();
            $data['industries']             = Industry::select('id', 'name')->where('status', 1)->orderBy('name', 'ASC')->get();
            $data['jobcats']                = JobCategory::select('id', 'name')->where('status', 1)->orderBy('name', 'ASC')->get();
            $data['designations']           = Designation::select('id', 'name')->where('status', 1)->orderBy('id', 'ASC')->get();
            $data['functionalareas']        = FunctionalArea::select('id', 'name')->where('status', 1)->orderBy('name', 'ASC')->get();

            $data['location_countries']     = [];
            $data['location_cities']        = [];

            $data                           = $this->siteAuthService ->admin_after_login_layout($title,$page_name,$data);
            return view('maincontents.' . $page_name, $data);
        }
    /* add */
    /* edit */
        public function edit(Request $request, $id){
            $data['module']                 = $this->data;
            $id                             = Helper::decoded($id);
            $title                          = $this->data['title'].' Update';
            $page_name                      = 'post-job.add-edit';
            $data['row']                    = PostJob::where('id', '=', $id)->first();
            $data['employers']              = Employer::select('id', 'name')->where('status', 1)->orderBy('name', 'ASC')->get();
            // $data['cities']                 = City::select('id', 'name')->where('status', 1)->orderBy('name', 'ASC')->get();
            $data['nationalities']          = Nationality::select('id', 'name')->where('status', 1)->orderBy('name', 'ASC')->get();
            $data['contract_types']         = ContractType::select('id', 'name')->where('status', 1)->orderBy('name', 'ASC')->get();
            $data['keyskills']              = Keyskill::select('id', 'name')->where('status', 1)->orderBy('name', 'ASC')->get();
            $data['experiences']            = CurrentWorkLevel::select('id', 'name')->where('status', 1)->orderBy('name', 'ASC')->get();
            $data['currencies']             = Country::select('id', 'name', 'currency_code')->where('status', 1)->where('currency_code', '!=', '')->orderBy('name', 'ASC')->get();
            $data['industries']             = Industry::select('id', 'name')->where('status', 1)->orderBy('name', 'ASC')->get();
            $data['jobcats']                = JobCategory::select('id', 'name')->where('status', 1)->orderBy('name', 'ASC')->get();
            $data['designations']           = Designation::select('id', 'name')->where('status', 1)->orderBy('id', 'ASC')->get();
            $data['functionalareas']        = FunctionalArea::select('id', 'name')->where('status', 1)->orderBy('name', 'ASC')->get();

            $data['location_countries']     = json_decode($data['row']->location_countries ?? '[]', true);
            $country_ids                    = json_decode($data['row']->location_countries ?? '[]', true);
            $data['cities'] = City::select('id', 'name')
                            ->where('status', 1)
                            ->whereIn('country_id', $country_ids)
                            ->orderBy('name', 'ASC')
                            ->get();
            $data['location_cities']        = json_decode($data['row']->location_cities ?? '[]', true);

            if($request->isMethod('post')){
                $postData = $request->all();
                $rules = [
                    // 'position_name'             => 'required',
                    'employer_id'               => 'required',
                    'job_type'                  => 'required',
                    // 'location_countries'        => 'required',
                    // 'location_cities'           => 'required',
                    'industry'                  => 'required',
                    'job_category'              => 'required',
                    'nationality'               => 'required',
                    'gender'                    => 'required',
                    'open_position_number'      => 'required',
                    'contract_type'             => 'required',
                    'min_exp_year'              => 'required',
                ];
                if($this->validate($request, $rules)){
                    $getDesignation = Designation::select('name')->where('id', $postData['designation'])->first();
                    if($postData['designation'] == 7037){
                        $position_name = $postData['position_name'];
                    } else {
                        $position_name = (($getDesignation)?$getDesignation->name:'');
                    }

                    /* user activity */
                        $activityData = [
                            'user_email'        => session('user_data')['email'],
                            'user_name'         => session('user_data')['name'],
                            'user_type'         => 'ADMIN',
                            'ip_address'        => $request->ip(),
                            'activity_type'     => 3,
                            'activity_details'  => $position_name . ' ' . $this->data['title'] . ' Updated',
                            'platform_type'     => 'WEB',
                        ];
                        UserActivity::insert($activityData);
                    /* user activity */
                    
                    $location_countries         = [];
                    $location_country_names     = [];
                    if(array_key_exists("location_countries",$postData)){
                        $locationCountryIds = $postData['location_countries'];
                        $location_countries = json_encode($locationCountryIds);
                        if(!empty($locationCountryIds)){
                            for($k=0;$k<count($locationCountryIds);$k++){
                                $getCity = Country::select('name')->where('id', $locationCountryIds[$k])->first();
                                $location_country_names[] = (($getCity)?$getCity->name:'');
                            }
                        }
                    }

                    $location_cities    = [];
                    $location_city_names     = [];
                    if(array_key_exists("location_cities",$postData)){
                        $locationIds = $postData['location_cities'];
                        $location_cities = json_encode($locationIds);
                        if(!empty($locationIds)){
                            for($k=0;$k<count($locationIds);$k++){
                                $getCity = City::select('name')->where('id', $locationIds[$k])->first();
                                $location_city_names[] = (($getCity)?$getCity->name:'');
                            }
                        }
                    }

                    $skill_ids = [];
                    $skill_names = [];
                    if(array_key_exists("skill_ids",$postData)){
                        $skillIds = $postData['skill_ids'];
                        $skill_ids = json_encode($skillIds);
                        if(!empty($skillIds)){
                            for($k=0;$k<count($skillIds);$k++){
                                $getSkill = Keyskill::select('name')->where('id', $skillIds[$k])->first();
                                $skill_names[] = (($getSkill)?$getSkill->name:'');
                            }
                        }
                    }                    
                    
                    $fields = [
                        'position_name'             => strip_tags($position_name),
                        'employer_id'               => strip_tags($postData['employer_id']),
                        'job_type'                  => strip_tags($postData['job_type']),
                        'location_countries'        => ((!empty($location_countries))?$location_countries:''),
                        'location_country_names'    => ((!empty($location_country_names))?json_encode($location_country_names):''),
                        'location_cities'           => ((!empty($location_cities))?$location_cities:''),
                        'location_city_names'       => ((!empty($location_city_names))?json_encode($location_city_names):''),
                        'industry'                  => $postData['industry'],
                        'job_category'              => $postData['job_category'],
                        'nationality'               => $postData['nationality'],
                        'gender'                    => $postData['gender'],
                        'open_position_number'      => strip_tags($postData['open_position_number']),
                        'contract_type'             => strip_tags($postData['contract_type']),
                        'designation'               => $postData['designation'],
                        'functional_area'           => $postData['functional_area'],
                        'min_exp_year'              => $postData['min_exp_year'],
                        'max_exp_year'              => $postData['max_exp_year'],
                        'job_description'           => $postData['job_description'],
                        'requirement'               => $postData['requirement'],
                        'skill_ids'                 => ((!empty($skill_ids))?$skill_ids:''),
                        'skill_names'               => ((!empty($skill_names))?json_encode($skill_names):''),
                        // 'experience_level'          => $postData['experience_level'],
                        // 'expected_close_date'       => (($postData['expected_close_date'] != '')?date_format(date_create($postData['expected_close_date']), "Y-m-d"):null),
                        'expected_close_date'       => null,
                        'currency'                  => ((array_key_exists("currency",$postData))?$postData['currency']:''),
                        'min_salary'                => ((array_key_exists("min_salary",$postData))?(($postData['min_salary'] != '')?str_replace(',', '', $postData['min_salary']):0):0),
                        'max_salary'                => ((array_key_exists("max_salary",$postData))?(($postData['max_salary'] != '')?str_replace(',', '', $postData['max_salary']):0):0),
                        'is_salary_negotiable'      => ((array_key_exists("is_salary_negotiable",$postData))?1:0),
                        'posting_open_date'         => (($postData['posting_open_date'] != '')?date_format(date_create($postData['posting_open_date']), "Y-m-d"):null),
                        'posting_close_date'        => (($postData['posting_close_date'] != '')?date_format(date_create($postData['posting_close_date']), "Y-m-d"):null),
                        'application_through'       => strip_tags($postData['application_through']),
                        'apply_on_email'            => strip_tags($postData['apply_on_email']),
                        'apply_on_link'             => strip_tags($postData['apply_on_link']),
                        'walkin_address1'           => strip_tags($postData['walkin_address1']),
                        'walkin_address2'           => strip_tags($postData['walkin_address2']),
                        'walkin_country'            => strip_tags($postData['walkin_country']),
                        'walkin_state'              => strip_tags($postData['walkin_state']),
                        'walkin_city'               => strip_tags($postData['walkin_city']),
                        'walkin_pincode'            => strip_tags($postData['walkin_pincode']),
                        'walkin_latitude'           => strip_tags($postData['walkin_latitude']),
                        'walkin_longitude'          => strip_tags($postData['walkin_longitude']),
                        'walkin_details'            => html_entity_decode($request->walkin_details),
                        'created_by'                => session('user_data')['user_id'],
                        'updated_by'                => session('user_data')['user_id'],
                    ];
                    // Helper::pr($fields);
                    PostJob::where('id', '=', $id)->update($fields);
                    // return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' Updated Successfully !!!');
                    return redirect($this->data['controller_route'] . "/preview/" . Helper::encoded($id))->with('success_message', $this->data['title'].' Preview !!!');
                } else {
                    return redirect()->back()->with('error_message', 'All Fields Required !!!');
                }
            }
            $data                           = $this->siteAuthService ->admin_after_login_layout($title,$page_name,$data);
            return view('maincontents.' . $page_name, $data);
        }
    /* edit */
    /* preview */
        public function preview(Request $request, $id){
            $data['module']                 = $this->data;
            $id                             = Helper::decoded($id);
            $page_name                      = 'post-job.preview';
            $data['row']                    = PostJob::where('id', '=', $id)->first();
            $title                          = $this->data['title'].' Preview: ' . (($data['row'])?$data['row']->job_no:'');

            $data                           = $this->siteAuthService ->admin_after_login_layout($title,$page_name,$data);
            return view('maincontents.' . $page_name, $data);
        }
    /* preview */
    /* view details */
        public function viewDetails(Request $request, $id){
            $data['module']                 = $this->data;
            $id                             = Helper::decoded($id);
            $page_name                      = 'post-job.view-details';
            $data['row']                    = PostJob::where('id', '=', $id)->first();
            $title                          = $this->data['title'].' Details: ' . (($data['row'])?$data['row']->job_no:'');

            $data                           = $this->siteAuthService ->admin_after_login_layout($title,$page_name,$data);
            return view('maincontents.' . $page_name, $data);
        }
    /* view details */
    /* delete */
        public function delete(Request $request, $id){
            $id                             = Helper::decoded($id);
            $model                          = PostJob::find($id);
            $fields = [
                'status'             => 3,
                'deleted_at'         => date('Y-m-d H:i:s'),
            ];
            PostJob::where($this->data['primary_key'], '=', $id)->update($fields);
            /* user activity */
                $activityData = [
                    'user_email'        => session('user_data')['email'],
                    'user_name'         => session('user_data')['name'],
                    'user_type'         => 'ADMIN',
                    'ip_address'        => $request->ip(),
                    'activity_type'     => 3,
                    'activity_details'  => $model->position_name . ' ' . $this->data['title'] . ' Deleted',
                    'platform_type'     => 'WEB',
                ];
                UserActivity::insert($activityData);
            /* user activity */
            return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' Deleted Successfully !!!');
        }
    /* delete */
    /* cancel */
        public function cancel(Request $request, $id){
            $id                             = Helper::decoded($id);
            $model                          = PostJob::find($id);
            $fields = [
                'status'             => 3,
                'deleted_at'         => date('Y-m-d H:i:s'),
            ];
            PostJob::where($this->data['primary_key'], '=', $id)->update($fields);
            /* user activity */
                $activityData = [
                    'user_email'        => session('user_data')['email'],
                    'user_name'         => session('user_data')['name'],
                    'user_type'         => 'ADMIN',
                    'ip_address'        => $request->ip(),
                    'activity_type'     => 3,
                    'activity_details'  => $model->position_name . ' ' . $this->data['title'] . ' Cancelled',
                    'platform_type'     => 'WEB',
                ];
                UserActivity::insert($activityData);
            /* user activity */
            return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' Cancelled Successfully !!!');
        }
    /* cancel */
    /* approve */
        public function approve(Request $request, $id){
            $id                             = Helper::decoded($id);
            $model                          = PostJob::find($id);
            $fields = [
                'status'             => 1,
                'deleted_at'         => date('Y-m-d H:i:s'),
            ];
            PostJob::where($this->data['primary_key'], '=', $id)->update($fields);
            /* user activity */
                $activityData = [
                    'user_email'        => session('user_data')['email'],
                    'user_name'         => session('user_data')['name'],
                    'user_type'         => 'ADMIN',
                    'ip_address'        => $request->ip(),
                    'activity_type'     => 3,
                    'activity_details'  => $model->position_name . ' ' . $this->data['title'] . ' Approved',
                    'platform_type'     => 'WEB',
                ];
                UserActivity::insert($activityData);
            /* user activity */
            return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' Approved Successfully !!!');
        }
    /* approve */
    /* reject */
        public function reject(Request $request, $id){
            $id                             = Helper::decoded($id);
            $model                          = PostJob::find($id);
            $fields = [
                'status'             => 2,
                'deleted_at'         => date('Y-m-d H:i:s'),
            ];
            PostJob::where($this->data['primary_key'], '=', $id)->update($fields);
            /* user activity */
                $activityData = [
                    'user_email'        => session('user_data')['email'],
                    'user_name'         => session('user_data')['name'],
                    'user_type'         => 'ADMIN',
                    'ip_address'        => $request->ip(),
                    'activity_type'     => 3,
                    'activity_details'  => $model->position_name . ' ' . $this->data['title'] . ' Rejected',
                    'platform_type'     => 'WEB',
                ];
                UserActivity::insert($activityData);
            /* user activity */
            return redirect("job/reject-list")->with('success_message', $this->data['title'].' Rejected Successfully !!!');
        }
    /* reject */
    /* change status */
        public function change_status(Request $request, $id){
            $id                             = Helper::decoded($id);
            $model                          = PostJob::find($id);
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
                        'activity_details'  => $model->position_name . ' ' . $this->data['title'] . ' Deactivated',
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
                        'activity_details'  => $model->position_name . ' ' . $this->data['title'] . ' Activated',
                        'platform_type'     => 'WEB',
                    ];
                    UserActivity::insert($activityData);
                /* user activity */
            }            
            $model->save();
            return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' '.$msg.' Successfully !!!');
        }
    /* change status */
    /* get country wise city */
        public function getCitiesByCountries(Request $request)
        {
            $countryIds = $request->input('country_ids', []);

            if (!empty($countryIds)) {
                $cities = City::whereIn('country_id', $countryIds)->get(['id', 'name']);
                return response()->json($cities);
            }

            return response()->json([]);
        }
    /* get country wise city */
    /* job applications */
        public function applications(Request $request, $id){
            $data['module']                 = $this->data;
            $id                             = Helper::decoded($id);
            $page_name                      = 'post-job.application-list';
            $data['row']                    = PostJob::where('id', '=', $id)->first();
            $data['job_id']                 = $id;
            $position_name                  = (($data['row'])?$data['row']->position_name:'');
            $job_no                         = (($data['row'])?$data['row']->job_no:'');
            $title                          = $this->data['title'].' Applications : '.$position_name.' ('.$job_no.')';
            $data['jobApplications']        = DB::table('post_job_user_applieds')
                                                ->join('users', 'post_job_user_applieds.user_id', '=', 'users.id')
                                                ->select('post_job_user_applieds.*', 'users.first_name', 'users.last_name', 'users.email', 'users.phone')
                                                ->where('post_job_user_applieds.status', '=', 1)
                                                ->where('post_job_user_applieds.job_id', '=', $id)
                                                ->orderBy('post_job_user_applieds.id', 'DESC')
                                                ->get();
            
            $data                           = $this->siteAuthService ->admin_after_login_layout($title,$page_name,$data);
            return view('maincontents.' . $page_name, $data);
        }
    /* job applications */
    /* user wise job list */
        public function userWiseList(Request $request){
            $data['module']                 = $this->data;
            $page_name                      = 'post-job.user-wise-job-list';
            $title                          = 'User Wise Job Posted';
            $data['subusers']               = DB::table('users')
                                                ->join('roles', 'roles.id', '=', 'users.role_id')
                                                ->select('users.*', 'roles.role_name')
                                                ->where('users.status', '!=', 3)
                                                ->whereIn('users.role_id', [1, 2, 9, 10])
                                                ->orderBy('users.id', 'DESC')
                                                ->get();
            
            $data                           = $this->siteAuthService ->admin_after_login_layout($title,$page_name,$data);
            return view('maincontents.' . $page_name, $data);
        }
    /* user wise job list */
}
