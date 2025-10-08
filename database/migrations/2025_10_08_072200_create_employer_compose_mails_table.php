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
        Schema::create('employer_compose_mails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('from_email');
            $table->foreignId('designation_id')->nullable();
            $table->integer('experience_max')->nullable();
            $table->integer('experience_min')->nullable();
            $table->foreignId('country_id')->constrained('countries');
            $table->foreignId('city_id')->constrained('cities');
            $table->foreignId('currency_id')->constrained('countries');
            $table->integer('salary_max')->nullable();
            $table->integer('salary_min')->nullable();
            $table->string('tag_id')->nullable();
            $table->text('subject');
            $table->text('message');
            $table->text('questions')->nullable(); //json string
            $table->text('answers')->nullable(); //json string
            $table->integer('status');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employer_compose_mails');
    }
};
