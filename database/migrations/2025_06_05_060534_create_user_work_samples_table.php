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
        Schema::create('user_work_samples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('sample_title');
            $table->string('sample_url')->nullable();
            $table->integer('from_month')->nullable();
            $table->integer('from_year')->nullable();
            $table->integer('to_month')->nullable();
            $table->integer('to_year')->nullable();
            $table->boolean('currently_working')->default(false);
            $table->text('sample_description');
            $table->string('sample_image')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_work_samples');
    }
};
