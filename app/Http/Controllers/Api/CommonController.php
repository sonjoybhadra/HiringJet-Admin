<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use Illuminate\Support\Facades\Storage;

use App\Models\Industry;
use App\Models\Country;
use App\Models\Nationality;
use App\Models\Religion;
use App\Models\University;
use App\Models\Qualification;
use App\Models\Language;
use App\Models\Designation;
use App\Models\Keyskill;


class CommonController extends BaseApiController
{
    //
    public function get_industry()
    {
        return $this->sendResponse(
            Industry::select('id', 'name')->where('status', 1)->get(),
            'List'
        );
    }

    public function get_country()
    {
        return $this->sendResponse(
            Country::select('id', 'name', 'country_code')->where('status', 1)->get(),
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

    public function get_qualification()
    {
        return $this->sendResponse(
            Qualification::select('id', 'name')->where('status', 1)->get(),
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

    public function get_designation()
    {
        return $this->sendResponse(
            Designation::select('id', 'name')->where('status', 1)->get(),
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

    public function get_proficiency_level()
    {
        return $this->sendResponse(
            ['Beginner', 'Intermediate', 'Advanced', 'Expert'],
            'List'
        );
    }


}
