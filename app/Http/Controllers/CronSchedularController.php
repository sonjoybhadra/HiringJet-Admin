<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendSimilarJobsAlert;

use App\Models\JobJobseekerSimilarJobsAlert;
use App\Models\PostJob;
use App\Models\Designation;
use App\Models\Industry;
use App\Models\ItSkill;
use App\Models\Country;
use App\Models\City;
use App\Models\JobCategory;

class CronSchedularController extends Controller
{
    //

    public function sendSimilarJobsAlert(){
        $list = JobJobseekerSimilarJobsAlert::with('jobseeker')->latest()->take(1)->get();
        if($list->count() > 0){
            foreach($list as $value){
                $payload = json_decode($value->search_string);
                // print_r($payload->industry);
                // echo $payload->job_type;
                // dd($payload);
                $sql = PostJob::select('post_jobs.*');
                $sql->where('status', 1);
                $sql->where('posting_close_date', '>=', date('Y-m-d'));

                if(!empty($payload->job_type) && strtolower($payload->job_type) != 'all-jobs'){
                    $sql->where('job_type', $payload->job_type);
                }
                if(!empty($payload->keyword)){
                    $keywords_array = explode(',', $payload->keyword);

                    $designations = Designation::whereIn('name', $keywords_array)->get()->pluck('id')->toArray();
                    $industries = Industry::whereIn('name', $keywords_array)->get()->pluck('id')->toArray();
                    $itskills = ItSkill::whereIn('name', $keywords_array)->get()->pluck('id')->toArray();
                    if(count($designations) > 0){
                        $sql->where(function ($q) use ($designations) {
                            foreach ($designations as $tag) {
                                $q->orWhere('designation', (string)$tag);
                            }
                        });
                    }

                    if(count($industries) > 0){
                        $sql->where(function ($q) use ($industries) {
                            foreach ($industries as $tag) {
                                $q->orWhere('industry', (string)$tag);
                            }
                        });
                    }

                    if(count($itskills) > 0){
                        $sql->where(function ($q) use ($itskills) {
                            foreach ($itskills as $tag) {
                                $q->orWhereRaw(
                                    "CASE
                                        WHEN skill_ids IS NULL OR skill_ids = '' THEN FALSE
                                        ELSE skill_ids::jsonb @> ?::jsonb
                                    END",
                                    [json_encode([$tag])]
                                );
                            }
                        });
                    }
                }
                $country_ids = $city_ids = $location_array = [];
                if(!empty($payload->location)){
                    $location_array = explode(',', $payload->location);
                    if(count($location_array) > 1){
                        $country_ids = Country::whereIn('name', $location_array)->get()->pluck('id')->toArray();
                        $city_ids = City::whereIn('name', $location_array)->get()->pluck('id')->toArray();
                    }else{
                        $country_ids = Country::whereRaw('LOWER(name) LIKE ?', [strtolower($payload->location)])->get()->pluck('id')->toArray();
                        $city_ids = City::whereRaw('LOWER(name) LIKE ?', [strtolower($payload->location)])->get()->pluck('id')->toArray();
                    }
                    if(!empty($country_ids)){
                        $sql->where(function ($q) use ($country_ids) {
                            foreach ($country_ids as $tag) {
                                $q->orWhereRaw(
                                    "CASE
                                        WHEN location_countries IS NULL OR location_countries = '' THEN FALSE
                                        ELSE location_countries::jsonb @> ?::jsonb
                                    END",
                                    [json_encode([(string)$tag])]
                                );
                            }
                        });
                    }
                    if(!empty($city_ids)){
                        $sql->where(function ($q) use ($city_ids) {
                            foreach ($city_ids as $tag) {
                                $q->orWhereRaw(
                                    "CASE
                                        WHEN location_cities IS NULL OR location_cities = '' THEN FALSE
                                        ELSE location_cities::jsonb @> ?::jsonb
                                    END",
                                    [json_encode([(string)$tag])]
                                );
                            }
                        });
                    }
                }

                if(!empty($payload->job_category)){
                    $category = JobCategory::where('name', 'ILIKE', $payload->job_category)->first();
                    if($category){
                        $sql->where('job_category', $category->id);
                    }
                }

                /* $countrys = $this->filterRequestParam($payload->country);
                if(!empty($countrys) && count($countrys) > 0){
                    $sql->orWhere(function ($q) use ($countrys) {
                        foreach ($countrys as $tag) {
                            $q->orWhereRaw(
                                "CASE
                                    WHEN location_countries IS NULL OR location_countries = '' THEN FALSE
                                    ELSE location_countries::jsonb @> ?::jsonb
                                END",
                                [json_encode([$tag])]
                            );
                        }
                    });
                }

                $citys = $this->filterRequestParam($payload->city);
                if(!empty($citys) && count($citys) > 0){
                    $sql->where(function ($q) use ($citys) {
                        foreach ($citys as $tag) {
                            $q->orWhereRaw(
                                "CASE
                                    WHEN location_cities IS NULL OR location_cities = '' THEN FALSE
                                    ELSE location_cities::jsonb @> ?::jsonb
                                END",
                                [json_encode([$tag])]
                            );
                        }
                    });
                } */
                if(!empty($payload->designation)){
                    $designation = $this->filterRequestParam($payload->designation);
                    if(!empty($designation) && count($designation) > 0){
                        $sql->whereIn('designation', $designation);
                    }
                }
                if(!empty($payload->employer)){
                    $employer = $this->filterRequestParam($payload->employer);
                    if(!empty($employer)){
                        $sql->whereIn('employer_id', $employer);
                    }
                }
                if(!empty($payload->industry)){
                    $industry = $this->filterRequestParam($payload->industry);
                    if(!empty($industry) && count($industry) > 0){
                        $sql->whereIn('industry', $industry);
                    }
                }
                if(!empty($payload->nationality)){
                    $nationality = $this->filterRequestParam($payload->nationality);
                    if(!empty($nationality) && count($nationality) > 0){
                        $sql->whereIn('nationality', $nationality);
                    }
                }
                if(!empty($payload->skills)){
                    $skills = $this->filterRequestParam($payload->skills);
                    if(!empty($skills) && count($skills) > 0){
                        $skills = $payload->skills;
                        $sql->where(function ($q) use ($skills) {
                            foreach ($skills as $tag) {
                                $q->orWhereRaw(
                                    "CASE
                                        WHEN skill_ids IS NULL OR skill_ids = '' THEN FALSE
                                        ELSE skill_ids::jsonb @> ?::jsonb
                                    END",
                                    [json_encode([$tag])]
                                );
                            }
                        });
                    }
                }

                if(!empty($payload->experience)){
                    $experience = explode('-', $payload->experience);
                    if(count($experience) > 1){
                        $sql->where('min_exp_year', '>=', $experience[0]);
                        $sql->where('max_exp_year', '<=', $experience[1]);
                    }
                }

                if(!empty($payload->gender)){
                    $sql->where('gender', $payload->gender);
                }

                if(!empty($payload->freshness)){
                    $sql->where('created_at', '>=', date('Y-m-d', strtotime('-'.$payload->freshness.' days')));
                }
                $sql->with('employer');
                $sql->with('industryRelation');
                $sql->with('jobCategory');
                $sql->with('nationalityRelation');
                $sql->with('contractType');
                $sql->with('designationRelation');
                $sql->with('functionalArea');
                $jobs = $sql->latest()->take(5)->get()->toArray();
                // echo '<pre>';
                // print_r($jobs);
                // dd($value->jobseeker);
                $jobseeket_name = $value->jobseeker->first_name.' '.$value->jobseeker->last_name;
                $email = $value->jobseeker->email;
                Mail::to('work.chayan2020@gmail.com')->send(new SendSimilarJobsAlert($jobseeket_name, $jobs));
            }
        }
    }

    private function filterRequestParam($data){
        if(is_array($data)){
            $filter_array = [];
            if(!empty($data)){
                foreach($data as $d){
                    if(!empty($d) && $d != NULL){
                        array_push($filter_array, $d);
                    }
                }
            }
            return $filter_array;
        }else{
            return $data;
        }
    }
}
