<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Mail\NotificationEmail;

use App\Models\PostJob;
use App\Models\PostJobUserApplied;
use App\Models\ShortlistedJob;
use App\Models\Designation;
use App\Models\Industry;
use App\Models\ItSkill;
use App\Models\JobCategory;
use App\Models\Country;
use App\Models\City;
use App\Models\Employer;
use App\Models\Nationality;
use App\Models\UserJobSearchHistory;

class JobSearchController extends BaseApiController
{
    /**
     * Get jobs by request params.
    */
    public function getJobsByParams(Request $request, $job_type)
    {
        try {
            $job_search_data = [
                'user_id'=> NULL,
                'ip'=> $_SERVER['REMOTE_ADDR'],
                'search_string'=> json_encode($request->all())
            ];
            dd($job_search_data);
            if(Auth::guard('api')->check()){
                $job_search_data['user_id'] = auth()->user()->id;
                $saved_jobs = UserJobSearchHistory::where('user_id', auth()->user()->id)->latest()->get();
            }else{
                $saved_jobs = UserJobSearchHistory::where('ip', $_SERVER['REMOTE_ADDR'])->latest()->get();
            }
            dd($saved_jobs);
            if($saved_jobs->count() < 5){
                UserJobSearchHistory::create($job_search_data);
            }else{
                UserJobSearchHistory::where('id', $saved_jobs[0]->id)->update($job_search_data);
            }

            $sql = PostJob::select('post_jobs.*');
            //$sql->where('posting_close_date', '>=', date('Y-m-d'));
            if(strtolower($job_type) != 'all-jobs'){
                $sql->where('job_type', $job_type);
            }
            if(Auth::guard('api')->check()){
                $sql->addSelect(DB::raw('(SELECT COUNT(*) FROM post_job_user_applieds WHERE post_job_user_applieds.user_id = '.Auth::guard('api')->user()->id.' and post_job_user_applieds.job_id = post_jobs.id and post_job_user_applieds.status=1) AS job_applied_status'));

                $sql->addSelect(DB::raw('(SELECT COUNT(*) FROM shortlisted_jobs WHERE shortlisted_jobs.user_id = '.Auth::guard('api')->user()->id.' and shortlisted_jobs.job_id = post_jobs.id and shortlisted_jobs.status=1) AS job_shortlisted_status'));
            }

            if(!empty($request->keyword)){
                $keywords_array = explode(',', $request->keyword);

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

            if(!empty($request->location)){
                $location_array = explode(',', $request->location);
                $country_ids = Country::whereIn('name', $location_array)->get()->pluck('id')->toArray();
                $city_ids = City::whereIn('name', $location_array)->get()->pluck('id')->toArray();
                if(!empty($country_ids)){
                    $sql->where(function ($q) use ($location_array) {
                        foreach ($location_array as $tag) {
                            $q->orWhereRaw(
                                "CASE
                                    WHEN location_country_names IS NULL OR location_country_names = '' THEN FALSE
                                    ELSE location_country_names::jsonb @> ?::jsonb
                                END",
                                [json_encode([$tag])]
                            );
                        }
                    });
                }
                if(!empty($city_ids)){
                    $sql->where(function ($q) use ($location_array) {
                        foreach ($location_array as $tag) {
                            $q->orWhereRaw(
                                "CASE
                                    WHEN location_city_names IS NULL OR location_city_names = '' THEN FALSE
                                    ELSE location_city_names::jsonb @> ?::jsonb
                                END",
                                [json_encode([$tag])]
                            );
                        }
                    });
                }
            }

            if(!empty($request->job_category)){
                $category = JobCategory::where('name', 'ILIKE', $request->job_category)->first();
                if($category){
                    $sql->where('job_category', $category->id);
                }
            }

            if(!empty($request->country)){
                $countrys = $request->country;
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

            if(!empty($request->city)){
                $citys = $request->city;
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
            }

            if(!empty($request->skills)){
                $skills = $request->skills;
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

            if($request->designation){
                $sql->where('designation', $request->designation);
            }
            if(!empty($request->industry)){
                $sql->whereIn('industry', $request->industry);
            }
            if(!empty($request->nationality)){
                $sql->whereIn('nationality', $request->nationality);
            }
            if(!empty($request->employer)){
                $sql->whereIn('employer_id', $request->employer);
            }
            if(!empty($request->experience)){
                $sql->whereIn('min_exp_year', $request->experience);
            }
            if(!empty($request->gender)){
                $sql->where('gender', $request->gender);
            }
            if(!empty($request->freshness)){
                $sql->where('created_at', '>=', date('Y-m-d', strtotime('-'.$request->freshness.' days')));
            }
            /* if(!empty($request->salary)){
                $sql->whereRaw('? BETWEEN min_salary AND max_salary', [$request->salary]);
            } */
            $sql->with('employer');
            $sql->with('industryRelation');
            $sql->with('jobCategory');
            $sql->with('nationalityRelation');
            $sql->with('contractType');
            $sql->with('designationRelation');
            $sql->with('functionalArea');
            // $sql->with('applied_users');
            $all_data_sql = $pagination_sql = $sql->latest();

            $limit = 5;
            $offset = 0;
            if($request->page && $request->page > 1){
                $limit += 10;
                //$offset = 25 + (($request->page - 2) * $limit);
            }

            $list = $pagination_sql->limit($limit)->offset($offset)->get();
            $filter_data_array = $this->getFilterParametersArray($all_data_sql->get());
            return $this->sendResponse([
                    'jobs'=> $list,
                    'filter_array'=> $filter_data_array,
                    'page'=> $request->page,
                    'take'=> ['limit'=> $limit, 'offset'=> $offset]
                ],
                'List search jobs'
            );
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    private function getFilterParametersArray($data_array){
        $data_count_country_array = $data_count_city_array = $data_count_industry_array = $data_count_designation_array = $data_count_nationality_array = $data_count_gender_array = $data_count_employers_array = $data_count_freshness_array = $data_count_experience_array = [];
        $freshness = [1, 3, 7, 15, 30, 60];
        $experience = ['0-1', '2-5', '6-10', '11-15', '16-20', '21'];
        foreach ($data_array as $job) {
            // get location Country list
            $data_ids = json_decode($job->location_countries, true);
            if (is_array($data_ids)) {
                foreach ($data_ids as $id) {
                    if (!isset($data_count_country_array[$id])) {
                        $data = Country::find($id);
                        $data_count_country_array[$id] = ['name'=> $data ? $data->name : '', 'count'=> 0, 'id'=> $id];
                    }
                    $data_count_country_array[$id]['count'] = $data_count_country_array[$id]['count']+1;
                }
            }
            // end location country
            // get location Clty list
            $data_ids = json_decode($job->location_cities, true);
            if (is_array($data_ids)) {
                foreach ($data_ids as $id) {
                    if (!isset($data_count_city_array[$id])) {
                        $data = City::find($id);
                        $data_count_city_array[$id] = ['name'=> $data ? $data->name : '', 'count'=> 0, 'id'=> $id];
                    }
                    $data_count_city_array[$id]['count'] = $data_count_city_array[$id]['count']+1;
                }
            }
            // end location Clty
            // get industry list
            if (!empty($job->industry)) {
                if (!isset($data_count_industry_array[$job->industry])) {
                    $data = Industry::find($job->industry);
                    $data_count_industry_array[$job->industry] = ['name'=> $data ? $data->name : '', 'count'=> 0, 'id'=> $id];
                }
                $data_count_industry_array[$job->industry]['count'] = $data_count_industry_array[$job->industry]['count']+1;
            }
            // end industry
            // get nationality list
            if (!empty($job->nationality)) {
                if (!isset($data_count_nationality_array[$job->nationality])) {
                    $data = Nationality::find($job->nationality);
                    $data_count_nationality_array[$job->nationality] = ['name'=> $data ? $data->name : '', 'count'=> 0, 'id'=> $data->id];
                }
                $data_count_nationality_array[$job->nationality]['count'] = $data_count_nationality_array[$job->nationality]['count']+1;
            }
            // end nationality
            // get designation list
            if (!empty($job->designation)) {
                if (!isset($data_count_designation_array[$job->designation])) {
                    $data = Designation::find($job->designation);
                    $data_count_designation_array[$job->designation] = ['name'=> $data ? $data->name : '', 'count'=> 0, 'id'=> $data->id];
                }
                $data_count_designation_array[$job->designation]['count'] = $data_count_designation_array[$job->designation]['count']+1;
            }
            // end designation
            // get gender list
            if (!empty($job->gender)) {
                if (!isset($data_count_gender_array[$job->gender])) {
                    $data_count_gender_array[$job->gender] = ['name'=> $job->gender, 'count'=> 0, 'id'=> $data->id];
                }
                $data_count_gender_array[$job->gender]['count'] = $data_count_gender_array[$job->gender]['count']+1;
            }
            // end gender
            // get employers list
            if (!empty($job->employer_id)) {
                if (!isset($data_count_employers_array[$job->employer_id])) {
                     $data = Employer::find($job->employer_id);
                    $data_count_employers_array[$job->employer_id] = ['name'=> $data ? $data->name : '', 'count'=> 0, 'id'=> $data->id];
                }
                $data_count_employers_array[$job->employer_id]['count'] = $data_count_employers_array[$job->employer_id]['count']+1;
            }
            // end employers
            // get freshness list
            foreach($freshness as $day){
                $freshness_day = date('Y-m-d', strtotime('-'.$day.' days'));
                if($job->created_at >= $freshness_day){
                    if (!isset($data_count_freshness_array[$day])) {
                        $data_count_freshness_array[$day] = ['name'=> $day.' days old', 'count'=> 0, 'id'=> $day];
                    }
                    $data_count_freshness_array[$day]['count'] = $data_count_freshness_array[$day]['count']+1;
                }
            }
            // end freshness
            // get freshness list
            foreach($experience as $value){
                $min_max_year = explode('-', $value);
                if((count($min_max_year) > 1 && $job->min_exp_year >= (int)$min_max_year[0] && $job->max_exp_year <= (int)$min_max_year[1]) || ($job->max_exp_year >= (int)$min_max_year[0])){
                    if (!isset($data_count_experience_array[$value])) {
                        $data_count_experience_array[$value] = ['name'=> $value.' years', 'count'=> 0, 'id'=> $value];
                    }
                    $data_count_experience_array[$value]['count'] = $data_count_experience_array[$value]['count']+1;
                }
            }
            // end freshness
        }   //end foreach

        return [
            'country' => $data_count_country_array,
            'city' => $data_count_city_array,
            'industry' => $data_count_industry_array,
            'designation' => $data_count_designation_array,
            'nationality' => $data_count_nationality_array,
            'employers' => $data_count_employers_array,
            'gender'=> $data_count_gender_array,
            'freshness'=> $data_count_freshness_array,
            'experience'=> $data_count_experience_array
        ];
    }

    public function getJobDetails(Request $request, $job_type, $slug)
    {
        try {
            $sql = PostJob::select('post_jobs.*')->where('job_no', $slug)
                            ->with('employer')
                            ->with('industryRelation')
                            ->with('jobCategory')
                            ->with('nationalityRelation')
                            ->with('contractType')
                            ->with('designationRelation')
                            ->with('functionalArea');
            if(Auth::guard('api')->check()){
                $sql->addSelect(DB::raw('(SELECT COUNT(*) FROM post_job_user_applieds WHERE post_job_user_applieds.user_id = '.Auth::guard('api')->user()->id.' and post_job_user_applieds.job_id = post_jobs.id and post_job_user_applieds.status=1) AS job_applied_status'));

                $sql->addSelect(DB::raw('(SELECT COUNT(*) FROM shortlisted_jobs WHERE shortlisted_jobs.user_id = '.Auth::guard('api')->user()->id.' and shortlisted_jobs.job_id = post_jobs.id and shortlisted_jobs.status=1) AS job_shortlisted_status'));
            }
            return $this->sendResponse(
                $sql->first(),
                'Job details'
            );
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function postJobApply(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job_id' => 'required|integer'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            $job_details = PostJob::find($request->job_id);
            if($job_details && $job_details->posting_close_date >= date('Y-m-d')){
                $has_data = PostJobUserApplied::where('user_id', auth()->user()->id)->where('job_id', $request->job_id)->count();
                if($has_data == 0){
                    $applied_job_id = PostJobUserApplied::insertGetId([
                        'job_id'=> $request->job_id,
                        'user_id'=> auth()->user()->id,
                        'status'=> 1,
                        'created_at'=> date('Y-m-d H:i:s')
                    ]);

                    $full_name = auth()->user()->first_name.' '.auth()->user()->last_name;
                    Mail::to(auth()->user()->email)->send(new NotificationEmail('Job applied successfully.', $full_name, 'You have applied for this job successfully.'));
                    return $this->sendResponse(
                        ['applied_job_id'=> $applied_job_id],
                        'You have successfully applied for the job.'
                    );
                }else{
                    return $this->sendError('Warning', 'You have already applied for this job.', 201);
                }
            }else{
                return $this->sendError('Error', 'Sorry!! job apply date is over.', 201);
            }
        }catch (\Exception $exception) {
            return $this->sendError('Error', 'Sorry!! Something went wrong. Unable to process right now.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function jobseekerAppliedJobs(Request $request)
    {
        try{
            $job_ids = PostJobUserApplied::select('job_id')->where('user_id', auth()->user()->id)
                                            ->get()->pluck('job_id')->toArray();
            $data = [];
            if(count($job_ids)){
                $data = PostJob::whereIn('id', $job_ids)
                            ->with('employer')
                            ->with('industryRelation')
                            ->with('jobCategory')
                            ->with('nationalityRelation')
                            ->with('contractType')
                            ->with('designationRelation')
                            ->with('functionalArea')
                            ->latest()->get();
            }
            return $this->sendResponse(
                $data,
                'Applied Jobs list'
            );
        }catch (\Exception $exception) {
            return $this->sendError('Error', 'Sorry!! Something went wrong. Unable to process right now.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function shortlistedJob(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job_id' => 'required|integer',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            $job_details = PostJob::find($request->job_id);
            if($job_details && $job_details->posting_close_date >= date('Y-m-d')){
                $has_data = ShortlistedJob::where('user_id', auth()->user()->id)->where('job_id', $request->job_id)->first();
                if(!$has_data){
                    ShortlistedJob::create([
                        'job_id'=> $request->job_id,
                        'user_id'=> auth()->user()->id,
                        'status'=> 1,
                        'created_at'=> date('Y-m-d H:i:s')
                    ]);

                    $msg = 'Job shortlisted successfully.';
                    $full_name = auth()->user()->first_name.' '.auth()->user()->last_name;
                    Mail::to(auth()->user()->email)->send(new NotificationEmail('Shortlisted Job.', $full_name, 'Shortlisted Job saved successfully.'));
                }else{
                    if($has_data->status == 1){
                        $update_date = [
                            'status'=> 0,
                            'deleted_at'=> date('Y-m-d H:i:s')
                        ];
                        $msg = 'Removed job from shortlisted.';
                    }else{
                        $update_date = [
                            'status'=> 1,
                            'deleted_at'=> null
                        ];
                        $msg = 'Job shortlisted successfully.';
                        $full_name = auth()->user()->first_name.' '.auth()->user()->last_name;
                        Mail::to(auth()->user()->email)->send(new NotificationEmail('Shortlisted Job.', $full_name, 'Shortlisted Job saved successfully.'));
                    }
                    $update_date['updated_at'] = date('Y-m-d H:i:s');
                    ShortlistedJob::where('id', $has_data->id)->update($update_date);
                }
                return $this->sendResponse(
                    ShortlistedJob::where('user_id', auth()->user()->id)->where('status', 1)->with('job_details')->latest()->get(),
                    $msg
                );
            }else{
                return $this->sendError('Error', 'Sorry!! Something went wrong. Unable to process right now.', 201);
            }
        }catch (\Exception $exception) {
            return $this->sendError('Internal Error', $exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getShortlistedJob(Request $request)
    {
        try{
            $job_ids = ShortlistedJob::select('job_id')->where('user_id', auth()->user()->id)
                                        ->get()->pluck('job_id')->toArray();
            $data = [];
            if(count($job_ids)){
                $data = PostJob::whereIn('id', $job_ids)
                            ->with('employer')
                            ->with('industryRelation')
                            ->with('jobCategory')
                            ->with('nationalityRelation')
                            ->with('contractType')
                            ->with('designationRelation')
                            ->with('functionalArea')
                            ->latest()->get();
            }
            return $this->sendResponse(
                $data,
                'Shortlisted Jobs list'
            );
        }catch (\Exception $exception) {
            return $this->sendError('Error', 'Sorry!! Something went wrong. Unable to process right now.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getMatchedJobsForJobseeker(Request $request)
    {
        try {
            /* $jobseeker_designation = UserEmployment::select('last_designation')
                                                    ->where('user_id', auth()->user()->id)
                                                    ->orderBy('is_current_job', 'DESC')
                                                    ->first();

            $user_skills = UserSkill::where('user_id', auth()->user()->id)->get()->pluck('keyskill_id')->toArray();

            $sql = PostJob::select('post_jobs.*');
            $sql->addSelect(DB::raw('(SELECT COUNT(*) FROM post_job_user_applieds WHERE post_job_user_applieds.user_id = '.auth()->user()->id.' and post_job_user_applieds.job_id = post_jobs.id and post_job_user_applieds.status=1) AS job_applied_status'));
            $sql->addSelect(DB::raw('(SELECT COUNT(*) FROM shortlisted_jobs WHERE shortlisted_jobs.user_id = '.auth()->user()->id.' and shortlisted_jobs.job_id = post_jobs.id and shortlisted_jobs.status=1) AS job_shortlisted_status'));

            if($jobseeker_designation){
                $sql->where('designation', $jobseeker_designation->last_designation);
            }
            if(!empty($user_skills)){
                foreach ($user_skills as $tag) {
                    $sql->orWhereJsonContains('skill_ids', (string)$tag);
                }
            } */
            $postJobObj = new PostJob();
            $sql = $postJobObj->get_job_search_custom_sql();
            $sql->with('employer');
            $sql->with('industryRelation');
            $sql->with('jobCategory');
            $sql->with('nationalityRelation');
            $sql->with('contractType');
            $sql->with('designationRelation');
            $sql->with('functionalArea');
            // $sql->with('applied_users');
            $sql->latest();
            return $this->sendResponse(
                $sql->get(),
                'Matched Job list'
            );
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

}
