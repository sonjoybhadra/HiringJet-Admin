<?php

namespace App\Http\Controllers\Api\Employer;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;

use App\Models\DemoRequest;

class DemoRequestsController extends BaseApiController
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string',
            'country_code' => 'required',
            'phone' => 'required|unique:demo_requests',
            'email' => 'required|string|email|unique:demo_requests',
            'city' => 'required|string',
            'organization' => 'required|string',
            'interested_in' => 'required|string'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            DemoRequest::insertGetId([
                'name'=> $request->name,
                'country_code'=> $request->country_code,
                'phone'=> $request->phone,
                'email'=> $request->email,
                'city'=> $request->city,
                'organization'=> $request->organization,
                'interested_in'=> $request->interested_in,
            ]);

            return $this->sendResponse([], 'Demo request is submitted successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

}
