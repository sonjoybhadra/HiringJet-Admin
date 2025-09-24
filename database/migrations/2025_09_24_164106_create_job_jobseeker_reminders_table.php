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
        Schema::create('job_jobseeker_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('post_jobs');
            $table->foreignId('jobseeker_id')->constrained('users');
            $table->foreignId('employer_id')->constrained('users');
            $table->integer('reminder_count');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_jobseeker_reminders');
    }
};
