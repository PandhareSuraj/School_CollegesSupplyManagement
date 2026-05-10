<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        Department::create(['college_id' => 1, 'name' => 'Admin Department', 'code' => 'ADMIN-MAIN']);
        Department::create(['college_id' => 2, 'name' => 'Computer Science', 'code' => 'CS-ENG']);
        Department::create(['college_id' => 2, 'name' => 'Mechanical Engineering', 'code' => 'ME-ENG']);
        Department::create(['college_id' => 3, 'name' => 'Anatomy', 'code' => 'ANA-MED']);
    }
}
