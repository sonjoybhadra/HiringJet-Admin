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
     * Create a new job posting
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
                $data['position_name'] ?? ''
            );

            // Process location data (countries and cities)
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

            // Insert job into database using PostgreSQL
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
     *
     * @return array
     */
    private function generateJobNumber(): array
    {
        // Use PostgreSQL specific query to get latest job
        $lastJob = DB::table('post_jobs')
                    ->select('sl_no')
                    ->orderBy('id', 'DESC')
                    ->first();

        if ($lastJob) {
            $nextSlNo = $lastJob->sl_no + 1;
        } else {
            $nextSlNo = 1;
        }

        // Format serial number with leading zeros
        $formattedSlNo = str_pad($nextSlNo, 7, '0', STR_PAD_LEFT);
        $jobNo = 'HJ-J-' . $formattedSlNo;

        return [
            'sl_no' => $nextSlNo,
            'job_no' => $jobNo
        ];
    }

    /**
     * Get position name based on designation
     *
     * @param int $designationId
     * @param string $customPositionName
     * @return string
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

        return $designation ? $designation->name : '';
    }

    /**
     * Process location data (countries and cities)
     *
     * @param array $data
     * @return array
     */
    private function processLocationData(array $data): array
    {
        $locationData = [
            'countries_json' => '',
            'country_names_json' => '',
            'cities_json' => '',
            'city_names_json' => ''
        ];

        // Process countries
        if (!empty($data['location_countries']) && is_array($data['location_countries'])) {
            $countryIds = $data['location_countries'];
            $locationData['countries_json'] = json_encode($countryIds);

            // Get country names using PostgreSQL
            $countryNames = DB::table('countries')
                             ->select('name')
                             ->whereIn('id', $countryIds)
                             ->pluck('name')
                             ->toArray();

            $locationData['country_names_json'] = json_encode($countryNames);
        }

        // Process cities
        if (!empty($data['location_cities']) && is_array($data['location_cities'])) {
            $cityIds = $data['location_cities'];
            $locationData['cities_json'] = json_encode($cityIds);

            // Get city names using PostgreSQL
            $cityNames = DB::table('cities')
                          ->select('name')
                          ->whereIn('id', $cityIds)
                          ->pluck('name')
                          ->toArray();

            $locationData['city_names_json'] = json_encode($cityNames);
        }

        return $locationData;
    }

    /**
     * Process skills data
     *
     * @param array $data
     * @return array
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

            // Get skill names using PostgreSQL
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
     * Prepare job data for database insertion
     *
     * @param array $data
     * @param array $jobNumberData
     * @param string $positionName
     * @param array $locationData
     * @param array $skillData
     * @param int $userId
     * @return array
     */
    private function prepareJobDataForInsertion(
        array $data,
        array $jobNumberData,
        string $positionName,
        array $locationData,
        array $skillData,
        int $userId
    ): array {
        // Handle nationality - convert array to JSON if needed
        // $nationality = $data['nationality'] ?? '';
        // if (is_array($nationality)) {
        //     $nationality = json_encode($nationality);
        // }

        // Process salary fields - remove commas and convert to numeric
        $minSalary = 0;
        $maxSalary = 0;

        if (!empty($data['min_salary'])) {
            $minSalary = (float) str_replace(',', '', $data['min_salary']);
        }

        if (!empty($data['max_salary'])) {
            $maxSalary = (float) str_replace(',', '', $data['max_salary']);
        }

        // Prepare the final data array for PostgreSQL insertion
        return [
            'sl_no' => $jobNumberData['sl_no'],
            'job_no' => $jobNumberData['job_no'],
            'position_name' => $positionName,
            'employer_id' => (int) $data['employer_id'],
            'job_type' => (int) $data['job_type'],
            'location_countries' => $locationData['countries_json'],
            'location_country_names' => $locationData['country_names_json'],
            'location_cities' => $locationData['cities_json'],
            'location_city_names' => $locationData['city_names_json'],
            'industry' => (int) $data['industry'],
            'job_category' => (int) $data['job_category'],
            'nationality' => (int) $data['nationality'],
            'gender' => (int) $data['gender'],
            'open_position_number' => (int) $data['open_position_number'],
            'contract_type' => (int) $data['contract_type'],
            'designation' => (int) $data['designation'],
            'functional_area' => (int) ($data['functional_area'] ?? 0),
            'min_exp_year' => (int) $data['min_exp_year'],
            'max_exp_year' => isset($data['max_exp_year']) ? (int) $data['max_exp_year'] : null,
            'job_description' => $data['job_description'] ?? '',
            'requirement' => $data['requirement'] ?? '',
            'skill_ids' => $skillData['skill_ids_json'],
            'skill_names' => $skillData['skill_names_json'],
            'expected_close_date' => null,
            'currency' => $data['currency'] ?? '',
            'min_salary' => $minSalary,
            'max_salary' => $maxSalary,
            'is_salary_negotiable' => isset($data['is_salary_negotiable']) ? 1 : 0,
            'posting_open_date' => $data['posting_open_date'] ?? null,
            'posting_close_date' => $data['posting_close_date'] ?? null,
            'application_through' => (int) $data['application_through'],
            'apply_on_email' => $data['apply_on_email'] ?? '',
            'apply_on_link' => $data['apply_on_link'] ?? '',
            'walkin_address1' => $data['walkin_address1'] ?? '',
            'walkin_address2' => $data['walkin_address2'] ?? '',
            'walkin_country' => (int) ($data['walkin_country'] ?? 0),
            'walkin_state' => (int) ($data['walkin_state'] ?? 0),
            'walkin_city' => (int) ($data['walkin_city'] ?? 0),
            'walkin_pincode' => $data['walkin_pincode'] ?? '',
            'walkin_latitude' => $data['walkin_latitude'] ?? '',
            'walkin_longitude' => $data['walkin_longitude'] ?? '',
            'walkin_details' => isset($data['walkin_details'])
                ? html_entity_decode($data['walkin_details'])
                : '',
            'created_by' => $userId,
            'updated_by' => $userId,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ];
    }

    /**
     * Log user activity for audit trail
     *
     * @param string $userEmail
     * @param string $userName
     * @param string $ipAddress
     * @param string $positionName
     * @param string $action
     * @return void
     */
    private function logUserActivity(
        string $userEmail,
        string $userName,
        string $ipAddress,
        string $positionName,
        string $action
    ): void {
        try {
            DB::table('user_activities')->insert([
                'user_email' => $userEmail,
                'user_name' => $userName,
                'user_type' => 'EMPLOYER',
                'ip_address' => $ipAddress,
                'activity_type' => 3, // Job posting activity type
                'activity_details' => $positionName . ' Job ' . $action,
                'platform_type' => 'API',
                'created_at' => now()
            ]);
        } catch (Exception $e) {
            // Log the error but don't fail the main operation
            Log::warning('Failed to log user activity: ' . $e->getMessage());
        }
    }

    /**
     * Update existing job posting
     *
     * @param int $jobId
     * @param array $data
     * @param int $userId
     * @return array
     */
    public function updateJobPost(int $jobId, array $data, int $userId): array
    {
        try {
            DB::beginTransaction();

            // Check if job exists
            $existingJob = DB::table('post_jobs')
                            ->where('id', $jobId)
                            ->first();

            if (!$existingJob) {
                return [
                    'success' => false,
                    'message' => 'Job not found'
                ];
            }

            // Process position name
            $positionName = $this->getPositionName(
                $data['designation'],
                $data['position_name'] ?? ''
            );

            // Process location and skills data
            $locationData = $this->processLocationData($data);
            $skillData = $this->processSkillsData($data);

            // Prepare update data (exclude sl_no and job_no)
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

            // Update job in PostgreSQL
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
     *
     * @param int $jobId
     * @param int $userId
     * @return array
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
     *
     * @param int $jobId
     * @return array|null
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
     *
     * @param int $employerId
     * @param array $filters
     * @param int $perPage
     * @return array
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

            // Get total count for pagination
            $total = $query->count();

            // Get paginated results
            $jobs = $query->orderBy('created_at', 'desc')
                         ->offset(($filters['page'] ?? 1 - 1) * $perPage)
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
