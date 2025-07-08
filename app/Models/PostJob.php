<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PostJob extends Model
{
    use SoftDeletes;

    protected $table = 'post_jobs';

    protected $fillable = [
        'sl_no',
        'job_no',
        'position_name',
        'employer_id',
        'job_type',
        'location_countries',
        'location_country_names',
        'location_cities',
        'location_city_names',
        'industry',
        'job_category',
        'nationality',
        'gender',
        'open_position_number',
        'contract_type',
        'job_description',
        'requirement',
        'department',
        'functional_area',
        'skill_ids',
        'skill_names',
        'experience_level',
        'expected_close_date',
        'currency',
        'min_salary',
        'max_salary',
        'is_salary_negotiable',
        'posting_open_date',
        'posting_close_date',
        'apply_on_email',
        'application_through',
        'apply_on_link',
        'walkin_address1',
        'walkin_address2',
        'walkin_country',
        'walkin_state',
        'walkin_city',
        'walkin_pincode',
        'walkin_latitude',
        'walkin_longitude',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'expected_close_date' => 'date',
        'posting_open_date' => 'date',
        'posting_close_date' => 'date',
        'min_salary' => 'float',
        'max_salary' => 'float',
        'is_salary_negotiable' => 'boolean',
        'status' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function employer()
    {
        return $this->belongsTo(Employer::class);
    }

    public function industryRelation()
    {
        return $this->belongsTo(Industry::class, 'industry');
    }

    public function jobCategory()
    {
        return $this->belongsTo(JobCategory::class, 'job_category');
    }

    public function nationalityRelation()
    {
        return $this->belongsTo(Nationality::class, 'nationality');
    }

    public function contractType()
    {
        return $this->belongsTo(ContractType::class, 'contract_type');
    }

    public function designationRelation()
    {
        return $this->belongsTo(Designation::class, 'designation');
    }

    public function functionalArea()
    {
        return $this->belongsTo(FunctionalArea::class, 'functional_area');
    }

    // public function experienceLevel()
    // {
    //     return $this->belongsTo(CurrentWorkLevel::class, 'experience_level');
    // }

    public function applied_users()
    {
        return $this->belongsToMany(User::class, 'post_job_user_applieds', 'job_id', 'user_id');
    }

    public function get_job_search_custom_sql(){
        $jobseeker_designation = UserEmployment::select('last_designation')
                                                    ->where('user_id', auth()->user()->id)
                                                    ->orderBy('is_current_job', 'DESC')
                                                    ->first();

        $user_skills = UserSkill::where('user_id', auth()->user()->id)->get()->pluck('keyskill_id')->toArray();

        $sql = PostJob::select('post_jobs.*');
        $sql->addSelect(DB::raw('(SELECT COUNT(*) FROM post_job_user_applieds WHERE post_job_user_applieds.user_id = '.auth()->user()->id.' and post_job_user_applieds.job_id = post_jobs.id and post_job_user_applieds.status=1) AS job_applied_status'));
        $sql->addSelect(DB::raw('(SELECT COUNT(*) FROM shortlisted_jobs WHERE shortlisted_jobs.user_id = '.auth()->user()->id.' and shortlisted_jobs.job_id = post_jobs.id and shortlisted_jobs.status=1) AS job_shortlisted_status'));

        if($jobseeker_designation){
            $sql->where('designation', $jobseeker_designation->last_designation);
        }
        if(!empty($user_skills)){
            $sql->where(function ($q) use ($user_skills) {
                foreach ($user_skills as $tag) {
                    // $q->orWhereJsonContains('skill_ids', (string)$tag);
                    // $q->orwhereRaw("skill_ids::jsonb @> ?::jsonb", [json_encode($tag)]);
                    $q->orWhereRaw("CAST(skill_ids AS jsonb) @> ?", [json_encode([(string)$tag])])->get();
                }
            });

            // $jobs = PostJob::whereRaw("?::jsonb @> to_jsonb(?)", ['locations', $searchLocation])->get();
            // // Or more explicitly checking for string containment within the array
            // $jobs = PostJob::whereRaw("locations::jsonb @> ?::jsonb", [json_encode([$searchLocation])])->get();
        }

        return $sql;
    }

}
