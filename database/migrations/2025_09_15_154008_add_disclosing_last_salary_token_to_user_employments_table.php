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
        Schema::table('user_employments', function (Blueprint $table) {
            $table->boolean('disclosing_last_salary')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_employments', function (Blueprint $table) {
            $table->dropColumn('disclosing_last_salary');
        });
    }
};
