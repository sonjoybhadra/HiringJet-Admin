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
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->string('user_email', 100)->nullable();
            $table->string('user_name', 100)->nullable();
            $table->enum('user_type', ['ADMIN', 'USER']);
            $table->string('ip_address', 50);
            $table->tinyInteger('activity_type')->comment('0=>failed login, 1=>success login, 2=>logout');
            $table->longText('activity_details');
            $table->enum('platform_type', ['WEB','MOBILE','ANDROID','IOS'])->default('WEB');
            $table->softDeletes();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate(); // Auto-updates on change
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};
