<?php

namespace App\Http\Controllers\Api\Employer;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use App\Models\EmployerCvSearch;
/**-------------------------- SME -------------------------------- */
class EmployerSaveCvSearchController extends BaseApiController
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
        $list = EmployerCvSearch::where('employer_id', auth()->user()->id)
                                ->latest()->get();
        return $this->sendResponse($list, 'Search CV List.');
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
            'search_json' => 'required|string',
            'title' => 'required|string|max:255',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            $id = EmployerCvSearch::insertGetId([
                'employer_id' => auth()->user()->id,
                'search_json'=> json_encode($request->search_json),
                'title'=> $request->title,
            ]);

            if($id){
                return $this->sendResponse([
                    'id'=> $id
                ], 'Search CV saved successfully.');
            }else{
                return $this->sendError('Error', 'Sorry!! Unable to saved search.');
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
        $list = EmployerCvSearch::where('id', $id)
                                    ->first();
        return $this->sendResponse($list, 'Search details.');
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
            'search_json' => 'required|string',
            'title' => 'required|string|max:255',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            $data = EmployerCvSearch::findOrFail($id);
            $data->search_json = json_encode($request->search_json);
            $data->title = $request->title;
            $data->save();

            return $this->sendResponse([$data], 'Search data has successfully updated.');
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
            EmployerCvSearch::where('id', $id)
                ->where('parent_id', auth()->user()->id)
                ->delete();

            return $this->sendResponse([], 'Search data has successfully deleted.');
        }catch(\Exception $cus_ex){
            return $this->sendError('Error', $cus_ex->getMessage(), 500);
        }
    }

}
