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
        Schema::create('post_job_employer_sharings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('post_jobs');
            $table->foreignId('owner_id')->constrained('users');
            $table->foreignId('sharing_user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_job_employer_sharings');
    }
};
