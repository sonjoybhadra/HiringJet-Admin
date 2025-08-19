<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

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
  
}
