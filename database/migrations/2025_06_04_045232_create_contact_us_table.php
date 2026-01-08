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
        Schema::create('contact_us', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable()->constrained('users');
            $table->string('name', 100);
            $table->string('email', 100);
            $table->string('phone', 20);
            $table->integer('city_id')->nullable()->constrained('cities');
            $table->string('organization', 100);
            $table->string('interested_in', 50);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_us');
    }
};
