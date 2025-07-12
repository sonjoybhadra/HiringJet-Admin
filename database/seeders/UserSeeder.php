<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::insert([
            [
                'role_id'=> 1,
                'first_name' => 'Admin',
                'last_name' => 'User',
                'email' => 'admin@hiringjet.com',
                'password' => Hash::make('admin123'),
                'country_code' => '+971',
                'phone' => '0000000000',
                'status'    => 1
            ],
            [
                'role_id'=> 3,
                'first_name' => 'User',
                'last_name' => 'Jobseeker',
                'email' => 'demoJobseeker@hiringjet.com',
                'password' => Hash::make('demo123'),
                'country_code' => '+971',
                'phone' => '1111111111',
                'status'    => 1
            ]
        ]);
    }
}
