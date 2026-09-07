<?php

namespace Database\Seeders;

use App\Models\Perusahaan;
use App\Models\User;
use Carbon\Traits\Timestamp;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $perusahaan = Perusahaan::first();

        User::create([
            
                'perusahaan_id' => $perusahaan->id,
                'name' => 'Super Admin',
                'email' => 'superadmin@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            
        ]);

        User::create([
            
                'perusahaan_id' => $perusahaan->id,
                'name' => 'User',
                'email' => 'user@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'role' => 'user',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            
        ]);
    }
}