<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\JobCategory;
use App\Models\Industry;
use App\Models\Designation;
use App\Models\Employer;
use App\Models\Country;
use App\Models\City;
use App\Models\Currency;
use App\Models\Keyskill;
use App\Models\PerkBenefit;
use App\Models\Availability;
use App\Models\CurrentWorkLevel;
use App\Models\FunctionalArea;
use App\Models\OnlineProfile;
use App\Models\Qualification;
use App\Models\Course;
use App\Models\Specialization;
use App\Models\University;
use App\Models\MostCommonEmail;
use App\Models\Language;
use App\Models\Religion;
use App\Models\MaritalStatus;
use App\Models\ItSkill;
use App\Models\Nationality;

use App\Models\HomePage;
use App\Models\GeneralSetting;
use App\Models\Testimonial;
use App\Models\PostJob;
use App\Models\User;

class CommonController extends BaseApiController
{
    //
    public function get_masters_by_params(Request $request){
        $result_array = [];
        $common_resource_array = [
            'jobcategory'=> $this->get_jobcategory(1),
            'industry'=> $this->get_industry(1),
            'designation'=> $this->get_designation(1),
            'employer'=> $this->get_employer(1),
            'country'=> $this->get_country(1),
            'country_code'=> $this->get_country_code(1),
            'currency'=> $this->get_currency(1),
            'keyskill'=> $this->get_keyskill(1),
            'perkbenefit'=> $this->get_perkbenefit(1),
            'availability'=> $this->get_availability(1),
            'currentworklevel'=> $this->get_currentworklevel(1),
            'functionalarea'=> $this->get_functionalarea(1),
            'onlineprofile'=> $this->get_onlineprofile(1),
            'qualification'=> $this->get_qualification(1),
            'course'=> $this->get_course('', 1),
            // 'specialization'=> $this->get_specialization(1),
            'nationality'=> $this->get_nationality(1),
            'religion'=> $this->get_religion(1),
            'university'=> $this->get_university(1),
            'mostcommonemail'=> $this->get_mostcommonemail(1),
            'language'=> $this->get_language(1),
            'maritalstatus'=> $this->get_maritalstatus(1),
            'proficiency_level'=> $this->get_proficiency_level(1),
            'cast_category'=> $this->get_cast_category(1),
            'diverse_background'=> $this->get_diverse_background(1),
            'employment_type'=> $this->get_employment_type(1),
            'course_type'=> $this->get_course_type(1),
            'report_bug_category'=> $this->get_report_bug_category(1),
            'interestedIn'=> $this->get_interestedIn(1),
            'itSkill'=> $this->get_itSkill(1),
        ];
        if(!empty($request->params )){
            $params = explode(',', $request->params);
            foreach($params as $param){
                if(!isset($common_resource_array[$param])){
                    continue;
                }
                $result_array[$param] = $common_resource_array[$param];
            }
        }
        return $this->sendResponse(
            $result_array,
            'Data List'
        );
    }

    public function get_jobcategory($res = '')
    {
        $list = JobCategory::select('id', 'name')->where('status', 1)->get();
        if($res != ''){
            return $list;
        }else{
            return $this->sendResponse(
                    $list,
                    'Data list.'
                );
        }
    }

    public function get_industry($res = '')
    {
        $list = Industry::select('id', 'name')->where('status', 1)->get();
        if($res != ''){
            return $list;
        }else{
            return $this->sendResponse(
                    $list,
                    'Data list.'
                );
        }
    }

    public function get_designation($res = '')
    {
        $list = Designation::select('id', 'name')->where('status', 1)->get();
        if($res != ''){
            return $list;
        }else{
            return $this->sendResponse(
                    $list,
                    'Data list.'
                );
        }
    }

    public function get_employer($res = '')
    {
        $list = Employer::select('id', 'name', 'logo', 'description', 'no_of_employee')
                        ->where('status', 1)
                        ->with('industry')
                        ->get();
        if($res != ''){
            return $list;
        }else{
            return $this->sendResponse(
                    $list,
                    'Data list.'
                );
        }
    }

    public function get_country($res = '')
    {
        $list = Country::select('id', 'name', 'country_code', 'country_flag', 'country_short_code')->where('status', 1)->get();
        if($res != ''){
            return $list;
        }else{
            return $this->sendResponse(
                    $list,
                    'Data list.'
                );
        }
    }

    public function get_city($country_id = '')
    {
        $sql = City::select('id', 'name')->where('status', 1);
        if($country_id != ''){
            $sql->where('country_id', $country_id);
        }
        return $this->sendResponse(
            $sql->get(),
            'List'
        );
    }

