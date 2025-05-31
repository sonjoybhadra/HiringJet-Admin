<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserEducation extends Model
{
    protected $table = 'user_educations';

    protected $fillable = ['user_id', 'qualification_id', 'course_id', 'specialization_id', 'location_id', 'university_id', 'passing_year', 'course_start_year', 'course_end_year', 'course_type', 'percentage', 'grade', 'is_active'];

    /**
     * Get the qualification details of associated user.
    */
    public function qualification(): BelongsTo
    {
        return $this->BelongsTo(Qualification::class, 'qualification_id');
    }

    public function course(): BelongsTo
    {
        return $this->BelongsTo(Course::class, 'course_id');
    }

    public function location(): BelongsTo
    {
        return $this->BelongsTo(City::class, 'location_id');
    }

    public function university(): BelongsTo
    {
        return $this->BelongsTo(University::class, 'university_id');
    }

    public function specialization(): BelongsTo
    {
        return $this->BelongsTo(Specialization::class, 'specialization_id');
    }

}
