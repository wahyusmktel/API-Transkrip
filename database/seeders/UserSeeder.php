<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@sekolah.com'],
            [
                'name' => 'Admin Sekolah',
                'full_name' => 'Admin Sekolah',
                'email' => 'admin@sekolah.com',
                'school_name' => 'SMK Telkom Lampung',
                'position' => 'admin',
                'password' => Hash::make('password123'),
            ]
        );
    }
}
