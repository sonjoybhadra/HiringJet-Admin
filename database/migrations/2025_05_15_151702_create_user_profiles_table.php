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
            $table->string('last_name', 10, 1000);
            $table->string('email', 100);
            $table->string('country_code', 10);
            $table->string('phone', 20);
            $table->string('whats_app_number', 20);
            $table->string('profile_image');
            $table->date('date_of_birth');
            $table->enum('gender', ['Male', 'Female', 'Other']);
            $table->integer('nationality_id');
            $table->integer('pasport_country_id');
            $table->foreignId('country_id')->constrained('countries')->nullable();
            $table->foreignId('city_id')->constrained('cities')->nullable();
            $table->string('address')->nullable();
            $table->string('resume_headline');
            $table->boolean('currently_employed')->default(true);
            $table->integer('total_experience_years')->nullable();
            $table->integer('total_experience_months')->nullable();
            $table->string('last_designation')->nullable();
            $table->string('last_employer_name')->nullable();
            $table->foreignId('last_employer_location')->constrained('cities')->nullable();
            $table->float('current_salary', 15, 2)->nullable();
            $table->foreignId('current_salary_currency_id')->constrained('currencies')->nullable();
            $table->integer('total_experience_months')->nullable();
            $table->integer('working_since_year')->nullable();
            $table->integer('working_since_month')->nullable();
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
