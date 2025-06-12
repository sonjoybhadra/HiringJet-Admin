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
        Schema::create('user_educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('qualification_id')->constrained('qualifications');
            $table->foreignId('course_id')->constrained('courses');
            $table->foreignId('specialization_id')->constrained('specializations');
            $table->foreignId('location_id')->nullable()->constrained('cities')->nullOnDelete()->comment('belongs to cities');
            $table->string('university_id')->nullable();
            $table->integer('passing_year')->nullable();
            $table->integer('course_start_year')->nullable();
            $table->integer('course_end_year')->nullable();
            $table->string('course_type', 50)->nullable();
            $table->float('percentage', 8,2)->nullable();
            $table->string('grade', 10)->nullable();
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
        Schema::dropIfExists('user_educations');
    }
};
