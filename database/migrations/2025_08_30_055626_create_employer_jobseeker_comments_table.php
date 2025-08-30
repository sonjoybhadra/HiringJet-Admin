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
        Schema::create('employer_jobseeker_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_id')->constrained('users');
            $table->foreignId('jobseeker_id')->constrained('users');
            $table->text('comment');
            $table->integer('status')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employer_jobseeker_comments');
    }
};
