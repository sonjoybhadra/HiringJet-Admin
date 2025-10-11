<?php

namespace App\Http\Controllers\Api\Employer;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;

use App\Models\EmployerJobseekerProfileView;

class EmployerJobseekerProfileViewController extends BaseApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sql = EmployerJobseekerProfileView::where('employer_id', auth()->user()->id);
        if(!empty($request->action_type)){
            $sql->where('action_type', $request->action_type);
        }else{
            $sql->whereNull('action_type');
        }
        $list = $sql->latest()->get();
        return $this->sendResponse([
            $list,
        ], 'Get request List.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            EmployerJobseekerProfileView::create([
                'jobseeker_id'=> $request->jobseeker_id,
                'employer_id'=> auth()->user()->id??NULL,
                'action_type'=> $request->action_type??NULL,
                'year'=> date('Y'),
                'month'=> date('m')
            ]);

            return $this->sendResponse([], 'Request submitted successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
