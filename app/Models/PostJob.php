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
        'location_ids',
        'location_names',
        'open_position_number',
        'contract_type',
        'job_description',
        'requirement',
        'skill_ids',
        'skill_names',
        'experience_level',
        'expected_close_date',
        'currency',
        'min_salary',
        'max_salary',
        'is_salary_negotiable',
        'industry',
        'job_category',
        'department',
        'functional_area',
        'posting_open_date',
        'posting_close_date',
        'apply_on_email',
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
        'min_salary' => 'float',
        'max_salary' => 'float',
        'is_salary_negotiable' => 'boolean',
        'status' => 'boolean',
        'skill_ids' => 'array',
        'skill_names' => 'array',
        'location_ids' => 'array',
        'location_names' => 'array',
    ];

    // ========================
    // Relationships
    // ========================

    public function employer()
    {
        return $this->belongsTo(Employer::class);
    }

    public function contractType()
    {
        return $this->belongsTo(ContractType::class, 'contract_type');
    }

    public function experienceLevel()
    {
        return $this->belongsTo(ExperienceLevel::class, 'experience_level');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency');
    }

    public function industry()
    {
        return $this->belongsTo(Industry::class, 'industry');
    }

    public function jobCategory()
    {
        return $this->belongsTo(JobCategory::class, 'job_category');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department');
    }

    public function functionalArea()
    {
        return $this->belongsTo(FunctionalArea::class, 'functional_area');
    }
}
