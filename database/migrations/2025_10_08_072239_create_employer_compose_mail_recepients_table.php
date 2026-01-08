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
        Schema::create('employer_compose_mail_recepients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compose_email_id')->constrained('employer_compose_mails');
            $table->foreignId('jobseeker_id')->constrained('users');
            $table->foreignId('employer_id')->constrained('users');
            $table->boolean('reply_status')->default(false);
            $table->boolean('view_status')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employer_compose_mail_recepients');
    }
};
