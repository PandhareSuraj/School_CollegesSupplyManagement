<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Teacher
        $teacher = User::create([
            'name' => 'John Teacher',
            'email' => 'teacher@example.com',
            'password' => Hash::make('password'),
            'department_id' => 2,
        ]);
        $teacher->assignRole('teacher');

        // HOD
        $hod = User::create([
            'name' => 'Jane HOD',
            'email' => 'hod@example.com',
            'password' => Hash::make('password'),
            'department_id' => 2,
        ]);
        $hod->assignRole('hod');

        // Principal
        $principal = User::create([
            'name' => 'Dr. Principal',
            'email' => 'principal@example.com',
            'password' => Hash::make('password'),
        ]);
        $principal->assignRole('principal');

        // Trust Head
        $trust = User::create([
            'name' => 'Mr. Trust Head',
            'email' => 'trust@example.com',
            'password' => Hash::make('password'),
        ]);
        $trust->assignRole('trust_head');

        // Provider
        $provider = User::create([
            'name' => 'Stationary Provider',
            'email' => 'provider@example.com',
            'password' => Hash::make('password'),
        ]);
        $provider->assignRole('provider');
    }
}
