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
            $table->integer('sl_no');
            $table->string('job_no');
            $table->longText('position_name');
            $table->foreignId('employer_id')->constrained('employers')->onDelete('cascade');
            $table->string('job_type');
            $table->longText('location_ids');
            $table->longText('location_names');
            $table->integer('open_position_number');
            $table->foreignId('contract_type')->nullable();
            $table->longText('job_description');
            $table->longText('requirement');
            $table->longText('skill_ids');
            $table->longText('skill_names');
            $table->foreignId('experience_level')->nullable()->constrained('current_work_levels')->onDelete('cascade');
            $table->string('expected_close_date');
            $table->string('currency')->nullable();
            $table->float('min_salary', 10, 2)->default(0.00);
            $table->float('max_salary', 10, 2)->default(0.00);
            $table->tinyInteger('is_salary_negotiable')->default(0);
            $table->foreignId('industry')->nullable()->constrained('industries')->onDelete('cascade');
            $table->foreignId('job_category')->nullable()->constrained('job_categories')->onDelete('cascade');
            $table->foreignId('department')->nullable()->constrained('departments')->onDelete('cascade');
            $table->foreignId('functional_area')->nullable()->constrained('functional_areas')->onDelete('cascade');
            $table->string('posting_open_date');
            $table->string('posting_close_date');
            $table->string('apply_on_email');
            $table->string('apply_on_link');
            $table->longText('walkin_address1');
            $table->longText('walkin_address2');
            $table->longText('walkin_country');
            $table->longText('walkin_state');
            $table->longText('walkin_city');
            $table->longText('walkin_pincode');
            $table->longText('walkin_latitude');
            $table->longText('walkin_longitude');
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
