<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_employments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->integer('total_experience_years')->nullable();
            $table->integer('total_experience_months')->nullable();
            $table->string('last_designation')->nullable();
            $table->foreignId('employer_id')->constrained('employers');
            $table->foreignId('country_id')->nullable()->constrained('countries');
            $table->foreignId('city_id')->nullable()->constrained('cities');
            $table->foreignId('currency_id')->constrained('countries');
            $table->float('current_salary', 15, 2)->nullable();
            $table->integer('working_since_from_year')->nullable();
            $table->integer('working_since_from_month')->nullable();
            $table->integer('working_since_to_year')->nullable();
            $table->integer('working_since_to_month')->nullable();

            $table->foreignId('work_level')->nullable()->constrained('current_work_levels');
            /* $table->foreignId('industry')->nullable()->constrained('industries');
            $table->foreignId('functional_area')->nullable()->constrained('functional_areas');
            $table->foreignId('perk_benefits')->nullable()->constrained('perk_benefits'); */

            $table->string('employment_type')->nullable();
            $table->integer('notice_period')->nullable()->constrained('availabilities');
            $table->boolean('is_current_job')->default(true);
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_employments');
    }
};
