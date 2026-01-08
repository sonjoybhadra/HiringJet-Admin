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
        Schema::create('employer_brands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('company_name', 100);
            $table->string('company_logo')->nullable();
            $table->text('info')->nullable();
            $table->foreignId('industry_id')->constrained('industries');
            $table->foreignId('contact_person_id')->constrained('users');
            $table->foreignId('contact_person_designation_id')->constrained('designations');
            $table->string('web_url');
            $table->string('address');
            $table->foreignId('country')->constrained('countries');
            $table->string('zip_code', 10);
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
        Schema::dropIfExists('employer_brands');
    }
};
