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
        Schema::create('user_employers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('parent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 100)->unique();
            $table->string('country_code', 10);
            $table->string('phone', 15)->unique();
            $table->foreignId('business_id')->constrained('employers');
            $table->foreignId('designation_id')->constrained('designations');
            $table->foreignId('industrie_id')->constrained('industries');
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->foreignId('state_id')->nullable()->constrained('states')->nullOnDelete();
            $table->string('address')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('pincode', 10)->nullable();
            $table->string('landline', 20)->nullable();
            $table->string('trade_license')->nullable();
            $table->string('vat_registration')->nullable();
            $table->string('logo')->nullable();
            $table->string('profile_image')->nullable();
            $table->text('description')->nullable();
            $table->string('web_url')->nullable();
            $table->integer('completed_steps')->nullable();
            $table->integer('is_active')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_employers');
    }
};
