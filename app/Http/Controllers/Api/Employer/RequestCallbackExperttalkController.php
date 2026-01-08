<?php

namespace App\Http\Controllers\Api\Employer;


use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;

use App\Models\RequestCallbackExperttalk;

class RequestCallbackExperttalkController extends BaseApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $particular = '')
    {
        $sql = RequestCallbackExperttalk::where('created_by', auth()->user()->id);
        if($particular != ''){
            $sql->where('particulars', $particular);
        }
        $list = $sql->latest()->get();
        return $this->sendResponse([
            $list,
        ], 'Get request List.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $particular)
    {
        /* $validator = Validator::make($request->all(), [
            'email' => 'nullable|string',
            'phone' => 'required|string',
            'category' => 'required|string',
            'description' => 'required|string',
            'source' => 'required|string'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } */
       if(empty($request->all())){
            return $this->sendError('Validation Error', 'Please fill some data to post.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            RequestCallbackExperttalk::create([
                'name'=> $request->name,
                'email'=> $request->email,
                'country_code'=> $request->country_code,
                'phone'=> $request->phone,
                'preferred_time'=> $request->preferred_time,
                'particulars'=> $particular,
                'created_by'=> auth()->user()->id??NULL,
            ]);

            return $this->sendResponse([], 'Request submitted successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
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
        //
    }
}
