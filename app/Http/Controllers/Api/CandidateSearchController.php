<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
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
    $designationIds         = json_decode($request->included_designation, true) ?? [];
    $excludedDesignationIds = json_decode($request->excluded_designation, true) ?? [];
    $industryIds            = json_decode($request->included_industry, true) ?? [];
    $excludedIndustryIds    = json_decode($request->excluded_industry, true) ?? [];
    $nationalityIds         = json_decode($request->nationality_id, true) ?? [];
    $gender                 = $request->gender ?? null;
    $minExperience          = $request->min_experience ?? null;
    $maxExperience          = $request->max_experience ?? null;
    $employerId             = $request->employer_id;

    $salaryCurrencyId       = $request->salaryCurrencyId ?? null;
    $minSalary              = $request->minSalary ?? null;
    $maxSalary              = $request->maxSalary ?? null;
    $noticePeriod           = $request->noticePeriod ?? null;

    // boolean search queries
    $keywordQuery           = $request->keyword_query ?? null;
    $designationQuery       = $request->designation_query ?? null;
    $employerQuery          = $request->employer_query ?? null;

    try {
        $result = DB::table('users')
            ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            ->leftJoin('user_employments as current_employment', function ($join) {
                $join->on('users.id', '=', 'current_employment.user_id')
                     ->where('current_employment.is_current_job', 1)
                     ->whereNull('current_employment.deleted_at');
            })
            ->leftJoin(
                'designations',
                DB::raw("NULLIF(current_employment.last_designation, '')::BIGINT"),
                '=',
                'designations.id'
            )
            ->leftJoin('employers as current_employer', 'current_employment.employer_id', '=', 'current_employer.id')
            ->leftJoin(
                'industries',
                DB::raw("NULLIF(current_employer.industry_id, '')::BIGINT"),
                '=',
                'industries.id'
            )
            ->leftJoin('countries', 'user_profiles.country_id', '=', 'countries.id')
            ->leftJoin('cities', 'user_profiles.city_id', '=', 'cities.id')
            ->leftJoin('countries as currencies', 'current_employment.currency_id', '=', 'currencies.id')
            ->leftJoin(
                'nationalities',
                DB::raw("user_profiles.nationality_id::BIGINT"),
                '=',
                'nationalities.id'
            )
            ->leftJoin(DB::raw("(
                SELECT us.user_id, STRING_AGG(DISTINCT ks.name, ', ') AS skills
                FROM user_skills us
                JOIN keyskills ks ON us.keyskill_id = ks.id
                GROUP BY us.user_id
            ) as skillset"), 'users.id', '=', 'skillset.user_id')
            ->leftJoin(DB::raw("(
                SELECT ue.user_id,
                json_agg(DISTINCT jsonb_build_object('id', d.id, 'name', d.name))::jsonb as designations
                FROM user_employments ue
                JOIN designations d ON NULLIF(ue.last_designation, '')::BIGINT = d.id
                WHERE ue.deleted_at IS NULL
                GROUP BY ue.user_id
            ) as desigset"), 'users.id', '=', 'desigset.user_id')
            ->leftJoin(DB::raw("(
                SELECT ue.user_id,
                json_agg(DISTINCT jsonb_build_object('id', i.id, 'name', i.name))::jsonb as industries
                FROM user_employments ue
                JOIN employers e ON ue.employer_id = e.id
                JOIN industries i ON NULLIF(e.industry_id, '')::BIGINT = i.id
                WHERE ue.deleted_at IS NULL
                GROUP BY ue.user_id
            ) as indset"), 'users.id', '=', 'indset.user_id')
            ->where('users.role_id', 3)
            ->when(isset($keywordQuery) && $keywordQuery !== '', function($q) use ($keywordQuery) {
                preg_match_all('/"([^"]+)"|\S+/', $keywordQuery, $matches);
                $tokens = $matches[0];

                $q->where(function($outer) use ($tokens) {
                    $currentOp = 'and';
                    foreach ($tokens as $token) {
                        $upper = strtoupper($token);

                        if (in_array($upper, ['AND','OR','NOT'])) {
                            $currentOp = strtolower($upper);
                        } else {
                            $term = trim($token, '"');

                            if ($currentOp === 'not') {
                                $outer->whereNot(function($inner) use ($term) {
                                    $inner->orWhere('user_profiles.resume_headline', 'ILIKE', "%$term%")
                                          ->orWhere('skillset.skills', 'ILIKE', "%$term%")
                                          ->orWhere('designations.name', 'ILIKE', "%$term%")
                                          ->orWhere('industries.name', 'ILIKE', "%$term%");
                                });
                            } elseif ($currentOp === 'or') {
                                $outer->orWhere(function($inner) use ($term) {
                                    $inner->orWhere('user_profiles.resume_headline', 'ILIKE', "%$term%")
                                          ->orWhere('skillset.skills', 'ILIKE', "%$term%")
                                          ->orWhere('designations.name', 'ILIKE', "%$term%")
                                          ->orWhere('industries.name', 'ILIKE', "%$term%");
                                });
                            } else {
                                $outer->where(function($inner) use ($term) {
                                    $inner->orWhere('user_profiles.resume_headline', 'ILIKE', "%$term%")
                                          ->orWhere('skillset.skills', 'ILIKE', "%$term%")
                                          ->orWhere('designations.name', 'ILIKE', "%$term%")
                                          ->orWhere('industries.name', 'ILIKE', "%$term%");
                                });
                            }

                            $currentOp = 'and';
                        }
                    }
                });
            })

            ->when(isset($designationQuery) && $designationQuery !== '', function($q) use ($designationQuery) {
                preg_match_all('/"([^"]+)"|\S+/', $designationQuery, $matches);
                $tokens = $matches[0];

                $q->where(function($outer) use ($tokens) {
                    $currentOp = 'and';
                    foreach ($tokens as $token) {
                        $upper = strtoupper($token);

                        if (in_array($upper, ['AND','OR','NOT'])) {
                            $currentOp = strtolower($upper);
                        } else {
                            $term = trim($token, '"');

                            if ($currentOp === 'not') {
                                $outer->whereNot('designations.name', 'ILIKE', "%$term%");
                            } elseif ($currentOp === 'or') {
                                $outer->orWhere('designations.name', 'ILIKE', "%$term%");
                            } else {
                                $outer->where('designations.name', 'ILIKE', "%$term%");
                            }

                            $currentOp = 'and';
                        }
                    }
                });
            })

            ->when(isset($employerQuery) && $employerQuery !== '', function($q) use ($employerQuery) {
                preg_match_all('/"([^"]+)"|\S+/', $employerQuery, $matches);
                $tokens = $matches[0];

                $q->where(function($outer) use ($tokens) {
                    $currentOp = 'and';
                    foreach ($tokens as $token) {
                        $upper = strtoupper($token);

                        if (in_array($upper, ['AND','OR','NOT'])) {
                            $currentOp = strtolower($upper);
                        } else {
                            $term = trim($token, '"');

                            if ($currentOp === 'not') {
                                $outer->whereNot('current_employer.name', 'ILIKE', "%$term%");
                            } elseif ($currentOp === 'or') {
                                $outer->orWhere('current_employer.name', 'ILIKE', "%$term%");
                            } else {
                                $outer->where('current_employer.name', 'ILIKE', "%$term%");
                            }

                            $currentOp = 'and';
                        }
                    }
                });
            })

            ->when(!empty($designationIds), function ($q) use ($designationIds) {
                return $q->whereIn(DB::raw("NULLIF(current_employment.last_designation, '')::BIGINT"), $designationIds);
            })
            ->when(!empty($excludedDesignationIds), function ($q) use ($excludedDesignationIds) {
                return $q->whereNotIn(DB::raw("NULLIF(current_employment.last_designation, '')::BIGINT"), $excludedDesignationIds);
            })
            ->when(!empty($industryIds), function ($q) use ($industryIds) {
                return $q->whereIn(DB::raw("NULLIF(current_employer.industry_id, '')::BIGINT"), $industryIds);
            })
            ->when(!empty($excludedIndustryIds), function ($q) use ($excludedIndustryIds) {
                return $q->whereNotIn(DB::raw("NULLIF(current_employer.industry_id, '')::BIGINT"), $excludedIndustryIds);
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
            ->when(!empty($salaryCurrencyId), function ($q) use ($salaryCurrencyId) {
                return $q->where('current_employment.currency_id', $salaryCurrencyId);
            })
            ->when(is_numeric($minSalary), function ($q) use ($minSalary) {
                return $q->where('current_employment.current_salary', '>=', $minSalary);
            })
            ->when(is_numeric($maxSalary), function ($q) use ($maxSalary) {
                return $q->where('current_employment.current_salary', '<=', $maxSalary);
            })
            ->when(is_numeric($noticePeriod) && (int)$noticePeriod > 0, function ($q) use ($noticePeriod) {
                return $q->where('current_employment.notice_period', (int)$noticePeriod);
            })

            ->select(
                'users.id',
                'user_profiles.first_name',
                'user_profiles.last_name',
                'user_profiles.profile_image',
                'user_profiles.gender',
                'user_profiles.updated_at',
                'user_profiles.resume_headline',
                'current_employment.total_experience_years',
                'current_employment.total_experience_months',
                'current_employment.current_salary',
                'current_employment.notice_period',
                'designations.name as current_designation_name',
                'current_employer.name as current_employer_name',
                'industries.name as industry_name',
                'countries.name as country_name',
                'cities.name as city_name',
                'currencies.currency_code as currency_code',
                'nationalities.name as nationality_name',
                'skillset.skills as skill_names',
                'desigset.designations',
                'indset.industries'
            )
            ->distinct()
            ->get();

        return $this->sendResponse($result, 'Candidates fetched successfully');
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
