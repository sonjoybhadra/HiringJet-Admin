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
use App\Models\User;
use Auth;
use Session;
use Hash;
use DB;

class ReportController extends Controller
{
    protected $siteAuthService;
    public function __construct()
    {
        $this->siteAuthService = new SiteAuthService();
        $this->data = array(
            'title'             => 'Jobs',
            'controller'        => 'ReportController',
            'controller_route'  => 'post-job',
            'primary_key'       => 'id',
            'table_name'        => 'post_jobs',
        );
    }
    /* user wise job list */
        public function registrationCountReport(Request $request){
            $data['module']                 = $this->data;
            $page_name                      = 'reports.registration-count-report';
            $title                          = 'Registration Count Reports';
            $data['employer_count']         = DB::table('users')
                                                ->where('users.status', '=', 1)
                                                ->where('users.role_id', 2)
                                                ->count();
            $data['jobseeker_count']        = DB::table('users')
                                                ->where('users.status', '=', 1)
                                                ->where('users.role_id', 3)
                                                ->count();

            $data['search_day_id']          = 'all';
            $data['is_search']              = 0;
            $data['from_date']              = '';
            $data['to_date']                = '';

            if($request->isMethod('get')){
                if($request->mode == 'search'){
                    $postData = $request->all();
                    // Helper::pr($postData);
                    $search_day_id                  = $request->search_day_id;
                    $data['search_day_id']          = $request->search_day_id;
                    $data['is_search']              = 1;

                    $today          = date('Y-m-d');
                    $yesterday      = date('Y-m-d', strtotime("-1 days"));
                    $lastMonday     = date('Y-m-d', strtotime('last Monday'));
                    $lastWeekmonday = date('Y-m-d', strtotime('monday last week'));
                    $lastWeeksunday = date('Y-m-d', strtotime('sunday last week'));
                    $currentMonthFirstDay = date('Y-m') . "-01";
                    $firstDayLastMonth = date("Y-m-d", mktime(0, 0, 0, date("m") - 1, 1));
                    $lastDayLastMonth = date("Y-m-d", mktime(0, 0, 0, date("m"), 0));
                    $last7Day = date('Y-m-d', strtotime('-7 days'));
                    $last30Day = date('Y-m-d', strtotime('-30 days'));
                    $data['is_date_range']      = 0;
                    if ($search_day_id == 'all') {
                        $from_date  = '';
                        $to_date    = '';
                    } elseif ($search_day_id == 'today') {
                        $from_date  = $today;
                        $to_date    = $today;
                    } elseif ($search_day_id == 'yesterday') {
                        $from_date  = $yesterday;
                        $to_date    = $yesterday;
                    } elseif ($search_day_id == 'this_week') {
                        $from_date  = $lastMonday;
                        $to_date    = $today;
                    } elseif ($search_day_id == 'last_week') {
                        $from_date  = $lastWeekmonday;
                        $to_date    = $lastWeeksunday;
                    } elseif ($search_day_id == 'this_month') {
                        $from_date  = $currentMonthFirstDay;
                        $to_date    = $today;
                    } elseif ($search_day_id == 'last_month') {
                        $from_date  = $firstDayLastMonth;
                        $to_date    = $lastDayLastMonth;
                    } elseif ($search_day_id == 'last_7_days') {
                        $from_date  = $last7Day;
                        $to_date    = $yesterday;
                    } elseif ($search_day_id == 'last_30_days') {
                        $from_date  = $last30Day;
                        $to_date    = $yesterday;
                    } elseif ($search_day_id == 'custom') {
                        $from_date  = $request->search_range_from;
                        $to_date    = $request->search_range_to;
                    }
                    $data['from_date']            = $from_date;
                    $data['to_date']              = $to_date;

                    $data['employer_count']         = DB::table('users')
                                                            ->where('users.status', '=', 1)
                                                            ->where('users.role_id', 2)
                                                            ->whereDate('created_at', '>=', $from_date)
                                                            ->whereDate('created_at', '<=', $to_date)
                                                            ->count();
                    $data['jobseeker_count']        = DB::table('users')
                                                            ->where('users.status', '=', 1)
                                                            ->where('users.role_id', 3)
                                                            ->whereDate('created_at', '>=', $from_date)
                                                            ->whereDate('created_at', '<=', $to_date)
                                                            ->count();
                }
            }
            
            $data                           = $this->siteAuthService ->admin_after_login_layout($title,$page_name,$data);
            return view('maincontents.' . $page_name, $data);
        }
    /* user wise job list */
}
