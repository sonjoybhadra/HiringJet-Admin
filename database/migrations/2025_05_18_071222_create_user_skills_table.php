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
        Schema::create('user_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('keyskill_id')->constrained('keyskills');
            $table->enum('proficiency_level', ['Beginner', 'Intermediate', 'Advanced', 'Expert'])->default('Beginner');
            $table->integer('years_of_experience')->nullable();
            $table->boolean('is_primary')->default(true);
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
        Schema::dropIfExists('user_skills');
    }
};
