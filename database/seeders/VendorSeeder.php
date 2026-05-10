<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vendor;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        Vendor::create([
            'name' => 'Campus Stationary Supplies',
            'email' => 'contact@campusstationary.com',
            'phone' => '1234567890',
            'address' => '123 Supply St.',
        ]);

        Vendor::create([
            'name' => 'Elite Office Goods',
            'email' => 'sales@eliteoffice.com',
            'phone' => '0987654321',
            'address' => '456 Business Rd.',
        ]);
    }
}