    public function get_country_code($res = '')
    {
        $list = Country::select('country_code', 'country_flag', 'country_short_code')->where('status', 1)->distinct('country_code')->get();
        if($res != ''){
            return $list;
        }else{
            return $this->sendResponse(
                    $list,
                    'Data list.'
                );
        }
    }

    public function get_currency($res = '')
    {
        $list = Country::select('id', 'currency_code as name', 'country_short_code')
                        ->where('status', 1)
                        ->where('currency_code','<>','')
                        ->get();
        if($res != ''){
            return $list;
        }else{
            return $this->sendResponse(
                    $list,
                    'Data list.'
                );
        }
    }

    public function get_keyskill($res = '')
    {
        $list = Keyskill::select('id', 'name')->where('status', 1)->get();
        if($res != ''){
            return $list;
        }else{
            return $this->sendResponse(
                    $list,
                    'Data list.'
                );
        }
    }

    public function get_perkbenefit($res = '')
    {
        $list = PerkBenefit::select('id', 'name')->where('status', 1)->get();
        if($res != ''){
            return $list;
        }else{
            return $this->sendResponse(
                    $list,
                    'Data list.'
                );
        }
    }

    public function get_availability($res = '')
    {
        $list = Availability::select('id', 'name')->where('status', 1)->get();
        if($res != ''){
            return $list;
        }else{
            return $this->sendResponse(
                    $list,
                    'Data list.'
                );
        }
    }

    public function get_currentworklevel($res = '')
    {
        $list = CurrentWorkLevel::select('id', 'name')->where('status', 1)->get();
        if($res != ''){
            return $list;
        }else{
            return $this->sendResponse(
                    $list,
                    'Data list.'
                );
        }
    }

    public function get_functionalarea($res = '')
    {
        $list = FunctionalArea::select('id', 'name')->where('status', 1)->get();
        if($res != ''){
            return $list;
        }else{
            return $this->sendResponse(
                    $list,
                    'Data list.'
                );
        }
    }

    public function get_onlineprofile($res = '')
    {
        $list = OnlineProfile::select('id', 'name', 'logo')->where('status', 1)->get();
        if($res != ''){
            return $list;
        }else{
            return $this->sendResponse(
                    $list,
                    'Data list.'
                );
        }
    }

    public function get_qualification($res = '')
    {
        $list = Qualification::select('id', 'name')->where('status', 1)->get();
        if($res != ''){
            return $list;
        }else{
            return $this->sendResponse(
                    $list,
                    'Data list.'
                );
        }
    }

    public function get_course($qualification_id = '', $res = '')
    {
        $sql = Course::select('id', 'name')->where('status', 1)
                    ->with('qualification');
        if(!empty($qualification_id)){
            $sql->where('qualification_id', $qualification_id);
        }
        $list = $sql->get();
        if($res != ''){
            return $list;
        }else{
            return $this->sendResponse(
                    $list,
                    'Data list.'
                );
        }
    }

    public function get_specialization(Request $request)
    {
        $sql = Specialization::select('id', 'name')->where('status', 1)
                            ->with('qualification')
                            ->with('course');
        if(!empty($request->qualification_id)){
            $sql->where('qualification_id', $request->qualification_id);
        }
        if(!empty($request->course_id)){
            $sql->where('course_id', $request->course_id);
        }
        $list = $sql->get();

        return $this->sendResponse(
                    $list,
                    'Data list.'
                );
    }

    public function get_nationality($res = '')
    {
        $list = Nationality::select('id', 'name')->where('status', 1)->get();
        if($res != ''){
            return $list;
        }else{
            return $this->sendResponse(
                    $list,
                    'Data list.'
                );
        }
    }

    public function get_religion($res = '')
    {
        $list = Religion::select('id', 'name')->where('status', 1)->get();
        if($res != ''){
            return $list;
        }else{
            return $this->sendResponse(
                    $list,
                    'Data list.'
                );
        }
    }

    public function get_university($res = '')
    {
        $list = University::select('id', 'name')->where('status', 1)->get();
        if($res != ''){
            return $list;
        }else{
            return $this->sendResponse(
                    $list,
                    'Data list.'
                );
        }
    }

    public function get_mostcommonemail($res = '')
    {
        $list = MostCommonEmail::select('id', 'emailid_domain')->where('status', 1)->get();
        if($res != ''){
            return $list;
        }else{
            return $this->sendResponse(
                    $list,
                    'Data list.'
                );
        }
    }

