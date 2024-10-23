<?php

namespace App\Jobs;

use App\Models\Ingredient;
use App\Models\Product;
use App\Notifications\LowStockNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

class CheckAndSendLowStockEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly array $products)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->products as $productData) {
            $product = Product::find($productData['product_id']);
            $this->checkAndSendLowStockEmail($product);
        }
    }
    private function checkAndSendLowStockEmail(Product $product): void
    {
        foreach ($product->ingredients as $ingredient) {
            if ($ingredient->isLowStock() && !$ingredient->email_sent) {
                Notification::route('mail', 'merchant@example.com')
                    ->notify(new LowStockNotification($ingredient));
                $ingredient->update(['email_sent' => '1']);
            }
        }
    }
}
