<?php

namespace App\Services\OrderService;

use App\Jobs\CheckAndSendLowStockEmail;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\Product;
use App\Notifications\LowStockNotification;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class OrderService implements OrderServiceInterface
{
    public function order(array $data): array
    {
        try {
            DB::transaction(function () use ($data) {
                $order = Order::create(['name' => 'test']);
                $total = $this->processOrderProducts($order, $data);
                $order->update(['total_price' => $total]);
            });
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }

        CheckAndSendLowStockEmail::dispatch($data['products']);

        return ['success' => true, 'message' => "Order created successfully"];
    }

    /**
     * @throws Exception
     */
    private function processOrderProducts(Order $order, array $products): float
    {
        $total = 0;
        foreach ($products['products'] as $productData) {
            $product = Product::find($productData['product_id']);
            $order->products()->attach($product->id, ['quantity' => $productData['quantity'], 'price' => $product->price]);
            $total += $productData['quantity'] * $product->price;
            $this->processProductIngredients($product, $productData['quantity']);
        }
        return $total;
    }

    /**
     * @throws Exception
     */
    private function processProductIngredients(Product $product, int $quantity): void
    {
        foreach ($product->ingredients as $ingredient) {
            $amountUsed = $ingredient->pivot->amount * $quantity;
            $this->updateStock($amountUsed, $ingredient);
        }
    }

    /**
     * @throws Exception
     */
    private function updateStock(float $amountUsed, Ingredient $ingredient): bool
    {
        if ($amountUsed > $ingredient->stock) {
            return throw new Exception("Quantity of $ingredient->name out of stock");
        }
        $ingredient->update(['stock' => $ingredient->stock - $amountUsed]);
        return true;
    }

}