    public function get_language($res = '')
    {
        $list = Language::select('id', 'name')->where('status', 1)->get();
        if($res != ''){
            return $list;
        }else{
            return $this->sendResponse(
                    $list,
                    'Data list.'
                );
        }
    }

    public function get_maritalstatus($res = '')
    {
        $list = MaritalStatus::select('id', 'name')->where('status', 1)->get();
        if($res != ''){
            return $list;
        }else{
            return $this->sendResponse(
                    $list,
                    'Data list.'
                );
        }
    }

    public function get_proficiency_level($res = '')
    {
        $list = ['Beginner', 'Intermediate', 'Advanced', 'Expert'];
        if($res != ''){
            return $list;
        }else{
            return $this->sendResponse(
                    $list,
                    'Data list.'
                );
        }
    }

    public function get_cast_category($res = '')
    {
        $list = ['General/UR', 'SC', 'ST', 'OBC', 'OBC - Non Creamy', 'Others'];
        if($res != ''){
            return $list;
        }else{
            return $this->sendResponse(
                    $list,
                    'Data list.'
                );
        }
    }

    public function get_diverse_background($res = '')
    {
        $list = ['Single Parent', 'Working Mother', 'Retired', 'LGBTQ'];
        if($res != ''){
            return $list;
        }else{
            return $this->sendResponse(
                    $list,
                    'Data list.'
                );
        }
    }

    public function get_employment_type($res = '')
    {
        $list = ['Full Time', 'Internship'];
        if($res != ''){
            return $list;
        }else{
            return $this->sendResponse(
                    $list,
                    'Data list.'
                );
        }
    }

    public function get_course_type($res = '')
    {
        $list = ['Full Time', 'Part Time', 'Correspenence/ Distance learning'];
        if($res != ''){
            return $list;
        }else{
            return $this->sendResponse(
                    $list,
                    'Data list.'
                );
        }
    }

    public function get_report_bug_category($res = '')
    {
        $list = ['Suggest Improvements', 'Feedback on Paid Services', 'Report Bug', 'Any Complaints / Report Abuse', ' Appreciation'];
        if($res != ''){
            return $list;
        }else{
            return $this->sendResponse(
                    $list,
                    'Data list.'
                );
        }
    }

    public function get_interestedIn($res = '')
    {
        $list = ['CV Search', 'Job Posting', 'Employer Branding', 'Salary Tool', 'Power Your Career Site', 'Not Sure'];
        if($res != ''){
            return $list;
        }else{
            return $this->sendResponse(
                    $list,
                    'Data list.'
                );
        }
    }

    public function get_itSkill($res = '')
    {
        $list = ItSkill::select('id', 'name')->where('status', 1)->get();
        if($res != ''){
            return $list;
        }else{
            return $this->sendResponse(
                    $list,
                    'Data list.'
                );
        }
    }

    public function get_city_by_param(Request $request)
    {
        $sql = City::select('id', 'name')->where('status', 1)->with('country');
        if(!empty($request->country_id)){
            $country_id_array = explode(',', $request->country_id);
            $sql->whereIn('country_id', $country_id_array);
        }
        if(!empty($request->key)){
            $sql->where('name', 'ILIKE',  '%'.$request->key.'%');
        }
        return $this->sendResponse(
            $sql->get(),
            'List'
        );
    }

    public function get_homepage(){
        $list = HomePage::select('section1', 'section2', 'section3', 'section4', 'section5', 'section6', 'section7', 'section8', 'section9', 'section10')
                    ->where('status', 1)
                    ->latest()->first();
        // Decode the JSON into a PHP array
        $data = json_decode($list->section4, true);
        // Decode the country string (which is itself a JSON array) into an actual PHP array
        $country_id_array = json_decode($data['country'], true);
        $list->country_list = Country::select('id', 'name', 'country_short_code')
                                    ->whereIn('id', $country_id_array)
                                    ->get();

        $city_id_array = json_decode($data['city'], true);
        $list->city_list = City::select('id', 'name')
                                ->whereIn('id', $city_id_array)
                                ->get();

        $list->live_jobs= PostJob::where('status', 1)->count();
        $list->companies= Employer::where('status', 1)->count();
        $list->candidates= User::where('role_id', env('JOB_SEEKER_ROLE_ID'))->count();
        $list->new_jobs= PostJob::where('posting_open_date', '<=', date('Y-m-d'))
                                ->where('posting_close_date', '>=', date('Y-m-d'))
                                ->where('status', 1)
                                ->count();

        $locationCounts = [];

        foreach ($list->live_jobs as $job) {
            $locationIds = json_decode($job->location_countries, true);
            if (is_array($locationIds)) {
                foreach ($locationIds as $locId) {
                    if (!isset($locationCounts[$locId])) {
                        $locationCounts[$locId] = 0;
                    }
                    $locationCounts[$locId]++;
                }
            }
        }

        // Sort by count descending
        arsort($locationCounts);
        $list->posted_jobs_countries = $locationCounts;

        $list->posted_jobs_category = PostJob::select('post_jobs.job_category', 'job_categories.name', DB::raw('COUNT(*) as total'))
                                            ->join('job_categories', 'post_jobs.job_category', '=', 'job_categories.id')
                                            ->groupBy('post_jobs.job_category', 'job_categories.name')
                                            ->orderByDesc('total')
                                            ->take(8)
                                            ->get();
        return $this->sendResponse(
            $list,
            'Home page details'
        );
    }

