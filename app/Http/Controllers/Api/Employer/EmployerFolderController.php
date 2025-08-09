<?php

namespace App\Http\Controllers\Api\Employer;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;

use App\Models\EmployerCvFolder;
use App\Models\EmployerCvProfile;
use App\Models\User;

class EmployerFolderController extends BaseApiController
{
    /**
     * Display a listing of the resource.
    */
    public function index()
    {
        return $this->sendResponse($this->getList(), 'List of CV folders');
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
        $data = EmployerCvFolder::where('id', $id)->first()->with('profile_cv')->first();
        $jobseeker_id = $data->profile_cv ? $data->profile_cv->pluck('jobseeker_id')->toArray() : [];
        $data->profile_cv_jobseeker = [];
        if(count($jobseeker_id) > 0){
            $data->profile_cv_jobseeker = User::with('user_profile')->whereIn('id', $jobseeker_id)->get();
        }
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

            EmployerCvFolder::where('id', $id)->update([
                'user_id'=> auth()->user()->id,
                'user_employer_id'=> auth()->user()->user_employer_details->id,
                'folder_name'=> strtolower($request->folder_name),
                'owner_id'=> auth()->user()->id,
                'status'=> 1
            ]);

            return $this->sendResponse($this->getList(), 'CV folder updated successfully.');
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

        return $this->sendResponse($this->getList(), 'CV folder deleted successfully.');
    }

    /**
     * Change the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */
    public function changeStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|int'
        ]);

        $data = EmployerCvFolder::findOrFail($id);
        $data->status = $request->status;
        $data->updated_at = date('Y-m-d H:i:s');
        $data->save();

        return $this->sendResponse($this->getList(), 'CV folder status updated successfully.');
    }

    /**
     * Store a newly created resource in storage.
    */
    public function saveProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jobseeker_id' => 'required|integer',
            'folder_id' => 'required|integer',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            $has_data = EmployerCvProfile::where('user_id', auth()->user()->id)
                                        ->where('cv_folders_id', $request->folder_id)
                                        ->where('jobseeker_id', $request->jobseeker_id)
                                        ->count();
            if($has_data > 0){
                return $this->sendError('Error', 'Same name profile is already present in this folder.', Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            EmployerCvProfile::create([
                'user_id'=> auth()->user()->id,
                'cv_folders_id'=> $request->folder_id,
                'jobseeker_id'=> $request->jobseeker_id
            ]);


            return $this->sendResponse([], 'Profile added in selected folder successfully.');
        }catch (\Exception $exception) {
            return $this->sendError('Error', 'Sorry!! Something went wrong. Unable to process right now.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function getList(){
        $own_list = EmployerCvFolder::with('profile_cv')
                                ->where('user_id', auth()->user()->id)
                                ->orderBy('folder_name', 'ASC')
                                ->get();
        if($own_list->count() > 0){
            foreach($own_list as $index => $val){
                $own_list[$index]->profile_cv_count = EmployerCvProfile::where('cv_folders_id', $val->id)->count();
                $own_list[$index]->shared_employers = EmployerCvFolder::select('folder_name', 'first_name', 'last_name', 'users.id AS user_employer_id')
                                                            ->join('users', 'users.id', '=', 'employer_cv_folders.user_id')
                                                            ->where('user_id', '!=', auth()->user()->id)
                                                            ->where('owner_id', auth()->user()->id)
                                                            ->where('folder_name', $val->tag_name)
                                                            ->get()->toArray();
            }
        }

        $shared_list = EmployerCvFolder::with('profile_cv')
                                ->where('user_id', auth()->user()->id)
                                ->where('owner_id', '!=', auth()->user()->id)
                                ->orderBy('folder_name', 'ASC')
                                ->get();
        if($shared_list->count() > 0){
            foreach($shared_list as $index => $val){
                $shared_list[$index]->profile_cv_count = EmployerCvProfile::where('cv_folders_id', $val->id)->count();
                $shared_list[$index]->shared_employers = [];
            }
        }

        return [
            'own_list' => $own_list,
            'shared_list'  => $shared_list
        ];
    }

    public function share(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'emplyer_id' => 'required|array',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            $folder = EmployerCvFolder::find($id);
           /*  $has_data = EmployerCvFolder::where('user_id', $request->emplyer_id)
                                        ->where('folder_name', strtolower($folder->folder_name))
                                        ->count();
            if($has_data > 0){
                return $this->sendError('Error', 'Same name folder is already exists.', Response::HTTP_UNPROCESSABLE_ENTITY);
            } */
            EmployerCvFolder::where('user_id', '!=', auth()->user()->id)
                            ->where('owner_id', auth()->user()->id)
                            ->where('folder_name', $folder->folder_name)
                            ->delete();

            foreach($request->emplyer_id as $emplyer_id){
                if(!empty($emplyer_id)){
                    $employer = User::find($emplyer_id);
                    EmployerCvFolder::create([
                        'user_id'=> $emplyer_id,
                        'user_employer_id'=> $employer->user_employer_details->id,
                        'folder_name'=> strtolower($folder->folder_name),
                        'owner_id'=> $folder->owner_id,
                        'status'=> 1
                    ]);
                }
            }

            return $this->sendResponse($this->getList(), 'Folder shared with selected user successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

}
