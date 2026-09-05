<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Inventory
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
        /** @var \App\Models\Product|null $product */
        $product = $this->product;

        /** @var \App\Models\Location|null $location */
        $location = $this->location;

        /** @var \App\Models\Warehouse|null $warehouse */
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
