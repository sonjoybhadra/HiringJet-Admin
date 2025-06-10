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

    public function getJobDetails(Request $request, $job_type, $id)
    {
        try {
            return $this->sendResponse(
                PostJob::findOrFail($id),
                'Job details'
            );
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

}
