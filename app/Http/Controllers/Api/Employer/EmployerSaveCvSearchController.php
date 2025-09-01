<?php

namespace App\Http\Controllers\Api\Employer;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use App\Models\EmployerCvSearch;
use App\Models\User;
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
        $own_list = EmployerCvSearch::where('employer_id', auth()->user()->id)
                                ->latest()->get();
        $users_data_list = [];
        //for employers
        if(empty(auth()->user()->parent_id)){
            $child_users_id = User::where('parent_id', auth()->user()->id)->get()->pluck('id')->toArray();
            if(!empty($child_users_id)){
                $users_data_list = EmployerCvSearch::whereIn('employer_id', $child_users_id)
                                                    ->latest()->get();

                /* if($users_data_list->count() > 0){
                    foreach($users_data_list as $index => $val){
                        $users_data_list[$index]->profile_cv_count = EmployerCvSearch::where('tag_id', $val->id)->count();
                        $users_data_list[$index]->shared_employers = [];
                    }
                } */
            }
        }
        return $this->sendResponse([
            'own_list' => $own_list,
            'users_data_list' => $users_data_list,
        ], 'Search CV List.');
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
                'email_ids'=> !empty($request->email_ids) ? json_encode($request->email_ids) : NULL,
                'alert_frequency'=> $request->alert_frequency,
                'status' => 1,
                'created_at'=> date('Y-m-d h:i:s')
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
            $data->email_ids = !empty($request->email_ids) ? json_encode($request->email_ids) : NULL;
            $data->alert_frequency = $request->alert_frequency;
            $data->updated_at = date('Y-m-d h:i:s');
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
