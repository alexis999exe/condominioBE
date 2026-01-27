<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['number' => '101', 'tower' => 'A', 'floor' => 1, 'is_active' => true],
            ['number' => '102', 'tower' => 'A', 'floor' => 1, 'is_active' => true],
            ['number' => '103', 'tower' => 'A', 'floor' => 1, 'is_active' => true],
            ['number' => '201', 'tower' => 'A', 'floor' => 2, 'is_active' => true],
            ['number' => '202', 'tower' => 'A', 'floor' => 2, 'is_active' => true],
            ['number' => '301', 'tower' => 'B', 'floor' => 3, 'is_active' => true],
            ['number' => '302', 'tower' => 'B', 'floor' => 3, 'is_active' => true],
        ];

        foreach ($departments as $dept) {
            Department::create($dept);
        }

        $this->command->info('✅ Departamentos creados: ' . count($departments));
    }
}