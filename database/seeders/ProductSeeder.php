<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::create([
            'name' => 'A4 Paper Ream',
            'sku' => 'A4-REAM-001',
            'description' => '500 sheets of A4 paper',
            'price' => 5.50,
            'stock' => 100,
        ]);

        Product::create([
            'name' => 'Blue Pens (Box)',
            'sku' => 'PEN-BLU-BX',
            'description' => 'Box of 50 blue ballpoint pens',
            'price' => 10.00,
            'stock' => 50,
        ]);

        Product::create([
            'name' => 'Whiteboard Markers (Set of 4)',
            'sku' => 'MRK-WB-SET4',
            'description' => 'Red, Blue, Green, Black whiteboard markers',
            'price' => 4.50,
            'stock' => 200,
        ]);
    }
}
