<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\College;

class CollegeSeeder extends Seeder
{
    public function run(): void
    {
        College::create(['name' => 'Main Campus', 'code' => 'MAIN']);
        College::create(['name' => 'Engineering College', 'code' => 'ENG']);
        College::create(['name' => 'Medical College', 'code' => 'MED']);
    }
}
