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
            $sql = PostJob::where('job_type', $job_type);
            if(!empty($request->country)){
                $sql->whereJsonContains('location_countries', $request->country);
            }
            if(!empty($request->city)){
                $sql->whereJsonContains('location_cities', $request->city);
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
            if(!empty($request->salary)){
                $sql->where('min_salary', '>=', $request->salary);
                $sql->where('max_salary', '<=', $request->salary);
            }

            $sql->latest();
            if($request->paginate){
                $sql->paginate(15);
            }
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
                PostJob::where('job_no', $slug)->first(),
                'Job details'
            );
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

}
