<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $burger = Product::create([
            'name' => 'Burger',
            'price' => 100,
        ]);

        $burger->ingredients()->attach([
            Ingredient::where('name', 'Beef')->first()->id => ['amount' => 0.15],   // 150g (0.15kg) Beef
            Ingredient::where('name', 'Cheese')->first()->id => ['amount' => 0.03], // 30g (0.03kg) Cheese
            Ingredient::where('name', 'Onion')->first()->id => ['amount' => 0.02],  // 20g (0.02kg) Onion
        ]);
    }
}
