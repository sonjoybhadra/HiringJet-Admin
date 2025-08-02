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
        Schema::create('home_pages', function (Blueprint $table) {
            $table->id();
            $table->longtext('section1_title');
            $table->longtext('section1_description');
            $table->string('section1_button_text', 250);
            $table->longtext('section2_title');
            $table->longtext('section2_description');
            $table->string('section2_button_text', 250);
            $table->longtext('section3_box_image');
            $table->longtext('section3_box_text');
            $table->longtext('section3_box_number');
            $table->string('section4_title', 250);
            $table->longtext('section4_country');
            $table->longtext('section4_city');
            $table->string('section5_title', 250);
            $table->longtext('section5_box_name');
            $table->longtext('section5_box_image');
            $table->longtext('section6_title');
            $table->longtext('section6_description');
            $table->string('section6_button_text', 250);
            $table->longtext('section7_title');
            $table->longtext('section7_description');
            $table->longtext('section7_box_name');
            $table->longtext('section7_box_description');
            $table->longtext('section7_box_image');
            $table->longtext('section7_box_link_name');
            $table->longtext('section7_box_link_url');
            $table->longtext('section8_title');
            $table->longtext('section8_description');
            $table->longtext('section9_title');
            $table->longtext('section9_description');
            $table->longtext('section10_title');
            $table->longtext('section10_description');
            $table->longtext('section10_image1');
            $table->longtext('section10_image2');
            $table->longtext('section10_image3');
            $table->tinyInteger('status')->default(1);
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
        Schema::dropIfExists('home_pages');
    }
};
