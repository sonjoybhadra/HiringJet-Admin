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
use App\Models\UserEmployment;
use App\Models\UserSkill;

class JobSearchController extends BaseApiController
{
    /**
     * Get jobs by request params.
    */
    public function getJobsByParams(Request $request, $job_type)
    {
        try {
            $sql = PostJob::select('post_jobs.*')->where('job_type', $job_type);
            if(Auth::guard('api')->check()){
                $sql->addSelect(DB::raw('(SELECT COUNT(*) FROM post_job_user_applieds WHERE post_job_user_applieds.user_id = '.Auth::guard('api')->user()->id.' and post_job_user_applieds.job_id = post_jobs.id and post_job_user_applieds.status=1) AS job_applied_status'));

                $sql->addSelect(DB::raw('(SELECT COUNT(*) FROM shortlisted_jobs WHERE shortlisted_jobs.user_id = '.Auth::guard('api')->user()->id.' and shortlisted_jobs.job_id = post_jobs.id and shortlisted_jobs.status=1) AS job_shortlisted_status'));
            }

            if(!empty($request->country)){
                $countrys = $request->country;
                $sql->where(function ($q) use ($countrys) {
                    foreach ($countrys as $tag) {
                        $q->orWhereJsonContains('location_countries', (string)$tag);
                    }
                });
            }
            if(!empty($request->city)){
                $citys = $request->city;
                $sql->where(function ($q) use ($citys) {
                    foreach ($citys as $tag) {
                        $q->orWhereJsonContains('location_cities', (string)$tag);
                    }
                });
            }
            if(!empty($request->skills)){
                $skills = $request->skills;
                $sql->where(function ($q) use ($skills) {
                    foreach ($skills as $tag) {
                        $q->orWhereJsonContains('skill_ids', (string)$tag);
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
            $sql->latest();
            return $this->sendResponse(
                $sql->get(),
                'Job list by params'
            );
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
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
                return $this->sendError('Error', 'Sorry!! Something went wrong. Unable to process right now.', 201);
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

        //try{
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
        // }catch (\Exception $exception) {
        //     return $this->sendError('Error', 'Sorry!! Something went wrong. Unable to process right now.', Response::HTTP_INTERNAL_SERVER_ERROR);
        // }
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
