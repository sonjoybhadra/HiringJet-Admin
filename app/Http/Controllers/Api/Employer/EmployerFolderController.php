<?php

namespace App\Http\Controllers\Api\Employer;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;

use App\Models\EmployerCvFolder;

class EmployerFolderController extends BaseApiController
{
    /**
     * Display a listing of the resource.
    */
    public function index()
    {
        $list = EmployerCvFolder::where('user_id', auth()->user()->id)
                                ->orderBy('folder_name', 'ASC')
                                ->get();
        return $this->sendResponse($list, 'List of CV folders');
    }

    /**
     * Store a newly created resource in storage.
    */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'folder_name' => 'required|string'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            $has_data = EmployerCvFolder::where('user_id', auth()->user()->id)
                                        ->where('folder_name', strtolower($request->folder_name))
                                        ->count();
            if($has_data > 0){
                return $this->sendError('Error', 'Same name folder is already exists.', Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            EmployerCvFolder::create([
                'user_id'=> auth()->user()->id,
                'user_employer_id'=> auth()->user()->user_employer_details->id,
                'folder_name'=> strtolower($request->folder_name),
                'owner_id'=> auth()->user()->id,
                'status'=> 1
            ]);

            $list = EmployerCvFolder::where('user_id', auth()->user()->id)
                                ->orderBy('folder_name', 'ASC')
                                ->get();
            return $this->sendResponse($list, 'CV folder created successfully.');
        }catch (\Exception $exception) {
            return $this->sendError('Error', 'Sorry!! Something went wrong. Unable to process right now.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = EmployerCvFolder::find($id);
        return $this->sendResponse($data, 'Details CV folders');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'folder_name' => 'required|string'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            $has_data = EmployerCvFolder::where('user_id', auth()->user()->id)
                                        ->where('folder_name', strtolower($request->folder_name))
                                        ->where('id', '!=', $id)
                                        ->count();
            if($has_data > 0){
                return $this->sendError('Error', 'Same name folder is already exists.', Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            EmployerCvFolder::where('id', $id)->upate([
                'user_id'=> auth()->user()->id,
                'user_employer_id'=> auth()->user()->user_employer_details->id,
                'folder_name'=> strtolower($request->folder_name),
                'owner_id'=> auth()->user()->id,
                'status'=> 1
            ]);

            $list = EmployerCvFolder::where('user_id', auth()->user()->id)
                                ->orderBy('folder_name', 'ASC')
                                ->get();
            return $this->sendResponse($list, 'CV folder updated successfully.');
        }catch (\Exception $exception) {
            return $this->sendError('Error', 'Sorry!! Something went wrong. Unable to process right now.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = EmployerCvFolder::findOrFail($id);
        $data->delete();

        $list = EmployerCvFolder::where('user_id', auth()->user()->id)
                                ->orderBy('folder_name', 'ASC')
                                ->get();
        return $this->sendResponse($list, 'CV folder deleted successfully.');
    }
}
