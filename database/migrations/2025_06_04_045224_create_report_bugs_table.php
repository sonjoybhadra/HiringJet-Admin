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
        Schema::create('report_bugs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable()->constrained('users');
            $table->string('email', 100);
            $table->string('phone', 20);
            $table->string('category', 100);
            $table->text('description');
            $table->string('source', 50)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_bugs');
    }
};
