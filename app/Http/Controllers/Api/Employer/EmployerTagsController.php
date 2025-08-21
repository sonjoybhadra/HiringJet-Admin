<?php

namespace App\Http\Controllers\Api\Employer;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use Illuminate\Support\Facades\Storage;

use App\Models\EmployerTag;
use App\Models\TagJobseekerMapping;

class EmployerTagsController extends BaseApiController
{
    public function __construct()
    {
        //
    }

    /**
     * Display a listing of the resource.
    */
    public function index()
    {
        return $this->sendResponse($this->getList(), 'List of tags');
    }

    /**
     * Registered member step 1.
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tag_name' => 'required|string|max:255'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            $has_duplicate = EmployerTag::where('user_id', auth()->user()->id)
                                            ->where('tag_name', 'ilike', '%'.$request->tag_name.'%')
                                            ->get()->count();
            if($has_duplicate > 0){
                return $this->sendError('Duplicate Error', 'Duplicate tag is exists', Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            EmployerTag::insert([
                'user_id'=> auth()->user()->id,
                'tag_name'=> $request->tag_name,
                'owner_id'=> auth()->user()->id,
                'status'=> 1
            ]);

            return $this->sendResponse($this->getList(), 'Tag added successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
    */
    public function show(string $id)
    {
        $data = EmployerTag::where('id', $id)->first();
        $data->is_own_tag = ($data->user_id == auth()->user()->id && $data->owner_id == auth()->user()->id) ? true : false;
        if($data->is_own_tag){
            $tag_id_array = [$id];
        }else{
            $tag_id_array = EmployerTag::where('user_id', auth()->user()->id)
                                            ->where('owner_id', '!=', auth()->user()->id)
                                            ->where('parent_id', $data->parent_id)
                                            ->get()->pluck('id')->toArray();
        }
        $data->jobseekers_profiles = TagJobseekerMapping::select('users.id', 'users.first_name','users.last_name', 'users.email')
                                                        ->join('users', 'users.id', '=', 'tag_jobseeker_mappings.jobseeker_id')
                                                        ->whereIn('tag_jobseeker_mappings.tag_id', $tag_id_array)
                                                        ->get();

        return $this->sendResponse($data, 'Details of tag');
    }

    /**
     * Registered member step 1.
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'tag_name' => 'required|string|max:255',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            $has_duplicate = EmployerTag::where('user_id', auth()->user()->id)
                                            ->where('tag_name', 'ilike', '%'.$request->tag_name.'%')
                                            ->where('id', '!=', $id)
                                            ->get()->count();
            if($has_duplicate > 0){
                return $this->sendError('Duplicate Error', 'Duplicate tag is exists', Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            EmployerTag::find($id)->update([
                'tag_name'=> $request->tag_name
            ]);

            return $this->sendResponse($this->getList(), 'Tag updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = EmployerTag::findOrFail($id);
        $data->delete();

        return $this->sendResponse($this->getList(), 'Tag deleted successfully.');
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

        $data = EmployerTag::findOrFail($id);
        $data->status = $request->status;
        $data->updated_at = date('Y-m-d H:i:s');
        $data->save();

        return $this->sendResponse($this->getList(), 'Tag status updated successfully.');
    }

    /**
     * Store a newly created resource in storage.
    */
    public function saveProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jobseeker_id' => 'required|integer',
            'tag_id' => 'required|integer',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            $has_data = TagJobseekerMapping::where('user_id', auth()->user()->id)
                                        ->where('tag_id', $request->tag_id)
                                        ->where('jobseeker_id', $request->jobseeker_id)
                                        ->count();
            if($has_data > 0){
                return $this->sendError('Error', 'Same name profile is already present in this tag.', Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            TagJobseekerMapping::create([
                'user_id'=> auth()->user()->id,
                'tag_id'=> $request->tag_id,
                'jobseeker_id'=> $request->jobseeker_id
            ]);


            return $this->sendResponse([], 'Profile added in selected tag successfully.');
        }catch (\Exception $exception) {
            return $this->sendError('Error', 'Sorry!! Something went wrong. Unable to process right now.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function getList(){
        $own_list = EmployerTag::where('user_id', auth()->user()->id)->orderBy('tag_name', 'ASC')->get();
        if($own_list->count() > 0){
            foreach($own_list as $index => $val){
                $own_list[$index]->shared_employers = EmployerTag::select('tag_name', 'first_name', 'last_name', 'users.id AS user_employer_id')
                                                            ->join('users', 'users.id', '=', 'employer_tags.owner_id')
                                                            ->where('user_id', '!=', auth()->user()->id)
                                                            ->where('owner_id', auth()->user()->id)
                                                            ->where('parent_id', $val->id)
                                                            ->get()->toArray();
            }
        }
        $shared_list = EmployerTag::where('user_id', auth()->user()->id)
                                            ->where('owner_id', '!=', auth()->user()->id)
                                            ->orderBy('tag_name', 'ASC')->get();

        if($shared_list->count() > 0){
            foreach($shared_list as $index => $val){
                $shared_list[$index]->shared_employers = [];
            }
        }
        return [
            'own_list' => $own_list,
            'shared_list' => $shared_list,
        ];
    }

    /**
     * Registered member step 1.
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function share(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'emplyer_id' => 'required|array',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            $tag = EmployerTag::find($id);
            EmployerTag::where('user_id', '!=', auth()->user()->id)
                        ->where('owner_id', auth()->user()->id)
                        ->where('parent_id', $id)
                        ->delete();
            foreach($request->emplyer_id as $emplyer_id){
                if(!empty($emplyer_id)){
                    EmployerTag::insert([
                        'user_id'=> $emplyer_id,
                        'tag_name'=> $tag->tag_name,
                        'owner_id'=> $tag->owner_id,
                        'parent_id'=> $id,
                        'status'=> 1
                    ]);
                }
            }

            return $this->sendResponse($this->getList(), 'Tag shared with selected user successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

}
