<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Administrador',
                'email' => 'admin@condominio.com',
                'password' => Hash::make('admin123'),
                'department_id' => 1,
                'role' => 'admin',
            ],
            [
                'name' => 'Juan Pérez',
                'email' => 'juan@condominio.com',
                'password' => Hash::make('password'),
                'department_id' => 1,
                'role' => 'resident',
            ],
            [
                'name' => 'María García',
                'email' => 'maria@condominio.com',
                'password' => Hash::make('password'),
                'department_id' => 2,
                'role' => 'resident',
            ],
            [
                'name' => 'Carlos López',
                'email' => 'carlos@condominio.com',
                'password' => Hash::make('password'),
                'department_id' => 3,
                'role' => 'resident',
            ],
            [
                'name' => 'Ana Martínez',
                'email' => 'ana@condominio.com',
                'password' => Hash::make('password'),
                'department_id' => 4,
                'role' => 'resident',
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }

        $this->command->info('✅ Usuarios creados: ' . count($users));
    }
}