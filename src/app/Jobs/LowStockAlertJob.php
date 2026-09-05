<?php

namespace App\Jobs;

use App\Models\Inventory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class LowStockAlertJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly int $inventoryId)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $inventory = Inventory::with('product', 'location')
            ->find($this->inventoryId);

        if (!$inventory) {
            return;
        }

        /** @var \App\Models\Product|null $product */
        $product = $inventory->product;

        // Verify the relation model loaded successfully before reading fields
        if ($product) {
            $threshold = $product->low_stock_threshold;

            if ($inventory->quantity < $threshold) {
                Log::warning('Low stock alert', [
                    'product_id' => $inventory->product_id,
                    'location_id' => $inventory->location_id,
                    'quantity' => $inventory->quantity,
                    'threshold' => $threshold,
                ]);
            }
        }
    }
}
