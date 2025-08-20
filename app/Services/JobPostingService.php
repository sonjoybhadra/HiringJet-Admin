<?php

namespace App\Services;

use App\Models\PostJob;
use App\Models\Designation;
use App\Models\Country;
use App\Models\City;
use App\Models\Keyskill;
use App\Models\UserActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class JobPostingService
{
    /**
     * Create a new job posting - FIXED APPLICATION_THROUGH MAPPING
     *
     * @param array $data
     * @param int $userId
     * @param string $userEmail
     * @param string $userName
     * @param string $ipAddress
     * @return array
     */
    public function createJobPost(array $data, int $userId, string $userEmail, string $userName, string $ipAddress): array
    {
        try {
            DB::beginTransaction();

            // Generate job number
            $jobNumberData = $this->generateJobNumber();

            // Process position name based on designation
            $positionName = $this->getPositionName(
                $data['designation'],
                $data['roleName'] ?? $data['position_name'] ?? ''
            );

            // Process location data
            $locationData = $this->processLocationData($data);

            // Process skills data
            $skillData = $this->processSkillsData($data);

            // Prepare final job data for insertion
            $jobData = $this->prepareJobDataForInsertion(
                $data,
                $jobNumberData,
                $positionName,
                $locationData,
                $skillData,
                $userId
            );

            // Insert job into database
            $jobId = DB::table('post_jobs')->insertGetId($jobData);

            // Log user activity
            $this->logUserActivity(
                $userEmail,
                $userName,
                $ipAddress,
                $positionName,
                'Job Posted'
            );

            DB::commit();

            return [
                'success' => true,
                'message' => 'Job posted successfully',
                'job_id' => $jobId,
                'job_number' => $jobNumberData['job_no']
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Job posting failed: ' . $e->getMessage(), [
                'user_id' => $userId,
                'data' => $data
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create job posting',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate unique job number
     */
    private function generateJobNumber(): array
    {
        $lastJob = DB::table('post_jobs')
                    ->select('sl_no')
                    ->orderBy('id', 'DESC')
                    ->first();

        if ($lastJob) {
            $nextSlNo = $lastJob->sl_no + 1;
        } else {
            $nextSlNo = 1;
        }

        $formattedSlNo = str_pad($nextSlNo, 7, '0', STR_PAD_LEFT);
        $jobNo = 'HJ-J-' . $formattedSlNo;

        return [
            'sl_no' => $nextSlNo,
            'job_no' => $jobNo
        ];
    }

    /**
     * Get position name based on designation
     */
    private function getPositionName(int $designationId, string $customPositionName = ''): string
    {
        // If designation is 7037 (Others/Custom), use custom position name
        if ($designationId == 7037) {
            return trim(strip_tags($customPositionName));
        }

        // Otherwise get designation name from database
        $designation = DB::table('designations')
                        ->select('name')
                        ->where('id', $designationId)
                        ->first();

        return $customPositionName ? $customPositionName : ($designation ? $designation->name : '');
    }

    /**
     * Process location data
     */
    private function processLocationData(array $data): array
    {
        $locationData = [
            'countries_json' => '',
            'country_names_json' => '',
            'cities_json' => '',
            'city_names_json' => ''
        ];

        // Process countries - handle single value or array
        $countries = [];
        if (!empty($data['location_countries'])) {
            $countries = is_array($data['location_countries']) ? $data['location_countries'] : [$data['location_countries']];
            $locationData['countries_json'] = json_encode($countries);

            // Get country names
            $countryNames = DB::table('countries')
                             ->select('name')
                             ->whereIn('id', $countries)
                             ->pluck('name')
                             ->toArray();

            $locationData['country_names_json'] = json_encode($countryNames);
        }

        // Process cities - handle single value or array
        $cities = [];
        if (!empty($data['location_cities'])) {
            $cities = is_array($data['location_cities']) ? $data['location_cities'] : [$data['location_cities']];
            $locationData['cities_json'] = json_encode($cities);

            // Get city names
            $cityNames = DB::table('cities')
                          ->select('name')
                          ->whereIn('id', $cities)
                          ->pluck('name')
                          ->toArray();

            $locationData['city_names_json'] = json_encode($cityNames);
        }

        return $locationData;
    }

    /**
     * Process skills data
     */
    private function processSkillsData(array $data): array
    {
        $skillData = [
            'skill_ids_json' => '',
            'skill_names_json' => ''
        ];

        if (!empty($data['skill_ids']) && is_array($data['skill_ids'])) {
            $skillIds = $data['skill_ids'];
            $skillData['skill_ids_json'] = json_encode($skillIds);

            // Get skill names
            $skillNames = DB::table('keyskills')
                           ->select('name')
                           ->whereIn('id', $skillIds)
                           ->pluck('name')
                           ->toArray();

            $skillData['skill_names_json'] = json_encode($skillNames);
        }

        return $skillData;
    }

    /**
     * Prepare job data for database insertion - FIXED APPLICATION_THROUGH
     */
    private function prepareJobDataForInsertion(
        array $data,
        array $jobNumberData,
        string $positionName,
        array $locationData,
        array $skillData,
        int $userId
    ): array {
        // Handle nationality - keep as is or convert to JSON if array
        $nationality = $data['nationality'] ?? '';
        if (is_array($nationality)) {
            $nationality = json_encode($nationality);
        }

        // Process salary fields
        $minSalary = 0;
        $maxSalary = 0;

        if (!empty($data['min_salary'])) {
            $minSalary = (float) str_replace(',', '', $data['min_salary']);
        }

        if (!empty($data['max_salary'])) {
            $maxSalary = (float) str_replace(',', '', $data['max_salary']);
        }

        // Handle job type
        $jobType = $data['job_type'] ?? 'walk-in-jobs';

        // Handle gender
        $gender = $data['gender'] ?? 'No Preference';

        // Handle contract type - convert to integer as expected by database
        $contractType = (int) ($data['contract_type'] ?? 1);

        // FIXED: Handle application_through - keep as STRING, don't convert to integer
        $applicationThrough = $data['application_through'] ?? 'Hiring Jet';

        // Ensure it matches the database constraint values
        $validApplicationMethods = ['Hiring Jet', 'Apply To Email', 'Apply To Link'];
        if (!in_array($applicationThrough, $validApplicationMethods)) {
            // Default to 'Hiring Jet' if invalid value
            $applicationThrough = 'Hiring Jet';
        }

        // Handle currency - use directly from request data
        $currency = $data['currency'] ?? '';

        // Handle walk-in location data
        $walkinCountry = '';
        $walkinState = '';
        $walkinCity = '';

        if ($jobType === 'walk-in-jobs') {
            $walkinCountry = $data['walkin_country'] ?? NULL;
            $walkinState = $data['walkin_state'] ?? NULL;
            $walkinCity = $data['walkin_city'] ?? NULL;
        }

        // Prepare the final data array
        return [
            'sl_no' => $jobNumberData['sl_no'],
            'job_no' => $jobNumberData['job_no'],
            'position_name' => $positionName,
            'employer_id' => (int) $data['employer_id'],
            'job_type' => $jobType,
            'location_countries' => $locationData['countries_json'],
            'location_country_names' => $locationData['country_names_json'],
            'location_cities' => $locationData['cities_json'],
            'location_city_names' => $locationData['city_names_json'],
            'industry' => (int) ($data['industry'] ?? 1),
            'job_category' => (int) ($data['job_category'] ?? 1),
            'nationality' => $nationality,
            'gender' => $gender,
            'open_position_number' => (int) ($data['open_position_number'] ?? 1),
            'contract_type' => $contractType,
            'designation' => (int) $data['designation'],
            'functional_area' => (int) ($data['functional_area'] ?? 1),
            'min_exp_year' => (int) ($data['min_exp_year'] ?? 0),
            'max_exp_year' => (int) ($data['max_exp_year'] ?? 0),
            'job_description' => $data['job_description'] ?? NULL,
            'requirement' => $data['requirement'] ?? NULL,
            'skill_ids' => $skillData['skill_ids_json'],
            'skill_names' => $skillData['skill_names_json'],
            'expected_close_date' => $data['expected_close_date'] ?: NULL,
            'currency' => $currency,
            'min_salary' => $minSalary,
            'max_salary' => $maxSalary,
            'is_salary_negotiable' => ($data['is_salary_negotiable'] ?? false) ? 1 : 0,
            'posting_open_date' => $data['posting_open_date'] ?? date('Y-m-d'),
            'posting_close_date' => $data['posting_close_date'] ?? date('Y-m-d', strtotime('+30 days')),
            'application_through' => $applicationThrough, // Keep as STRING
            'apply_on_email' => $data['apply_on_email'] ?? NULL,
            'apply_on_link' => $data['apply_on_link'] ?? NULL,
            'walkin_address1' => $data['walkin_address1'] ?? NULL,
            'walkin_address2' => $data['walkin_address2'] ?? NULL,
            'walkin_country' => $walkinCountry,
            'walkin_state' => $walkinState,
            'walkin_city' => $walkinCity,
            'walkin_pincode' => $data['walkin_pincode'] ?? NULL,
            'walkin_latitude' => $data['walkin_latitude'] ?? null,
            'walkin_longitude' => $data['walkin_longitude'] ?? null,
            'walkin_details' => $data['walkin_details'] ?? NULL,
            'created_by' => $userId,
            'updated_by' => $userId,
            'status' => 0, // 0 = pending , 1 = active , 2 = rejected , 3 = deleted
            'created_at' => now(),
            'updated_at' => now()
        ];
    }

    /**
     * Get location ID by name - SAFE FALLBACK
     */
    private function getLocationId($type, $name, $countryId = null)
    {
        if (empty($name)) {
            return 0;
        }

        try {
            switch ($type) {
                case 'country':
                    $location = DB::table('countries')
                                ->where('name', 'ILIKE', $name)
                                ->first();
                    break;

                case 'state':
                    $query = DB::table('states')
                           ->where('name', 'ILIKE', $name);
                    if ($countryId) {
                        $query->where('country_id', $countryId);
                    }
                    $location = $query->first();
                    break;

                case 'city':
                    $query = DB::table('cities')
                           ->where('name', 'ILIKE', $name);
                    if ($countryId) {
                        $query->where('country_id', $countryId);
                    }
                    $location = $query->first();
                    break;

                default:
                    return 0;
            }

            return $location ? $location->id : 0;

        } catch (Exception $e) {
            Log::error("Error getting {$type} ID: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Log user activity for audit trail
     */
    private function logUserActivity(
        string $userEmail,
        string $userName,
        string $ipAddress,
        string $positionName,
        string $action
    ): void {
        //try {
            DB::table('user_activities')->insert([
                'user_email' => $userEmail,
                'user_name' => $userName,
                'user_type' => 'ADMIN',
                'ip_address' => $ipAddress,
                'activity_type' => 3, // Job posting activity type
                'activity_details' => $positionName . ' Job ' . $action,
                'platform_type' => 'WEB',
                'created_at' => now()
            ]);
        // } catch (Exception $e) {
        //     Log::warning('Failed to log user activity: ' . $e->getMessage());
        // }
    }

    /**
     * Update existing job posting
     */
    public function updateJobPost(int $jobId, array $data, int $userId): array
    {
        try {
            DB::beginTransaction();

            $existingJob = DB::table('post_jobs')
                            ->where('id', $jobId)
                            ->first();

            if (!$existingJob) {
                return [
                    'success' => false,
                    'message' => 'Job not found'
                ];
            }

            $positionName = $this->getPositionName(
                $data['designation'],
                $data['position_name'] ?? ''
            );

            $locationData = $this->processLocationData($data);
            $skillData = $this->processSkillsData($data);

            $updateData = $this->prepareJobDataForInsertion(
                $data,
                ['sl_no' => $existingJob->sl_no, 'job_no' => $existingJob->job_no],
                $positionName,
                $locationData,
                $skillData,
                $userId
            );

            // Remove fields that shouldn't be updated
            unset($updateData['sl_no'], $updateData['job_no'], $updateData['created_by'], $updateData['created_at']);
            $updateData['updated_at'] = now();

            DB::table('post_jobs')
              ->where('id', $jobId)
              ->update($updateData);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Job updated successfully',
                'job_id' => $jobId
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Job update failed: ' . $e->getMessage(), [
                'job_id' => $jobId,
                'user_id' => $userId
            ]);

            return [
                'success' => false,
                'message' => 'Failed to update job posting',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete job posting (soft delete)
     */
    public function deleteJobPost(int $jobId, int $userId): array
    {
        try {
            $updated = DB::table('post_jobs')
                        ->where('id', $jobId)
                        ->update([
                            'status' => 3, // Deleted status
                            'deleted_at' => now(),
                            'updated_by' => $userId,
                            'updated_at' => now()
                        ]);

            if ($updated) {
                return [
                    'success' => true,
                    'message' => 'Job deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Job not found'
                ];
            }

        } catch (Exception $e) {
            Log::error('Job deletion failed: ' . $e->getMessage(), [
                'job_id' => $jobId,
                'user_id' => $userId
            ]);

            return [
                'success' => false,
                'message' => 'Failed to delete job posting',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get job by ID
     */
    public function getJobById(int $jobId): ?array
    {
        try {
            $job = DB::table('post_jobs')
                    ->where('id', $jobId)
                    ->where('status', '!=', 3) // Not deleted
                    ->first();

            return $job ? (array) $job : null;

        } catch (Exception $e) {
            Log::error('Failed to fetch job: ' . $e->getMessage(), ['job_id' => $jobId]);
            return null;
        }
    }

    /**
     * Get jobs by employer with filters
     */
    public function getEmployerJobs(int $employerId, array $filters = [], int $perPage = 15): array
    {
        try {
            $query = DB::table('post_jobs')
                      ->where('employer_id', $employerId)
                      ->where('status', '!=', 3); // Not deleted

            // Apply filters
            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (!empty($filters['job_type'])) {
                $query->where('job_type', $filters['job_type']);
            }

            if (!empty($filters['date_from'])) {
                $query->whereDate('created_at', '>=', $filters['date_from']);
            }

            if (!empty($filters['date_to'])) {
                $query->whereDate('created_at', '<=', $filters['date_to']);
            }

            // Get total count
            $total = $query->count();

            // Get paginated results
            $jobs = $query->orderBy('created_at', 'desc')
                         ->offset((($filters['page'] ?? 1) - 1) * $perPage)
                         ->limit($perPage)
                         ->get();

            return [
                'success' => true,
                'jobs' => $jobs->toArray(),
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $filters['page'] ?? 1
            ];

        } catch (Exception $e) {
            Log::error('Failed to fetch employer jobs: ' . $e->getMessage(), [
                'employer_id' => $employerId,
                'filters' => $filters
            ]);

            return [
                'success' => false,
                'message' => 'Failed to fetch jobs',
                'error' => $e->getMessage()
            ];
        }
    }
}
