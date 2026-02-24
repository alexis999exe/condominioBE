<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario Admin si no existe
        if (!User::where('email', 'admin@condominio.com')->exists()) {
            User::create([
                'name' => 'Admin Sistema',
                'email' => 'admin@condominio.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);

            $this->command->info('✅ Usuario admin creado:');
            $this->command->info('Email: admin@condominio.com');
            $this->command->info('Password: admin123');
        }

        // Crear usuarios residentes de ejemplo
        $residents = [
            [
                'name' => 'Juan Pérez',
                'email' => 'juan@condominio.com',
                'department_number' => 'A-101',
            ],
            [
                'name' => 'María García',
                'email' => 'maria@condominio.com',
                'department_number' => 'A-201',
            ],
            [
                'name' => 'Carlos López',
                'email' => 'carlos@condominio.com',
                'department_number' => 'B-102',
            ],
        ];

        foreach ($residents as $resident) {
            if (!User::where('email', $resident['email'])->exists()) {
                User::create([
                    'name' => $resident['name'],
                    'email' => $resident['email'],
                    'password' => Hash::make('password123'),
                    'role' => 'resident',
                    'department_number' => $resident['department_number'],
                    'email_verified_at' => now(),
                ]);
            }
        }

        $this->command->info('✅ Usuarios residentes creados (password: password123)');
    }
}