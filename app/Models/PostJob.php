<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    public function departmentRelation()
    {
        return $this->belongsTo(Department::class, 'department');
    }

    public function functionalArea()
    {
        return $this->belongsTo(FunctionalArea::class, 'functional_area');
    }

    public function experienceLevel()
    {
        return $this->belongsTo(CurrentWorkLevel::class, 'experience_level');
    }
}
