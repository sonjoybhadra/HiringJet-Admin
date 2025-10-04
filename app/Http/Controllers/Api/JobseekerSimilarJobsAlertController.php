<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;

use App\Models\JobJobseekerSimilarJobsAlert;

class JobseekerSimilarJobsAlertController extends BaseApiController
{
    /**
     * Display a listing of the resource.
    */
    public function index()
    {
        return $this->sendResponse($this->getList(), 'List of saved jobs alert');
    }

    /**
     * Store a newly created resource in storage.
    */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'search_string' => 'required|string'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            $has_data = JobJobseekerSimilarJobsAlert::where('user_id', auth()->user()->id)->count();
            $user_limit = env('MAX_SAVED_SIMILAR_JOBS_ALERT_COUNT')??5;
            if($has_data >= $user_limit){
                return $this->sendError('Error', 'Your saved job alert limit is over. To add new please delete saved data from the list.', Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            JobJobseekerSimilarJobsAlert::create([
                'user_id'=> auth()->user()->id,
                'title'=> $request->search_string,
                'search_string'=> json_encode($request->search_string),
                'alert_start_date' => date('Y-m-d H:i:s'),
                'alert_till_date' => date('Y-m-d H:i:s', strtotime('+12 months')),
                'status'=> 1,
                'created_at'=> date('Y-m-d h:i:s')
            ]);

            return $this->sendResponse($this->getList(), 'CV folder created successfully.');
        }catch (\Exception $exception) {
            return $this->sendError('Error', 'Sorry!! Something went wrong. Unable to process right now.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
    */
    public function show(string $id)
    {
        return $this->sendResponse($this->getList($id), 'Details of saved jobs alert');
    }

    /**
     * Update the specified resource in storage.
    */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
    */
    public function destroy(string $id)
    {
        $data = JobJobseekerSimilarJobsAlert::findOrFail($id);
        $data->delete();

        return $this->sendResponse($this->getList(), 'Job alert deleted successfully.');
    }

    private function getList($id = ''){
        $sql = JobJobseekerSimilarJobsAlert::select('title','search_string')
                                ->where('user_id', auth()->user()->id);
        if($id != ''){
            return $sql->where('id', $id)->first();
        }else{
            return $sql->latest()
            ->get();
        }
    }
}