    public function get_general_settings(Request $request){
        $sql = GeneralSetting::where('is_active', 1)->latest();
        if(!empty($request->slug)){
            $params = explode(',', $request->slug);
            $sql->whereIn('slug', $params);
        }
        $list = $sql->get();
        return $this->sendResponse(
            $list,
            'General settings list'
        );
    }

    public function get_testimonials(Request $request){
        $sql = Testimonial::where('status', 1)->latest();
        $list = $sql->get();
        return $this->sendResponse(
            $list,
            'Testimonials list'
        );
    }

    public function get_testimonials_details(Request $request, $id){
        $data = Testimonial::find($id);
        return $this->sendResponse(
            $data,
            'Testimonials details'
        );
    }

    public function get_designation_by_param(Request $request)
    {
        $sql = Designation::select('id', 'name')->where('status', 1);
        if(!empty($request->key)){
            $sql->where('name', 'ILIKE',  '%'.$request->key.'%');
        }
        return $this->sendResponse(
            $sql->get(),
            'List'
        );
    }

    public function get_industry_by_param(Request $request)
    {
        $sql = Industry::select('id', 'name')->where('status', 1);
        if(!empty($request->key)){
            $sql->where('name', 'ILIKE',  '%'.$request->key.'%');
        }
        return $this->sendResponse(
            $sql->get(),
            'List'
        );
    }

    public function get_jobsearch_keys(Request $request)
    {
        $keywords_array = $location_array = [];
        if(!empty($request->keywords)){
            $keywords_array = explode(',', $request->keywords);

            $designations = Designation::select('id', 'name');
            $industries = Industry::select('id', 'name');
            $itskills = ItSkill::select('id', 'name');
            foreach($keywords_array as $index => $key){
                if($index > 0){
                    $designations->orWhere('name', 'ILIKE', '%'.$key.'%');
                    $industries->orWhere('name', 'ILIKE', '%'.$key.'%');
                    $itskills->orWhere('name', 'ILIKE', '%'.$key.'%');
                }else{
                    $designations->where('name', 'ILIKE', '%'.$key.'%');
                    $industries->where('name', 'ILIKE', '%'.$key.'%');
                    $itskills->where('name', 'ILIKE', '%'.$key.'%');
                }
            }
            $designations_array = $designations->orderBy('name', 'ASC')->get()->pluck('name')->toArray();
            $industries_array = $industries->orderBy('name', 'ASC')->get()->pluck('name')->toArray();
            $itskills_array = $itskills->orderBy('name', 'ASC')->get()->pluck('name')->toArray();

            $keywords_array = array_merge($designations_array, $industries_array, $itskills_array);
            $keywords_array = array_unique($keywords_array);
        }
        if(!empty($request->location)){
            $location_array = explode(',', $request->location);
            $country = Country::select('id', 'name');
            $city = City::select('id', 'name');
            foreach($location_array as $index => $key){
                if($index > 0){
                    $country->orWhere('name', 'ILIKE', '%'.$key.'%');
                    $city->orWhere('name', 'ILIKE', '%'.$key.'%');
                }else{
                    $country->where('name', 'ILIKE', '%'.$key.'%');
                    $city->where('name', 'ILIKE', '%'.$key.'%');
                }
            }
            $country_array = $country->orderBy('name', 'ASC')->get()->pluck('name')->toArray();
            $city_array = $city->orderBy('name', 'ASC')->get()->pluck('name')->toArray();

            $location_array = array_merge($country_array, $city_array);
            $location_array = array_unique($location_array);
        }
        return $this->sendResponse([
            'keywords'=> $keywords_array,
            'location'=> $location_array,
        ],
            'List'
        );
    }



}
