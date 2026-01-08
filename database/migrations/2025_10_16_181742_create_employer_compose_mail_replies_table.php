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

            // Reference to the main composed mail
            $table->unsignedBigInteger('compose_email_id');

            // Sender (employer or jobseeker)
            $table->unsignedBigInteger('sender_id');

            // Receiver (employer or jobseeker)
            $table->unsignedBigInteger('receiver_id');

            $table->text('reply_message');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('compose_email_id')
                  ->references('id')
                  ->on('employer_compose_mails')
                  ->onDelete('cascade');

            $table->foreign('sender_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->foreign('receiver_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
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
