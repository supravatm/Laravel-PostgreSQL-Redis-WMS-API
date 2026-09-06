<?php

namespace App\Services;

use App\Jobs\LowStockAlertJob;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function receive(
        int $productId,
        int $locationId,
        int $quantity,
        string $referenceNumber,
        int $userId
    ): Inventory {
        return DB::transaction(function () use (
            $productId,
            $locationId,
            $quantity,
            $referenceNumber,
            $userId
        ) {
            $product = Product::query()
                ->lockForUpdate()
                ->findOrFail($productId);

            if (! $product->isActive()) {
                throw ValidationException::withMessages([
                    'product_id' => 'Inactive products cannot receive stock.',
                ]);
            }

            $inventory = Inventory::query()
                ->where('product_id', $productId)
                ->where('location_id', $locationId)
                ->lockForUpdate()
                ->first();

            if (! $inventory) {
                $inventory = Inventory::create([
                    'product_id' => $productId,
                    'location_id' => $locationId,
                    'quantity' => 0,
                ]);
            }

            $inventory->increment('quantity', $quantity);

            StockMovement::create([
                'product_id' => $productId,
                'source_location_id' => null,
                'destination_location_id' => $locationId,
                'quantity' => $quantity,
                'movement_type' => 'receive',
                'reference_number' => $referenceNumber,
                'performed_by' => $userId,
            ]);

            /*
             * Register cache invalidation and low-stock check.
             * They will execute only after the transaction commits.
             */
            $this->afterInventoryChanged($inventory->id);

            return $inventory->fresh([
                'product',
                'location.warehouse',
            ]);
        });
    }

    public function transfer(
        int $productId,
        int $sourceLocationId,
        int $destinationLocationId,
        int $quantity,
        string $referenceNumber,
        int $userId
    ): Inventory {
        return DB::transaction(function () use (
            $productId,
            $sourceLocationId,
            $destinationLocationId,
            $quantity,
            $referenceNumber,
            $userId
        ) {
            $product = Product::query()
                ->lockForUpdate()
                ->findOrFail($productId);

            if (! $product->isActive()) {
                throw ValidationException::withMessages([
                    'product_id' => 'Inactive products cannot be transferred.',
                ]);
            }

            /*
             * Lock inventory rows in deterministic order.
             * This helps reduce deadlock risk when two transfers
             * involve the same locations in opposite directions.
             */
            $locationIds = [
                $sourceLocationId,
                $destinationLocationId,
            ];

            sort($locationIds);

            $inventories = Inventory::query()
                ->where('product_id', $productId)
                ->whereIn('location_id', $locationIds)
                ->orderBy('location_id')
                ->lockForUpdate()
                ->get()
                ->keyBy('location_id');

            $source = $inventories->get($sourceLocationId);

            if (! $source || $source->quantity < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Insufficient inventory.',
                ]);
            }

            $destination = $inventories->get($destinationLocationId);

            if (! $destination) {
                $destination = Inventory::create([
                    'product_id' => $productId,
                    'location_id' => $destinationLocationId,
                    'quantity' => 0,
                ]);
            }

            $source->decrement('quantity', $quantity);
            $destination->increment('quantity', $quantity);

            StockMovement::create([
                'product_id' => $productId,
                'source_location_id' => $sourceLocationId,
                'destination_location_id' => $destinationLocationId,
                'quantity' => $quantity,
                'movement_type' => 'transfer',
                'reference_number' => $referenceNumber,
                'performed_by' => $userId,
            ]);

            /*
             * Both inventories changed.
             *
             * Source quantity decreased.
             * Destination quantity increased.
             */
            $this->afterInventoryChanged(
                $source->id,
                $destination->id
            );

            return $destination->fresh([
                'product',
                'location.warehouse',
            ]);
        });
    }

    public function dispatch(
        int $productId,
        int $locationId,
        int $quantity,
        string $referenceNumber,
        int $userId
    ): Inventory {
        return DB::transaction(function () use (
            $productId,
            $locationId,
            $quantity,
            $referenceNumber,
            $userId
        ) {
            $product = Product::query()
                ->lockForUpdate()
                ->findOrFail($productId);

            if (! $product->isActive()) {
                throw ValidationException::withMessages([
                    'product_id' => 'Inactive products cannot be dispatched.',
                ]);
            }

            $inventory = Inventory::query()
                ->where('product_id', $productId)
                ->where('location_id', $locationId)
                ->lockForUpdate()
                ->first();

            if (! $inventory || $inventory->quantity < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Insufficient inventory.',
                ]);
            }

            $inventory->decrement('quantity', $quantity);

            StockMovement::create([
                'product_id' => $productId,
                'source_location_id' => $locationId,
                'destination_location_id' => null,
                'quantity' => $quantity,
                'movement_type' => 'dispatch',
                'reference_number' => $referenceNumber,
                'performed_by' => $userId,
            ]);

            /*
             * Inventory quantity decreased.
             * Check for low-stock after commit.
             */
            $this->afterInventoryChanged($inventory->id);

            return $inventory->fresh([
                'product',
                'location.warehouse',
            ]);
        });
    }

    /**
     * Register actions that must happen after the
     * inventory transaction successfully commits.
     */
    private function afterInventoryChanged(int ...$inventoryIds): void
    {
        DB::afterCommit(function () use ($inventoryIds) {
            /*
             * Invalidate inventory cache by changing
             * the inventory cache version.
             */
            Cache::increment('inventory:version');

            /*
             * Send low-stock checks to Redis queue.
             */
            foreach ($inventoryIds as $inventoryId) {
                LowStockAlertJob::dispatch($inventoryId);
            }
        });
    }
}
