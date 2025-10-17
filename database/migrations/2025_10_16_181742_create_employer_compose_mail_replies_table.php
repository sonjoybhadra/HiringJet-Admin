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
        Schema::create('employer_compose_mail_replies', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('compose_email_id');
        $table->unsignedBigInteger('jobseeker_id');
        $table->text('reply_message');
        $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employer_compose_mail_replies');
    }
};
