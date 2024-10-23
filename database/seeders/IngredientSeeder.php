<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IngredientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Ingredient::create([
            'name' => 'Beef',
            'initial_stock' => 20.00, // 20 kg
            'stock' => 20.00,         // 20 kg
        ]);

        Ingredient::create([
            'name' => 'Cheese',
            'initial_stock' => 5.00,  // 5 kg
            'stock' => 5.00,          // 5 kg
        ]);

        Ingredient::create([
            'name' => 'Onion',
            'initial_stock' => 1.00,  // 1 kg
            'stock' => 1.00,          // 1 kg
        ]);
    }
}
