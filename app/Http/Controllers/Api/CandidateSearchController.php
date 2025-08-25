<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

use App\Models\User;
use App\Models\UserEmployment;
use App\Models\Designation;
use App\Models\ShortlistedJob;
use App\Models\PostJobUserApplied;
use App\Models\PostJob;
use App\Models\ProfileComplete;
use App\Models\UserProfile;
use App\Models\UserProfileCompletedPercentage;

class CandidateSearchController extends BaseApiController{
 
  public function searchCandidates(Request $request){
    $designationIds = json_decode($request->included_designation, true) ?? [];
    $excludedDesignationIds = json_decode($request->excluded_designation, true) ?? [];
    $industryIds    = json_decode($request->included_industry, true) ?? [];
    $excludedIndustryIds = json_decode($request->excluded_industry, true) ?? [];
    $nationalityIds = json_decode($request->nationality_id, true) ?? [];
    $gender         = $request->gender ?? null;
    $minExperience  = $request->min_experience ?? null;
    $maxExperience  = $request->max_experience ?? null;

    try {
      $result = DB::table('users')
        ->join('user_employments', 'users.id', '=', 'user_employments.user_id')
        ->join('employers', 'user_employments.employer_id', '=', 'employers.id')
        ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
        ->leftJoin('user_employments as current_employment', function ($join) {
          $join->on('users.id', '=', 'current_employment.user_id')
          ->where('current_employment.is_current_job', 1);
        })
        ->leftJoin('designations', DB::raw("NULLIF(current_employment.last_designation, '')::BIGINT"), '=', 'designations.id')
        ->leftJoin('employers as current_employer', 'current_employment.employer_id', '=', 'current_employer.id')
        ->leftJoin('user_skills', 'users.id', '=', 'user_skills.user_id')
        ->leftJoin('keyskills', 'user_skills.keyskill_id', '=', 'keyskills.id')
        ->leftJoin('countries', 'user_profiles.country_id', '=', 'countries.id')
        ->leftJoin('cities', 'user_profiles.city_id', '=', 'cities.id')
        ->leftJoin('countries as currencies', 'current_employment.currency_id', '=', 'currencies.id')
        ->leftJoin('nationalities', DB::raw("user_profiles.nationality_id::BIGINT"), '=', 'nationalities.id')
        ->where('users.role_id', 3)
        ->when(!empty($designationIds), function ($q) use ($designationIds) {
          return $q->whereIn(DB::raw("NULLIF(user_employments.last_designation, '')::BIGINT"), $designationIds);
        })
        ->when(!empty($excludedDesignationIds), function ($q) use ($excludedDesignationIds) {
          return $q->whereNotIn(DB::raw("NULLIF(user_employments.last_designation, '')::BIGINT"), $excludedDesignationIds);
        })
        ->when(!empty($industryIds), function ($q) use ($industryIds) {
          return $q->whereIn('employers.industry_id', $industryIds);
        })
        ->when(!empty($excludedIndustryIds), function ($q) use ($excludedIndustryIds) {
          return $q->whereNotIn('employers.industry_id', $excludedIndustryIds);
        })
        ->when(!empty($nationalityIds), function ($q) use ($nationalityIds) {
          return $q->whereIn(DB::raw("user_profiles.nationality_id::BIGINT"), $nationalityIds);
        })
        ->when(strtolower($gender) !== 'any', function ($q) use ($gender) {
          return $q->whereRaw('LOWER(user_profiles.gender) = ?', [strtolower($gender)]);
        })
        ->when(is_numeric($minExperience), function ($q) use ($minExperience) {
          return $q->where('current_employment.total_experience_years', '>=', $minExperience);
        })
        ->when(is_numeric($maxExperience), function ($q) use ($maxExperience) {
          return $q->where('current_employment.total_experience_years', '<=', $maxExperience);
        })
        ->select(
          'users.id',
          'user_profiles.first_name',
          'user_profiles.last_name',
          'user_profiles.profile_image',
          'user_profiles.gender',
          'user_profiles.resume_headline',
          'current_employment.total_experience_years',
          'current_employment.total_experience_months',
          'current_employment.current_salary',
          'designations.name as current_designation_name',
          'current_employer.name as current_employer_name',
          DB::raw("COALESCE(STRING_AGG(DISTINCT keyskills.name, ', '), '') as skill_names"),
          'countries.name as country_name',
          'cities.name as city_name',
          'currencies.currency_code as currency_code',
          'nationalities.name as nationality_name'
        )
        ->groupBy(
          'users.id',
          'user_profiles.first_name',
          'user_profiles.last_name',
          'user_profiles.profile_image',
          'user_profiles.gender',
          'user_profiles.resume_headline',
          'current_employment.total_experience_years',
          'current_employment.total_experience_months',
          'current_employment.current_salary',
          'designations.name',
          'current_employer.name',
          'countries.name',
          'cities.name',
          'currencies.currency_code',
          'nationalities.name'
        )
        ->distinct()
        ->get();

        return $this->sendResponse($result, 'Test');
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
  }

  public function previewCV($id)
    {
      $data = User::where('id', $id)
      ->with('user_profile')
      ->with('user_skills')
      ->with('user_employments')
      ->with('user_education')
      // ->with('user_profile_completed_percentages')
      ->with('user_languages')
      ->with('user_certification')
      ->with('user_online_profile')
      ->with('user_work_sample')
      ->with('user_it_skill')
      ->with('user_cv')
      ->first();

$profileComplete = ProfileComplete::select('id', 'name', 'percentage')->get()->toArray();
$profile_completed_percentages = [];
$total_completed_percentage = 0;
foreach($profileComplete as $value){
  $has_user_data = UserProfileCompletedPercentage::where('user_id', $id)
                                                  ->where('profile_completes_id', $value['id'])
                                                  ->first();
  $value['completed_percentage'] = $has_user_data ? (int)$value['percentage'] : 0;
  $value['has_completed'] = $has_user_data ? 1 : 0;
  if($has_user_data){
      $total_completed_percentage += (int)$value['percentage'];
  }

  array_push($profile_completed_percentages, $value);
}
$data->user_profile->profile_completed_percentage = $total_completed_percentage;

UserProfile::where('user_id', $id)
          ->where('profile_completed_percentage', '!=', $total_completed_percentage)
          ->update(['profile_completed_percentage'=> $total_completed_percentage]);

$data->user_profile_completed_percentages = $profile_completed_percentages;


$user_employment = UserEmployment::where('user_id', $id)
                                  ->where('is_current_job', 1)
                                  ->with('employer')
                                  ->first();
if(!$user_employment){
  $user_employment = UserEmployment::where('user_id', $id)
                                  ->latest()
                                  ->with('employer')
                                  ->first();
}
$data->current_designation = $user_employment ? Designation::find($user_employment->last_designation) : [];
$data->current_company = $user_employment ? $user_employment->employer : [];
$data->shortlisted_jobs_count = ShortlistedJob::where('user_id', $id)->count();
$data->applied_jobs_count = PostJobUserApplied::where('user_id', $id)->count();
$data->job_alerts_count = 0;

        try {
            return $this->sendResponse(
                $data,
                'User Details'
            );
        } catch (\Exception $e) {
            return $this->sendError('Error', 'Sorry, something went wrong, unable to fetch user details.',  Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
  
}
