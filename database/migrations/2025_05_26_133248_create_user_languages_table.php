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
        Schema::create('user_languages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('language_id')->constrained('languages');
            $table->boolean('can_read')->default(false);
            $table->boolean('can_write')->default(false);
            $table->boolean('can_speak')->default(false);
            $table->enum('proficiency_level', ['Beginner', 'Intermediate', 'Advanced', 'Expert'])->default('Beginner');
            $table->boolean('is_default')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_languages');
    }
};
