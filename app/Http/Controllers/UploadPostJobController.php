<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\GeneralSetting;
use App\Models\UploadJob;
use App\Models\PostJob;
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
use App\Models\Designation;
use App\Models\UserActivity;
use App\Services\SiteAuthService;
use App\Helpers\Helper;
use Auth;
use Session;
use Hash;
use DB;

class UploadPostJobController extends Controller
{
    protected $siteAuthService;
    public function __construct()
    {
        $this->siteAuthService = new SiteAuthService();
        $this->data = array(
            'title'             => 'Upload Job',
            'controller'        => 'UploadPostJobController',
            'controller_route'  => 'upload-post-job',
            'primary_key'       => 'id',
        );
    }
    /* list */
        public function list(Request $request){
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' List';
            $page_name                      = 'upload-post-job.list';
            $data['rows']                   = UploadJob::where('status', '!=', 3)->orderBy('id', 'DESC')->get();
            $data                           = $this->siteAuthService ->admin_after_login_layout($title,$page_name,$data);

            if($request->isMethod('post')){
                $postData = $request->all();
                $rules = [
                    'name'                  => 'required',
                    'upload_file'           => 'required',
                ];
                if($this->validate($request, $rules)){
                    /* user activity */
                        $activityData = [
                            'user_email'        => session('user_data')['email'],
                            'user_name'         => session('user_data')['name'],
                            'user_type'         => 'ADMIN',
                            'ip_address'        => $request->ip(),
                            'activity_type'     => 3,
                            'activity_details'  => $postData['name'] . ' ' . $this->data['title'] . ' Added',
                            'platform_type'     => 'WEB',
                        ];
                        UserActivity::insert($activityData);
                    /* user activity */
                    /* upload_file */
                        $upload_folder = 'post-job';
                        $imageFile      = $request->file('upload_file');
                        if($imageFile != ''){
                            $imageName      = $imageFile->getClientOriginalName();
                            $uploadedFile   = $this->siteAuthService->upload_single_file('upload_file', $imageName, $upload_folder, 'csv');
                            if($uploadedFile['status']){
                                $upload_file = $uploadedFile['newFilename'];
                            } else {
                                return redirect()->back()->with(['error_message' => $uploadedFile['message']]);
                            }
                        } else {
                            return redirect()->back()->with(['error_message' => 'Please upload job file']);
                        }
                    /* upload_file */
                    $fields = [
                        'name'                  => strip_tags($postData['name']),
                        'upload_file'           => $upload_file,
                    ];
                    $upload_id = UploadJob::insertGetId($fields);
                    
                    /* extract csv file */
                        // Full path to your CSV in public/uploads/post-job
                        $path = public_path('uploads/post-job/' . $upload_file);

                        if (!file_exists($path)) {
                            return 'CSV file does not exist at: ' . $path;
                        }

                        $rows = [];

                        if (($handle = fopen($path, 'r')) !== false) {
                            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                                $rows[] = $data;
                            }
                            fclose($handle);
                        }
                    /* extract csv file */
                    /* insert data into post_jobs table */
                        if($rows){
                            for($k=0;$k<count($rows);$k++){
                                // skip first row for header
                                if($k > 0){
                                    $Sl_No = $rows[$k][0];
                                    $Designation = $rows[$k][1];
                                    $Employer = $rows[$k][2];
                                    $Job_Type = $rows[$k][3];
                                    $Location_Country = $rows[$k][4];
                                    $Loccation_City = $rows[$k][5];
                                    $Industry = $rows[$k][6];
                                    $Job_Category = $rows[$k][7];
                                    $Nationality = $rows[$k][8];
                                    $Gender = $rows[$k][9];
                                    $Open_Position_Number = $rows[$k][10];
                                    $Contract_Type = $rows[$k][11];
                                    $Functional_Area = $rows[$k][12];
                                    $Min_Experience = $rows[$k][13];
                                    $Max_Experience = $rows[$k][14];
                                    $Job_Description = $rows[$k][15];
                                    $Requirement = $rows[$k][16];
                                    $Skills = $rows[$k][17];
                                    $Is_Salary_Negotiable = $rows[$k][18];
                                    $Currency = $rows[$k][19];
                                    $Min_Salary = $rows[$k][20];
                                    $Max_Salary = $rows[$k][21];
                                    $Posting_Open_Date = $rows[$k][22];
                                    $Posting_Close_Date = $rows[$k][23];
                                    $Application_Through = $rows[$k][24];
                                    $Apply_on_email = $rows[$k][25];
                                    $Apply_on_link = $rows[$k][26];
                                    $Walkin_Details = $rows[$k][27];
                                    $Walkin_address_1 = $rows[$k][28];
                                    $Walkin_address_2 = $rows[$k][29];
                                    $Walkin_Country = $rows[$k][30];
                                    $Walkin_State = $rows[$k][31];
                                    $Walkin_City = $rows[$k][32];
                                    $Walkin_Pincode = $rows[$k][33];
                                    $Walkin_Latitude = $rows[$k][34];
                                    $Walkin_Longitude = $rows[$k][35];

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

                                    // employer
                                    $getEmployerID = Employer::select('id')->where('name', '=', $Employer)->first();
                                    if($getEmployerID){
                                        $employer_id = $getEmployerID->id;
                                    } else {
                                        $fields1 = [
                                            'name' => $Employer,
                                            'logo' => '',
                                            'description' => '',
                                            'industry_id' => 0,
                                            'no_of_employee' => 0,
                                            'status' => 1,
                                        ];
                                        $employer_id = Employer::insertGetId($fields1);
                                    }

                                    // position or designation
                                    $getDesignationID = Designation::select('id')->where('name', '=', $Designation)->first();
                                    if($getDesignationID){
                                        $designation = $getDesignationID->id;
                                    } else {
                                        $fields1 = [
                                            'name' => $Designation,
                                            'status' => 1,
                                        ];
                                        $designation = Designation::insertGetId($fields1);
                                    }

                                    // location country
                                    $location_countries = [];
                                    $country_datas = explode(',', $Location_Country);
                                    if(!empty($country_datas)){
                                        for($s=0;$s<count($country_datas);$s++){
                                            $country_name = $country_datas[$s];
                                            $getCountryID = Country::select('id')->where('name', '=', $country_name)->first();
                                            if($getCountryID){
                                                $country_id = $getCountryID->id;
                                            } else {
                                                $fields1 = [
                                                    'name'                  => $country_name,
                                                    'country_code'          => '',
                                                    'country_flag'          => '',
                                                    'country_short_code'    => '',
                                                    'currency_code'         => '',
                                                    'status'                => 1,
                                                ];
                                                $country_id = Country::insertGetId($fields1);
                                            }
                                            $location_countries[] = (string)$country_id;
                                        }
                                    }

                                    // location city
                                    $location_cities = [];
                                    $city_datas = explode(',', $Loccation_City);
                                    if(!empty($city_datas)){
                                        for($s=0;$s<count($city_datas);$s++){
                                            $city_name = $city_datas[$s];
                                            $getCityID = City::select('id')->where('name', '=', $city_name)->first();
                                            if($getCityID){
                                                $city_id = $getCityID->id;
                                            } else {
                                                $fields1 = [
                                                    'name'                  => $city_name,
                                                    'country_id'            => 0,
                                                    'status'                => 1,
                                                ];
                                                $city_id = City::insertGetId($fields1);
                                            }
                                            $location_cities[] = (string)$city_id;
                                        }
                                    }

                                    // industry
                                    $getIndustryID = Industry::select('id')->where('name', '=', $Industry)->first();
                                    if($getIndustryID){
                                        $industry = $getIndustryID->id;
                                    } else {
                                        $fields1 = [
                                            'name' => $Industry,
                                            'status' => 1,
                                        ];
                                        $industry = Industry::insertGetId($fields1);
                                    }

                                    // job category
                                    $getJobCategoryID = JobCategory::select('id')->where('name', '=', $Job_Category)->first();
                                    if($getJobCategoryID){
                                        $job_category = $getJobCategoryID->id;
                                    } else {
                                        $fields1 = [
                                            'name' => $Job_Category,
                                            'status' => 1,
                                        ];
                                        $job_category = JobCategory::insertGetId($fields1);
                                    }

                                    // nationality
                                    $getNationalityID = Nationality::select('id')->where('name', '=', $Nationality)->first();
                                    if($getNationalityID){
                                        $nationality = $getNationalityID->id;
                                    } else {
                                        // $fields1 = [
                                        //     'name' => $Nationality,
                                        //     'status' => 1,
                                        // ];
                                        // $nationality = Nationality::insertGetId($fields1);

                                        DB::table('nationalities')->updateOrInsert(
                                            ['name' => $Nationality],     // Match condition
                                            ['status' => 1]               // Will update if exists
                                        );

                                        $nationality = Nationality::where('name', $Nationality)->first();
                                        $nationality = $nationality->id;
                                    }

                                    // contract type
                                    $getContractTypeID = ContractType::select('id')->where('name', '=', $Contract_Type)->first();
                                    if($getContractTypeID){
                                        $contract_type = $getContractTypeID->id;
                                    } else {
                                        $fields1 = [
                                            'name' => $Contract_Type,
                                            'status' => 1,
                                        ];
                                        $contract_type = ContractType::insertGetId($fields1);
                                    }

                                    // functional area
                                    $getFunctionalAreaID = FunctionalArea::select('id')->where('name', '=', $Functional_Area)->first();
                                    if($getFunctionalAreaID){
                                        $functional_area = $getFunctionalAreaID->id;
                                    } else {
                                        // $fields1 = [
                                        //     'name' => $Functional_Area,
                                        //     'status' => 1,
                                        // ];
                                        // $functional_area = FunctionalArea::insertGetId($fields1);

                                        $maxId = DB::table('functional_areas')->max('id');
                                        DB::statement("ALTER SEQUENCE functional_areas_id_seq RESTART WITH " . ($maxId + 1));

                                        DB::table('functional_areas')->updateOrInsert(
                                            ['name' => $Functional_Area],     // Match condition
                                            ['status' => 1]               // Will update if exists
                                        );

                                        $functional_area = FunctionalArea::where('name', $Functional_Area)->first();
                                        $functional_area = $functional_area->id;
                                    }

                                    // skill
                                    $skill_ids = [];
                                    $skill_datas = explode(',', $Skills);
                                    if(!empty($skill_datas)){
                                        for($s=0;$s<count($skill_datas);$s++){
                                            $skill_name = $skill_datas[$s];
                                            $getSkillID = Keyskill::select('id')->where('name', '=', $skill_name)->first();
                                            if($getSkillID){
                                                $skill_id = $getSkillID->id;
                                            } else {
                                                $fields1 = [
                                                    'name' => $skill_name,
                                                    'status' => 1,
                                                ];
                                                $skill_id = Keyskill::insertGetId($fields1);
                                            }
                                            $skill_ids[] = (string)$skill_id;
                                        }
                                    }

                                    

                                    $fields = [
                                        'sl_no'                     => $next_sl_no,
                                        'job_no'                    => $job_no,
                                        'position_name'             => strip_tags($Designation),
                                        'employer_id'               => $employer_id,
                                        'job_type'                  => strip_tags($Job_Type),
                                        'location_countries'        => ((!empty($location_countries))?json_encode($location_countries):''),
                                        'location_country_names'    => ((!empty($Location_Country))?json_encode(explode(',', $Location_Country)):''),
                                        'location_cities'           => ((!empty($location_cities))?json_encode($location_cities):''),
                                        'location_city_names'       => ((!empty($Loccation_City))?json_encode(explode(',', $Loccation_City)):''),
                                        'industry'                  => $industry,
                                        'job_category'              => $job_category,
                                        'nationality'               => $nationality,
                                        'gender'                    => strip_tags($Gender),
                                        'open_position_number'      => strip_tags($Open_Position_Number),
                                        'contract_type'             => $contract_type,
                                        'designation'               => $designation,
                                        'functional_area'           => $functional_area,
                                        'min_exp_year'              => $Min_Experience,
                                        'max_exp_year'              => $Max_Experience,
                                        'job_description'           => $Job_Description,
                                        'requirement'               => $Requirement,
                                        'skill_ids'                 => ((!empty($skill_ids))?json_encode($skill_ids):''),
                                        'skill_names'               => ((!empty($Skills))?json_encode(explode(',', $Skills)):''),
                                        'expected_close_date'       => null,
                                        'currency'                  => strip_tags($Currency),
                                        'min_salary'                => $Min_Salary,
                                        'max_salary'                => $Max_Salary,
                                        'is_salary_negotiable'      => (($Is_Salary_Negotiable == 'YES')?1:0),
                                        'posting_open_date'         => (($Posting_Open_Date != '')?date_format(date_create($Posting_Open_Date), "Y-m-d"):null),
                                        'posting_close_date'        => (($Posting_Close_Date != '')?date_format(date_create($Posting_Close_Date), "Y-m-d"):null),
                                        // 'posting_open_date'         => $Posting_Open_Date,
                                        // 'posting_close_date'        => $Posting_Close_Date,
                                        'application_through'       => strip_tags($Application_Through),
                                        'apply_on_email'            => strip_tags($Apply_on_email),
                                        'apply_on_link'             => strip_tags($Apply_on_link),
                                        'walkin_address1'           => strip_tags($Walkin_address_1),
                                        'walkin_address2'           => strip_tags($Walkin_address_2),
                                        'walkin_country'            => strip_tags($Walkin_Country),
                                        'walkin_state'              => strip_tags($Walkin_State),
                                        'walkin_city'               => strip_tags($Walkin_City),
                                        'walkin_pincode'            => strip_tags($Walkin_Pincode),
                                        'walkin_latitude'           => strip_tags($Walkin_Latitude),
                                        'walkin_longitude'          => strip_tags($Walkin_Longitude),
                                        'walkin_details'            => html_entity_decode($Walkin_Details),
                                        'created_by'                => session('user_data')['user_id'],
                                        'updated_by'                => session('user_data')['user_id'],
                                        'status'                    => 1,
                                        'upload_id'                 => $upload_id,
                                    ];
                                    Helper::pr($fields);

                                    $maxId = DB::table('post_jobs')->max('id');
                                    DB::statement("ALTER SEQUENCE post_jobs_id_seq RESTART WITH " . ($maxId + 1));

                                    PostJob::insert($fields);
                                }
                            }
                        }
                    /* insert data into post_jobs table */
                    // Helper::pr($rows);
                    // die;
                    return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' Inserted Successfully !!!');
                } else {
                    return redirect()->back()->with('error_message', 'All Fields Required !!!');
                }
            }

            return view('maincontents.' . $page_name, $data);
        }
    /* list */
    /* delete */
        public function delete(Request $request, $id){
            $id                             = Helper::decoded($id);
            $model                          = UploadJob::find($id);
            $fields = [
                'status'             => 3,
                'deleted_at'         => date('Y-m-d H:i:s'),
            ];
            UploadJob::where($this->data['primary_key'], '=', $id)->update($fields);

            $fields = [
                'status'             => 3,
                'deleted_at'         => date('Y-m-d H:i:s'),
            ];
            PostJob::where('upload_id', '=', $id)->update($fields);

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
            $model                          = UploadJob::find($id);
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
