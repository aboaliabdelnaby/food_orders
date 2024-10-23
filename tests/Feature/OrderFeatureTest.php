<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Product;
use App\Notifications\LowStockNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderFeatureTest extends TestCase
{
    use DatabaseTransactions;

    #[Test] public function it_can_place_an_order_and_update_ingredient_stock()
    {
        Notification::fake();
        $this->seed();

        $product = Product::create(['name' => 'Burger', 'price' => 100]);

        // Attach ingredients to the product
        $ingredientBeef = Ingredient::create(['name' => 'Beef', 'initial_stock' => 20, 'stock' => 20]);
        $ingredientCheese = Ingredient::create(['name' => 'Cheese', 'initial_stock' => 5, 'stock' => 5]);
        $ingredientOnion = Ingredient::create(['name' => 'Onion', 'initial_stock' => 1, 'stock' => 1]);

        $product->ingredients()->attach($ingredientBeef->id, ['amount' => 0.15]); // 150g of Beef
        $product->ingredients()->attach($ingredientCheese->id, ['amount' => 0.03]); // 30g of Cheese
        $product->ingredients()->attach($ingredientOnion->id, ['amount' => 0.02]); // 20g of Onion

        // Simulate placing an order with 2 Burgers
        $response = $this->postJson('/api/order', [
            'products' => [
                ['product_id' => $product->id, 'quantity' => 2]
            ]
        ]);

        // Assert the order was successful
        $response->assertStatus(200)
            ->assertJson(['message' => 'Order created successfully']);

        // Assert the stock was correctly updated
        $this->assertEquals(19.7, $ingredientBeef->fresh()->stock);  // 20kg - (2 * 0.15kg) = 19.7kg
        $this->assertEquals(4.94, $ingredientCheese->fresh()->stock); // 5kg - (2 * 0.03kg) = 4.94kg
        $this->assertEquals(0.96, $ingredientOnion->fresh()->stock);  // 1kg - (2 * 0.02kg) = 0.96kg
    }

    #[Test] public function it_sends_a_low_stock_notification_when_ingredient_drops_below_50_percent()
    {
        Notification::fake();

        $this->seed();

        $product = Product::create(['name' => 'Burger', 'price' => 100]);

        // Attach ingredients to the product
        $ingredientBeef = Ingredient::create(['name' => 'Beef', 'initial_stock' => 20, 'stock' => 20]);
        $ingredientCheese = Ingredient::create(['name' => 'Cheese', 'initial_stock' => 5, 'stock' => 5]);
        $ingredientOnion = Ingredient::create(['name' => 'Onion', 'initial_stock' => 1, 'stock' => 1]);

        $product->ingredients()->attach($ingredientBeef->id, ['amount' => 0.15]); // 150g of Beef
        $product->ingredients()->attach($ingredientCheese->id, ['amount' => 0.03]); // 30g of Cheese
        $product->ingredients()->attach($ingredientOnion->id, ['amount' => 0.02]); // 20g of Onion

        $response = $this->postJson('/api/order', [
            'products' => [
                ['product_id' => $product->id, 'quantity' => 40] // Large quantity to drop stock
            ]
        ]);

        // Assert the stock was correctly updated
        $this->assertEquals(14.00, $ingredientBeef->fresh()->stock); // Stock below 50% (9.5kg)

        // Assert no duplicate notifications
        $response = $this->postJson('/api/order', [
            'products' => [
                ['product_id' => $product->id, 'quantity' => 1] // Another order
            ]
        ]);
        Notification::assertSentTimes(LowStockNotification::class, 1); // Ensure only one notification was sent
    }

    #[Test] public function it_returns_error_if_requested_quantity_exceeds_stock()
    {
        Notification::fake();

        $this->seed();

        $product = Product::create(['name' => 'Burger', 'price' => 100]);

        // Attach ingredients to the product
        $ingredientBeef = Ingredient::create(['name' => 'Beef', 'initial_stock' => 20, 'stock' => 20]);
        $ingredientCheese = Ingredient::create(['name' => 'Cheese', 'initial_stock' => 5, 'stock' => 5]);
        $ingredientOnion = Ingredient::create(['name' => 'Onion', 'initial_stock' => 1, 'stock' => 1]);

        $product->ingredients()->attach($ingredientBeef->id, ['amount' => 0.15]); // 150g of Beef
        $product->ingredients()->attach($ingredientCheese->id, ['amount' => 0.03]); // 30g of Cheese
        $product->ingredients()->attach($ingredientOnion->id, ['amount' => 0.02]); // 20g of Onion

        $response = $this->postJson('/api/order', [
            'products' => [
                ['product_id' => $product->id, 'quantity' => 70] // Large quantity to drop stock
            ]
        ]);

        $response->assertJson([
            'success' => false,
            'message' => 'Quantity of Onion out of stock'
        ]);
    }

    #[Test] public function it_returns_error_if_product_id_invalid()
    {
        Notification::fake();

        $this->seed();

        $product = Product::create(['name' => 'Burger', 'price' => 100]);

        // Attach ingredients to the product
        $ingredientBeef = Ingredient::create(['name' => 'Beef', 'initial_stock' => 20, 'stock' => 20]);
        $ingredientCheese = Ingredient::create(['name' => 'Cheese', 'initial_stock' => 5, 'stock' => 5]);
        $ingredientOnion = Ingredient::create(['name' => 'Onion', 'initial_stock' => 1, 'stock' => 1]);

        $product->ingredients()->attach($ingredientBeef->id, ['amount' => 0.15]); // 150g of Beef
        $product->ingredients()->attach($ingredientCheese->id, ['amount' => 0.03]); // 30g of Cheese
        $product->ingredients()->attach($ingredientOnion->id, ['amount' => 0.02]); // 20g of Onion

        $response = $this->postJson('/api/order', [
            'products' => [
                ['product_id' => 1000, 'quantity' => 70] // Large quantity to drop stock
            ]
        ]);
        $response->assertStatus(422);
    }
}
