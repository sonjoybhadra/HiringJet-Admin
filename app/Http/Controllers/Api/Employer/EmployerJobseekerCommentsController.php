<?php

namespace App\Http\Controllers\Api\Employer;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use App\Models\EmployerJobseekerComments;
/**-------------------------- SME -------------------------------- */
class EmployerJobseekerCommentsController extends BaseApiController
{
    private $employer;

    public function __construct()
    {
        $this->employer = env('EMPLOYER_ROLE_ID');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function index(Request $request)
    {
        $list = EmployerJobseekerComments::where('employer_id', auth()->user()->id)
                                        ->with('jobseekers')
                                        ->latest()->get();
        return $this->sendResponse($list, 'Jobseeker Comments List.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * First Name, Last Name, Email ID, Contact Number, Role/Designation, Manage Permission and Usage limits: CV Search / Job posting.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jobseeker_id' => 'required|integer',
            'comment' => 'required|string'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            $id = EmployerJobseekerComments::insertGetId([
                'employer_id' => auth()->user()->id,
                'jobseeker_id'=> $request->jobseeker_id,
                'comment'=> $request->comment,
                'status' => 1,
                'created_at'=> date('Y-m-d h:i:s')
            ]);

            if($id){
                return $this->sendResponse([
                    'id'=> $id], 'Comment saved successfully.');
            }else{
                return $this->sendError('Error', 'Sorry!! Unable to save Comment.');
            }
        }catch(\Exception $cus_ex){
            // Error through. Some error occurred
            return $this->sendError('DB Error', $cus_ex->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */
    public function show($id)
    {
        $list = EmployerJobseekerComments::where('id', $id)
                                        ->with('jobseekers')
                                        ->first();
        return $this->sendResponse($list, 'Comment details.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'jobseeker_id' => 'required|integer',
            'comment' => 'required|string'
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            $data = EmployerJobseekerComments::findOrFail($id);
            $data->jobseeker_id = $request->jobseeker_id;
            $data->comment = $request->comment;
            $data->updated_at = date('Y-m-d h:i:s');
            $data->save();

            return $this->sendResponse([$data], 'Comment has successfully updated.');
        }catch(\Exception $cus_ex){
            // Error through. Some error occurred
            return $this->sendError('Error', $cus_ex->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try{
            EmployerJobseekerComments::where('id', $id)
                                    ->delete();

            return $this->sendResponse([], 'Comment has successfully deleted.');
        }catch(\Exception $cus_ex){
            return $this->sendError('Error', $cus_ex->getMessage(), 500);
        }
    }

}
