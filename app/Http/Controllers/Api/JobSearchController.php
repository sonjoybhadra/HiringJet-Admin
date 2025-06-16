<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Mail;
use Validator;
use App\Mail\NotificationEmail;

use App\Models\PostJob;
use App\Models\PostJobUserApplied;

class JobSearchController extends BaseApiController
{
    /**
     * Get jobs by request params.
    */
    public function getJobsByParams(Request $request, $job_type)
    {
        try {
            $sql = PostJob::where('job_type', $job_type)
                            ->where('posting_open_date', '<=', date('Y-m-d 00:00:01'))
                            ->where('posting_close_date', '>=', date('Y-m-d 23:59:59'));
            if(!empty($request->country)){
                $countrys = $request->country;
                $sql->where(function ($q) use ($countrys) {
                    foreach ($countrys as $tag) {
                        $q->orWhereJsonContains('location_countries', $tag);
                    }
                });
            }
            if(!empty($request->city)){
                $citys = $request->city;
                $sql->where(function ($q) use ($citys) {
                    foreach ($citys as $tag) {
                        $q->orWhereJsonContains('location_cities', $tag);
                    }
                });
            }
            if(!empty($request->skills)){
                $skills = $request->skills;
                $sql->where(function ($q) use ($skills) {
                    foreach ($skills as $tag) {
                        $q->orWhereJsonContains('skill_ids', $tag);
                    }
                });
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
                $sql->whereIn('experience_level', $request->experience);
            }
            if(!empty($request->salary)){
                $sql->whereRaw('? BETWEEN min_salary AND max_salary', [$request->salary]);
            }
            $sql->with('employer');
            $sql->with('industryRelation');
            $sql->with('jobCategory');
            $sql->with('nationalityRelation');
            $sql->with('departmentRelation');
            $sql->with('functionalArea');
            $sql->with('experienceLevel');
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
            return $this->sendResponse(
                PostJob::where('job_no', $slug)
                        ->with('employer')
                        ->with('industryRelation')
                        ->with('jobCategory')
                        ->with('nationalityRelation')
                        ->with('departmentRelation')
                        ->with('functionalArea')
                        ->with('experienceLevel')
                        ->first(),
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
                        'Applied Jobs list'
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
            $data = PostJobUserApplied::where('user_id', auth()->user()->id)
                                        ->with('job_details')
                                        ->latest()->get();
            return $this->sendResponse(
                $data,
                'Applied Jobs list'
            );
        }catch (\Exception $exception) {
            return $this->sendError('Error', 'Sorry!! Something went wrong. Unable to process right now.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
