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
        Schema::table('home_pages', function (Blueprint $table) {
            $table->longtext('section1')->nullable();
            $table->longtext('section2')->nullable();
            $table->longtext('section3')->nullable();
            $table->longtext('section4')->nullable();
            $table->longtext('section5')->nullable();
            $table->longtext('section6')->nullable();
            $table->longtext('section7')->nullable();
            $table->longtext('section8')->nullable();
            $table->longtext('section9')->nullable();
            $table->longtext('section10')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('home_pages', function (Blueprint $table) {
            //
        });
    }
};
