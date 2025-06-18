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
        Schema::create('post_jobs', function (Blueprint $table) {
            $table->id();
            $table->integer('sl_no')->default(0);
            $table->string('job_no')->nullable();
            $table->longText('position_name')->nullable();
            $table->foreignId('employer_id')->constrained('employers')->onDelete('cascade');
            $table->enum('job_type', ['walk-in-jobs', 'remote-jobs', 'on-site-jobs', 'temp-role-jobs'])->nullable();
            $table->longText('location_countries');
            $table->longText('location_country_names');
            $table->longText('location_cities');
            $table->longText('location_city_names');
            $table->foreignId('industry')->nullable()->constrained('industries')->onDelete('cascade');
            $table->foreignId('job_category')->nullable()->constrained('job_categories')->onDelete('cascade');
            $table->foreignId('nationality')->nullable()->constrained('nationalities')->onDelete('cascade');
            $table->enum('gender', ['Male', 'Female', 'Transgender', 'No Preference'])->nullable();
            $table->integer('open_position_number')->default(0);
            $table->foreignId('contract_type')->nullable()->constrained('contract_types')->onDelete('cascade');
            $table->foreignId('designation')->nullable()->constrained('designations')->onDelete('cascade');
            $table->foreignId('functional_area')->nullable()->constrained('functional_areas')->onDelete('cascade');
            $table->integer('min_exp_year')->nullable();
            $table->integer('max_exp_year')->nullable();
            $table->longText('job_description')->nullable();
            $table->longText('requirement')->nullable();
            $table->longText('skill_ids');
            $table->longText('skill_names');
            // $table->foreignId('experience_level')->nullable()->constrained('current_work_levels')->onDelete('cascade');
            $table->date('expected_close_date')->nullable();
            $table->string('currency')->nullable();
            $table->float('min_salary', 10, 2)->default(0.00);
            $table->float('max_salary', 10, 2)->default(0.00);
            $table->tinyInteger('is_salary_negotiable')->default(0);
            $table->date('posting_open_date')->nullable();
            $table->date('posting_close_date')->nullable();
            $table->enum('application_through', ['Hiring Jet', 'Apply To Email', 'Apply To Link'])->nullable();
            $table->string('apply_on_email')->nullable();
            $table->string('apply_on_link')->nullable();
            $table->string('walkin_address1')->nullable();
            $table->string('walkin_address2')->nullable();
            $table->string('walkin_country')->nullable();
            $table->string('walkin_state')->nullable();
            $table->string('walkin_city')->nullable();
            $table->string('walkin_pincode')->nullable();
            $table->string('walkin_latitude')->nullable();
            $table->string('walkin_longitude')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->softDeletes();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate(); // Auto-updates on change
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_jobs');
    }
};
