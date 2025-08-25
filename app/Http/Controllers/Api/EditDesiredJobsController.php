<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;

use App\Models\UserProfile;
use App\Models\Designation;
use App\Models\City;
use App\Models\Industry;

class EditDesiredJobsController extends BaseApiController
{
    /**
        * Get resume headline.
    */
    public function getDesiredJobs()
    {
        try {
            return $this->sendResponse(
                UserProfile::select('job_type_temp', 'job_type_permanent', 'temp_remote','temp_onsite','temp_hybrid','permanent_remote', 'permanent_onsite', 'permanent_hybrid', 'profile_summery', 'preferred_designation', 'preferred_location', 'preferred_industry', 'availability_id')
                            ->where('user_id', auth()->user()->id)
                            ->with('availabilitie')
                            ->first(),
                'Desired jobs preference list'
            );
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
    /**
        * Post resume headline.
    */
    public function postDesiredJobs(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'preferred_designation' => 'nullable|array',
            'preferred_location' => 'nullable|array',
            'preferred_industry' => 'nullable|array',
            'availability' => 'nullable|integer',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            $preferred_designation = $preferred_location = $preferred_industry = [];
            if(!empty($request->preferred_designation)){
                $designations = Designation::whereIn('id', $request->preferred_designation)->get();
                if($designations->count() > 0){
                    foreach($designations as $designation){
                        $preferred_designation[] = ['id'=> $designation->id, 'name'=> $designation->name];
                    }
                }
            }
            if(!empty($request->preferred_location)){
                $locations = City::whereIn('id', $request->preferred_location)->get();
                if($locations->count() > 0){
                    foreach($locations as $location){
                        $preferred_location[] = ['id'=> $location->id, 'name'=> $location->name];
                    }
                }
            }
            if(!empty($request->preferred_industry)){
                $industrys = Industry::whereIn('id', $request->preferred_industry)->get();
                if($industrys->count() > 0){
                    foreach($industrys as $industry){
                        $preferred_industry[] = ['id'=> $industry->id, 'name'=> $industry->name];
                    }
                }
            }

            UserProfile::where('user_id', auth()->user()->id)
                        ->update([
                            'job_type_temp'=> $request->job_type_temp,
                            'job_type_permanent'=> $request->job_type_permanent,
                            'temp_remote'=> $request->temp_remote,
                            'temp_onsite'=> $request->temp_onsite,
                            'temp_hybrid'=> $request->temp_hybrid,
                            'permanent_remote'=> $request->permanent_remote,
                            'permanent_onsite'=> $request->permanent_onsite,
                            'permanent_hybrid'=> $request->permanent_hybrid,
                            'preferred_designation' => !empty($preferred_designation) ? json_encode($preferred_designation) : NULL,
                            'preferred_location' => !empty($preferred_location) ? json_encode($preferred_location) : NULL,
                            'preferred_industry' => !empty($preferred_industry) ? json_encode($preferred_industry) : NULL,
                            'availability_id' => $request->availability,
                        ]);

            return $this->sendResponse($this->getUserDetails(), 'Desired Job updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
