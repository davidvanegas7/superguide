<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@superguide.com'],
            [
                'name'     => 'Admin',
                'password' => Hash::make('Admin@2024'),
                'role'     => 'admin',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'estudiante@superguide.com'],
            [
                'name'     => 'Estudiante Demo',
                'password' => Hash::make('Student@2024'),
                'role'     => 'student',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Usuarios creados: admin@superguide.com (admin) y estudiante@superguide.com (student)');
    }
}
