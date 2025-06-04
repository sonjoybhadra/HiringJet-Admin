<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;

use App\Models\ReportBug;

class ReportBugController extends BaseApiController
{
    public function postReportBug(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'nullable|string',
            'phone' => 'required|string',
            'category' => 'required|string',
            'description' => 'required|string',
            'source' => 'required|string'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            ReportBug::insertGetId([
                'user_id'=> auth()->user()->id??NULL,
                'email'=> $request->email,
                'phone'=> $request->phone,
                'category'=> $request->category,
                'description'=> $request->description,
                'source'=> $request->source,
            ]);

            return $this->sendResponse($this->getUserDetails(), 'Report bug is submitted successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
