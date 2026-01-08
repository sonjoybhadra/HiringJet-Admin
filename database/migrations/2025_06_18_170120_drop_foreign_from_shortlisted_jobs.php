<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shortlisted_jobs', function (Blueprint $table) {
            // Drop the foreign key and optionally the column (if needed)
            $table->dropForeign(['job_id']); // Drops the constraint named shortlisted_jobs_job_id_foreign
            // $table->dropColumn('job_id'); // Uncomment only if you want to remove the column too
        });
    }

    public function down(): void
    {
        Schema::table('shortlisted_jobs', function (Blueprint $table) {
            // Re-add the column and foreign key
            // $table->unsignedBigInteger('job_id'); // Uncomment only if you dropped the column
            $table->foreign('job_id')->references('id')->on('post_jobs')->onDelete('cascade');
        });
    }
};
