<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use Illuminate\Support\Facades\Storage;

use App\Models\UserCertification;
use App\Models\UserOnlineProfile;
use App\Models\UserWorkSample;

class EditAccomplishmentsController extends BaseApiController
{
    /**
     * Get Certification details.
    */
    public function getCertificationDetails()
    {
        try {
            return $this->sendResponse(
                UserCertification::where('user_id', auth()->user()->id)
                                    ->get(),
                'Certificate list'
            );
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
    /**
     * Update Certification details.
    */
    public function updateCertificationDetails(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'certification_name' => 'required|string',
            'certification_provider' => 'required|string',
            // 'certification_url' => 'required|integer',
            'from_month' => 'required|integer',
            'from_year' => 'required|integer',
            'to_month' => 'required|integer',
            'to_year' => 'required|integer',
            'has_expire' => 'required|boolean',
            'certification_image' => 'required|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            $image_path = "";
            if (request()->hasFile('certification_image')) {
                $file = request()->file('certification_image');
                $fileName = md5($file->getClientOriginalName() .'_'. time()) . "." . $file->getClientOriginalExtension();
                Storage::disk('public')->put('uploads/user/certification/'.$fileName, file_get_contents($file));
                $image_path = 'public/storage/uploads/user/certification/'.$fileName;
            }
            UserCertification::where('id', $id)->update([
                'certification_name'=> $request->certification_name,
                'certification_provider'=> $request->certification_provider,
                'certification_url'=> $request->certification_url,
                'from_month'=> $request->from_month,
                'from_year'=> $request->from_year,
                'to_month'=> $request->to_month,
                'to_year'=> $request->to_year,
                'has_expire'=> $request->has_expire,
                'certification_image'=> $image_path,
            ]);

            return $this->sendResponse([], 'Certification details updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    /**
     * Post Certification details.
    */
    public function postCertificationDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'certification_name' => 'required|string',
            'certification_provider' => 'required|string',
            // 'certification_url' => 'required|integer',
            'from_month' => 'required|integer',
            'from_year' => 'required|integer',
            'to_month' => 'required|integer',
            'to_year' => 'required|integer',
            'has_expire' => 'required|boolean',
            'certification_image' => 'required|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            $image_path = "";
            if (request()->hasFile('certification_image')) {
                $file = request()->file('certification_image');
                $fileName = md5($file->getClientOriginalName() .'_'. time()) . "." . $file->getClientOriginalExtension();
                Storage::disk('public')->put('uploads/user/certification/'.$fileName, file_get_contents($file));
                $image_path = 'public/storage/uploads/user/certification/'.$fileName;
            }
            UserCertification::insert([
                'user_id'=> auth()->user()->id,
                'certification_name'=> $request->certification_name,
                'certification_provider'=> $request->certification_provider,
                'certification_url'=> $request->certification_url,
                'from_month'=> $request->from_month,
                'from_year'=> $request->from_year,
                'to_month'=> $request->to_month,
                'to_year'=> $request->to_year,
                'has_expire'=> $request->has_expire,
                'certification_image'=> $image_path,
            ]);

            return $this->sendResponse([], 'Certification details saved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    /**
     * Get OnlineProfile details.
    */
    public function getOnlineProfile()
    {
        try {
            return $this->sendResponse(
                UserOnlineProfile::where('user_id', auth()->user()->id)
                                    ->get(),
                'Online profile details'
            );
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
    /**
     * Update OnlineProfile details.
    */
    public function postOnlineProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'personal_website' => 'nullable|url',
            'linkedin' => 'nullable|url',
            'twitter' => 'nullable|url',
            'youtube' => 'nullable|url',
            'instagram' => 'nullable|url'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            $data_array = [];
            if(!empty($request->personal_website)){
                $data_array[] = [
                    'user_id'=> auth()->user()->id,
                    'profile_key'=> 'personal_website',
                    'value'=>$request->personal_website
                ];
            }
            if(!empty($request->linkedin)){
                $data_array[] = [
                    'user_id'=> auth()->user()->id,
                    'profile_key'=> 'linkedin',
                    'value'=>$request->linkedin
                ];
            }
            if(!empty($request->twitter)){
                $data_array[] = [
                    'user_id'=> auth()->user()->id,
                    'profile_key'=> 'twitter',
                    'value'=>$request->twitter
                ];
            }
            if(!empty($request->youtube)){
                $data_array[] = [
                    'user_id'=> auth()->user()->id,
                    'profile_key'=> 'youtube',
                    'value'=>$request->youtube
                ];
            }
            if(!empty($request->instagram)){
                $data_array[] = [
                    'user_id'=> auth()->user()->id,
                    'profile_key'=> 'instagram',
                    'value'=>$request->instagram
                ];
            }

            UserCertification::create();

            return $this->sendResponse([$data_array], 'Online profile updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    /**
     * Get Work Sample details.
    */
    public function getWorkSampleDetails()
    {
        try {
            return $this->sendResponse(
                UserWorkSample::where('user_id', auth()->user()->id)
                                    ->get(),
                'Work Sample list'
            );
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
    /**
     * Update Work Sample details.
    */
    public function updateWorkSampleDetails(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'sample_title' => 'required|string',
            'sample_url' => 'nullable|url',
            'from_month' => 'required|integer',
            'from_year' => 'required|integer',
            'to_month' => 'required|integer',
            'to_year' => 'required|integer',
            'currently_working' => 'required|boolean',
            'sample_description' => 'nullable|string',
            'sample_image' => 'required|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            $image_path = "";
            if (request()->hasFile('sample_image')) {
                $file = request()->file('sample_image');
                $fileName = md5($file->getClientOriginalName() .'_'. time()) . "." . $file->getClientOriginalExtension();
                Storage::disk('public')->put('uploads/user/work/'.$fileName, file_get_contents($file));
                $image_path = 'public/storage/uploads/user/work/'.$fileName;
            }
            UserWorkSample::where('id', $id)->update([
                'sample_title'=> $request->sample_title,
                'sample_url'=> $request->sample_url,
                'from_month'=> $request->from_month,
                'from_year'=> $request->from_year,
                'to_month'=> $request->to_month,
                'to_year'=> $request->to_year,
                'currently_working'=> $request->currently_working,
                'sample_description'=> $request->sample_description,
                'sample_image'=> $image_path
            ]);

            return $this->sendResponse([], 'Work Sample details updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    /**
     * Post Work Sample details.
    */
    public function postWorkSampleDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sample_title' => 'required|string',
            'sample_url' => 'nullable|url',
            'from_month' => 'required|integer',
            'from_year' => 'required|integer',
            'to_month' => 'required|integer',
            'to_year' => 'required|integer',
            'currently_working' => 'required|boolean',
            'sample_description' => 'nullable|string',
            'sample_image' => 'required|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            $image_path = "";
            if (request()->hasFile('sample_image')) {
                $file = request()->file('sample_image');
                $fileName = md5($file->getClientOriginalName() .'_'. time()) . "." . $file->getClientOriginalExtension();
                Storage::disk('public')->put('uploads/user/work/'.$fileName, file_get_contents($file));
                $image_path = 'public/storage/uploads/user/work/'.$fileName;
            }
            UserCertification::insert([
                'user_id'=> auth()->user()->id,
                'sample_title'=> $request->sample_title,
                'sample_url'=> $request->sample_url,
                'from_month'=> $request->from_month,
                'from_year'=> $request->from_year,
                'to_month'=> $request->to_month,
                'to_year'=> $request->to_year,
                'currently_working'=> $request->currently_working,
                'sample_description'=> $request->sample_description,
                'sample_image'=> $image_path
            ]);

            return $this->sendResponse([], 'Work Sample details saved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

}
