<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'location_id' => $this->location_id,
            'quantity' => $this->quantity,

            'product' => [
                'id' => $this->product?->id,
                'sku' => $this->product?->sku,
                'name' => $this->product?->name,
                'unit' => $this->product?->unit,
            ],

            'location' => [
                'id' => $this->location?->id,
                'code' => $this->location?->code,
                'name' => $this->location?->name,
                'warehouse_id' => $this->location?->warehouse_id,
            ],

            'warehouse' => [
                'id' => $this->location?->warehouse?->id,
                'code' => $this->location?->warehouse?->code,
                'name' => $this->location?->warehouse?->name,
            ],

            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
