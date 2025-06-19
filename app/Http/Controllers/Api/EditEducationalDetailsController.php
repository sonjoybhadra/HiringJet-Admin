<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserEducation;

class EditEducationalDetailsController extends BaseApiController
{
    /**
     * Get educational details.
    */
    public function getEducationalDetails()
    {
        try {
            return $this->sendResponse(
                UserEducation::where('user_id', auth()->user()->id)
                                ->with('qualification')
                                ->with('course')
                                ->with('location')
                                ->with('university')
                                ->with('specialization')
                                ->get()
            );
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
    /**
     * Update educational details.
    */
    public function updateEducationalDetails(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'qualification' => 'required|integer',
            'university' => 'required|integer',
            'course' => 'required|integer',
            'specialization' => 'required|integer',
            'course_type' => 'required|string',
            'course_start_year' => 'required|integer',
            'course_end_year' => 'required|integer',
            'grade' => 'required|string',
            // 'location' => 'required|integer',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            UserEducation::where('id', $id)->update([
                'qualification_id'=> $request->qualification,
                'university_id'=> $request->university,
                'course_id'=> $request->course,
                'specialization_id'=> $request->specialization,
                'course_type'=> $request->course_type,
                'course_start_year'=> $request->course_start_year,
                'course_end_year'=> $request->course_end_year,
                'grade'=> $request->grade
                // 'location_id' => $request->location,
            ]);
            $this->calculate_profile_completed_percentage(auth()->user()->id, 'education'); //Education completes
            return $this->sendResponse([], 'Education details updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    /**
     * Post educational details.
    */
    public function postEducationalDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'qualification' => 'required|integer',
            'university' => 'required|integer',
            'course' => 'required|integer',
            'specialization' => 'required|integer',
            'course_type' => 'required|string',
            'course_start_year' => 'required|integer',
            'course_end_year' => 'required|integer',
            'grade' => 'required|string',
            // 'location' => 'required|integer',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            // UserEducation::where('user_id', auth()->user()->id)->delete();
            UserEducation::insert([
                'user_id'=> auth()->user()->id,
                'qualification_id'=> $request->qualification,
                'university_id'=> $request->university,
                'course_id'=> $request->course,
                'specialization_id'=> $request->specialization,
                'course_type'=> $request->course_type,
                'course_start_year'=> $request->course_start_year,
                'course_end_year'=> $request->course_end_year,
                'grade'=> $request->grade
                // 'location_id' => $request->location,
            ]);
            $this->calculate_profile_completed_percentage(auth()->user()->id, 'education'); //Education completes
            return $this->sendResponse([], 'Education details added successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

}
