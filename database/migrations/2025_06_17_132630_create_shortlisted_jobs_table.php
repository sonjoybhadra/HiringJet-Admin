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
        Schema::create('shortlisted_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('post_jobs');
            $table->foreignId('user_id')->constrained('users');
            $table->integer('status')->default(1);
            $table->softDeletes();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate(); // Auto-updates on change
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shortlisted_jobs');
    }
};
