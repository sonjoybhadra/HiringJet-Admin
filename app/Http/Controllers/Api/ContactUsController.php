<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;

use App\Models\ContactUs;

class ContactUsController extends BaseApiController
{
    public function postContactUs(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string',
            'email' => 'required|string',
            'phone' => 'required|string',
            'city' => 'required|integer',
            'organization' => 'required|string',
            'interested_in' => 'required|string'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            ContactUs::insertGetId([
                'user_id'=> auth()->check() ? auth()->user()->id : NULL,
                'name'=> $request->name,
                'email'=> $request->email,
                'phone'=> $request->phone,
                'city_id'=> $request->city,
                'organization'=> $request->organization,
                'interested_in'=> $request->interested_in,
            ]);

            return $this->sendResponse([], 'Contact us request is submitted successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
