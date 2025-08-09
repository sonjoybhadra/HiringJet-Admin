<?php

namespace App\Http\Controllers\Api\Employer;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use Illuminate\Support\Facades\Storage;

use App\Models\EmployerTag;

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

    private function getList(){
        $own_list = EmployerTag::where('user_id', auth()->user()->id)->orderBy('tag_name', 'ASC')->get();
        if($own_list->count() > 0){
            foreach($own_list as $index => $val){
                $own_list[$index]->shared_employers = EmployerTag::select('tag_name', 'first_name', 'last_name', 'users.id AS user_employer_id')
                                                            ->join('users', 'users.id', '=', 'employer_tags.user_id')
                                                            ->where('user_id', '!=', auth()->user()->id)
                                                            ->where('owner_id', auth()->user()->id)
                                                            ->where('tag_name', $val->tag_name)
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
            /* $has_duplicate = EmployerTag::where('user_id', $request->emplyer_id)
                                            ->where('tag_name', 'ilike', '%'.$tag->tag_name.'%')
                                            ->get()->count();
            if($has_duplicate > 0){
                return $this->sendError('Duplicate Error', 'Duplicate tag is exists', Response::HTTP_UNPROCESSABLE_ENTITY);
            } */
            EmployerTag::where('user_id', '!=', auth()->user()->id)
                        ->where('owner_id', auth()->user()->id)
                        ->where('tag_name', $tag->tag_name)
                        ->delete();
            foreach($request->emplyer_id as $emplyer_id){
                if(!empty($emplyer_id)){
                    EmployerTag::insert([
                        'user_id'=> $emplyer_id,
                        'tag_name'=> $tag->tag_name,
                        'owner_id'=> $tag->owner_id,
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
