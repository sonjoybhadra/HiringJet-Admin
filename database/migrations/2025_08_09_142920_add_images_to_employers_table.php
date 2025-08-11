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
        Schema::table('employers', function (Blueprint $table) {
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
            $table->enum('employe_type', ['company', 'agency'])->default('company');
            $table->string('web_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employers', function (Blueprint $table) {
            $table->dropColumn('country_id');
            $table->dropColumn('city_id');
            $table->dropColumn('state_id');
            $table->dropColumn('address');
            $table->dropColumn('address_line_2');
            $table->dropColumn('pincode');
            $table->dropColumn('landline');
            $table->dropColumn('trade_license');
            $table->dropColumn('vat_registration');
            $table->dropColumn('logo');
            $table->dropColumn('employe_type');
            $table->dropColumn('web_url');
        });
    }
};
