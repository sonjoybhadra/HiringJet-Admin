<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Models\PostJob;

class JobSearchController extends BaseApiController
{
    /**
     * Get jobs by request params.
    */
    public function getJobsByParams(Request $request, $job_type)
    {
        try {
            $sql = PostJob::where('job_type', $job_type)
                            ->where('posting_open_date', '>=', date('Y-m-d 00:00:01'));
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

}
