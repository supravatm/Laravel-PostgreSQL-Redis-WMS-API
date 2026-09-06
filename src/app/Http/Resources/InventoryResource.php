<?php

namespace App\Http\Resources;

use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Inventory
 */
class InventoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Product|null $product */
        $product = $this->product;

        /** @var Location|null $location */
        $location = $this->location;

        /** @var Warehouse|null $warehouse */
        $warehouse = $location?->warehouse;

        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'location_id' => $this->location_id,
            'quantity' => $this->quantity,

            'product' => [
                'id' => $product?->id,
                'sku' => $product?->sku,
                'name' => $product?->name,
                'unit' => $product?->unit,
            ],

            'location' => [
                'id' => $location?->id,
                'code' => $location?->code,
                'name' => $location?->name,
                'warehouse_id' => $location?->warehouse_id,
            ],

            'warehouse' => [
                'id' => $warehouse?->id,
                'code' => $warehouse?->code,
                'name' => $warehouse?->name,
            ],

            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
