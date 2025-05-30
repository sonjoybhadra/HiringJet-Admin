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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 100);
            $table->string('country_code', 10);
            $table->string('phone', 20);
            $table->string('whatsapp_country_code', 10)->nullable();
            $table->string('whatsapp_number', 20)->nullable();
            $table->string('profile_image')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable();
            $table->integer('merital_status_id')->nullable()->constrained('marital_statuses');
            $table->boolean('differently_abled')->default(false);
            $table->boolean('career_break')->default(false);
            $table->integer('nationality_id')->nullable()->constrained()->nullOnDelete();
            $table->string('cast_category')->nullable();
            $table->boolean('usa_working_permit')->default(false);
            $table->integer('pasport_country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->string('address')->nullable();
            $table->string('pincode', 10)->nullable();
            $table->string('resume_headline')->nullable();
            $table->string('alt_email', 100)->nullable();
            $table->string('alt_country_code', 10)->nullable();
            $table->string('alt_phone', 20)->nullable();
            $table->string('diverse_background', 50)->nullable();
            $table->boolean('is_experienced')->default(false);
            $table->boolean('currently_employed')->default(false);

            /*---------- Not required here ---------*/
            /* $table->integer('total_experience_years')->nullable();
            $table->integer('total_experience_months')->nullable();
            $table->string('last_designation')->nullable();
            $table->string('last_employer_name')->nullable();
            $table->foreignId('employer_country_id')->nullable();
            $table->foreignId('employer_city_id')->nullable();
            $table->float('current_salary', 15, 2)->nullable();
            $table->foreignId('current_salary_currency_id')->nullable();
            $table->integer('working_since_from_year')->nullable();
            $table->integer('working_since_from_month')->nullable();
            $table->integer('working_since_to_year')->nullable();
            $table->integer('working_since_to_month')->nullable(); */
            /*---------- Not required here ---------*/

            $table->text('profile_summery')->nullable();
            $table->text('preferred_designation')->nullable();
            $table->text('preferred_location')->nullable();
            $table->text('preferred_industry')->nullable();
            $table->foreignId('availabilitie_id')->nullable()->constrained('availabilities')->nullOnDelete();
            $table->integer('profile_completed_percentage')->default(0);
            $table->integer('completed_steps');
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
        Schema::dropIfExists('user_profiles');
    }
};
