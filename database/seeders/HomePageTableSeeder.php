<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\HomePage;

class HomePageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        HomePage::insert([
            [
                'section1_title'=> '',
                'section1_description' => '',
                'section1_button_text' => '',
                'section2_title' => '',
                'section2_description' => '',
                'section2_button_text' => '',
                'section3_box_image' => '',
                'section3_box_text' => '',
                'section3_box_number' => '',
                'section4_title' => '',
                'section4_country' => '',
                'section4_city' => '',
                'section5_title' => '',
                'section5_box_name' => '',
                'section5_box_image' => '',
                'section6_title' => '',
                'section6_description' => '',
                'section6_button_text' => '',
                'section7_title' => '',
                'section7_description' => '',
                'section7_box_name' => '',
                'section7_box_description' => '',
                'section7_box_image' => '',
                'section7_box_link_name' => '',
                'section7_box_link_url' => '',
                'section8_title' => '',
                'section8_description' => '',
                'section9_title' => '',
                'section9_description' => '',
                'section10_title' => '',
                'section10_description' => '',
                'section10_image1' => '',
                'section10_image2' => '',
                'section10_image3' => '',
                'status'    => 1
            ]
        ]);
    }
}
