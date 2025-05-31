<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use Illuminate\Support\Facades\Storage;

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

use App\Models\Nationality;


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
            'course'=> $this->get_course(1),
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
        ];
        if(!empty($request->params )){
            $params = explode(',', $request->params);
            foreach($params as $param){
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
        $list = Country::select('id', 'name', 'country_code', 'country_flag')->where('status', 1)->get();
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
        $list = Country::select('country_code', 'country_flag')->where('status', 1)->distinct('country_code')->get();
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
        $list = Currency::select('name')
                        ->where('status', 1)
                        ->with('country')
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

    public function get_course($res = '')
    {
        $list = Course::select('id', 'name')->where('status', 1)
                    ->with('qualification')
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

}
