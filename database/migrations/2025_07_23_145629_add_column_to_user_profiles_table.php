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
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->boolean('job_type_temp')->default(false);
            $table->boolean('job_type_permanent')->default(false);
            $table->boolean('temp_remote')->default(false);
            $table->boolean('temp_onsite')->default(false);
            $table->boolean('temp_hybrid')->default(false);
            $table->boolean('permanent_remote')->default(false);
            $table->boolean('permanent_onsite')->default(false);
            $table->boolean('permanent_hybrid')->default(false);
        });
    }

    /**
     * Reverse the migrations.
    */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn('job_type_temp');
            $table->dropColumn('job_type_permanent');
            $table->dropColumn('temp_remote');
            $table->dropColumn('temp_onsite');
            $table->dropColumn('temp_hybrid');
            $table->dropColumn('permanent_remote');
            $table->dropColumn('permanent_onsite');
            $table->dropColumn('permanent_hybrid');
        });
    }
};
