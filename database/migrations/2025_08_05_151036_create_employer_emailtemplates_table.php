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
        Schema::create('employer_emailtemplates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('template_name');
            $table->foreignId('from_email_user_id')->constrained('users');
            $table->foreignId('designation_id')->constrained('designations');
            $table->integer('experience_max');
            $table->integer('experience_min');
            $table->foreignId('country_id')->constrained('countries');
            $table->foreignId('city_id')->constrained('cities');
            $table->foreignId('currency_id')->constrained('currencies');
            $table->integer('salary_max');
            $table->integer('salary_min');
            $table->text('message');
            $table->foreignId('owner_id')->constrained('users');
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
        Schema::dropIfExists('employer_emailtemplates');
    }
};
