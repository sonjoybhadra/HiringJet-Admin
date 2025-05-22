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
    public function get_jobcategory()
    {
        return $this->sendResponse(
            JobCategory::select('id', 'name')->where('status', 1)->get(),
            'List'
        );
    }

    public function get_industry()
    {
        return $this->sendResponse(
            Industry::select('id', 'name')->where('status', 1)->get(),
            'List'
        );
    }

    public function get_designation()
    {
        return $this->sendResponse(
            Designation::select('id', 'name')->where('status', 1)->get(),
            'List'
        );
    }

    public function get_employer()
    {
        return $this->sendResponse(
            Employer::select('id', 'name', 'logo', 'description', 'no_of_employee')
                        ->where('status', 1)
                        ->with('industry')
                        ->get(),
            'List'
        );
    }

    public function get_country()
    {
        return $this->sendResponse(
            Country::select('id', 'name', 'country_code', 'country_flag')->where('status', 1)->get(),
            'List'
        );
    }

    public function get_city($country_id)
    {
        return $this->sendResponse(
            City::select('id', 'name')->where('status', 1)->where('country_id', $country_id)->get(),
            'List'
        );
    }

    public function get_country_code()
    {
        return $this->sendResponse(
            Country::select('country_code', 'country_flag')->where('status', 1)->distinct('country_code')->get(),
            'List'
        );
    }

    public function get_currency()
    {
        return $this->sendResponse(
            Currency::select('country_code', 'country_flag')
                        ->where('status', 1)
                        ->distinct('country_code')
                        ->with('country')
                        ->get(),
            'List'
        );
    }

    public function get_keyskill()
    {
        return $this->sendResponse(
            Keyskill::select('id', 'name')->where('status', 1)->get(),
            'List'
        );
    }

    public function get_perkbenefit()
    {
        return $this->sendResponse(
            PerkBenefit::select('id', 'name')->where('status', 1)->get(),
            'List'
        );
    }

    public function get_availability()
    {
        return $this->sendResponse(
            Availability::select('id', 'name')->where('status', 1)->get(),
            'List'
        );
    }

    public function get_currentworklevel()
    {
        return $this->sendResponse(
            CurrentWorkLevel::select('id', 'name')->where('status', 1)->get(),
            'List'
        );
    }

    public function get_functionalarea()
    {
        return $this->sendResponse(
            FunctionalArea::select('id', 'name')->where('status', 1)->get(),
            'List'
        );
    }

    public function get_onlineprofile()
    {
        return $this->sendResponse(
            OnlineProfile::select('id', 'name', 'logo')->where('status', 1)->get(),
            'List'
        );
    }

    public function get_qualification()
    {
        return $this->sendResponse(
            Qualification::select('id', 'name')->where('status', 1)->get(),
            'List'
        );
    }

    public function get_course()
    {
        return $this->sendResponse(
            Course::select('id', 'name')->where('status', 1)
                    ->with('qualification')
                    ->get(),
            'List'
        );
    }

    public function get_specialization()
    {
        return $this->sendResponse(
            Specialization::select('id', 'name')->where('status', 1)
                            ->with('qualification')
                            ->with('course')
                            ->get(),
            'List'
        );
    }

    public function get_nationality()
    {
        return $this->sendResponse(
            Nationality::select('id', 'name')->where('status', 1)->get(),
            'List'
        );
    }

    public function get_religion()
    {
        return $this->sendResponse(
            Religion::select('id', 'name')->where('status', 1)->get(),
            'List'
        );
    }

    public function get_university()
    {
        return $this->sendResponse(
            University::select('id', 'name')->where('status', 1)->get(),
            'List'
        );
    }

    public function get_mostcommonemail()
    {
        return $this->sendResponse(
            MostCommonEmail::select('id', 'emailid_domain')->where('status', 1)->get(),
            'List'
        );
    }

    public function get_language()
    {
        return $this->sendResponse(
            Language::select('id', 'name')->where('status', 1)->get(),
            'List'
        );
    }

    public function get_maritalstatus()
    {
        return $this->sendResponse(
            MaritalStatus::select('id', 'name')->where('status', 1)->get(),
            'List'
        );
    }

    public function get_proficiency_level()
    {
        return $this->sendResponse(
            ['Beginner', 'Intermediate', 'Advanced', 'Expert'],
            'List'
        );
    }


}
