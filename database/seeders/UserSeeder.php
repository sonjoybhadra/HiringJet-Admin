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
                'parent_id'=> 0,
                'name' => 'Admin User',
                'email' => 'admin@hiringjet.com',
                'password' => Hash::make('admin123'),
                'country_code' => '+971',
                'phone' => '0000000000',
                'status'    => 1
            ]
        ]);
    }
}
