<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DispatchStockRequest;
use App\Http\Requests\ReceiveStockRequest;
use App\Http\Requests\TransferStockRequest;
use App\Http\Resources\InventoryResource;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;

class InventoryOperationController extends Controller
{
    public function __construct(
        private readonly InventoryService $inventoryService
    ) {
        //
    }

    public function receive(
        ReceiveStockRequest $request
    ): JsonResponse {
        $inventory = $this->inventoryService->receive(
            productId: $request->integer('product_id'),
            locationId: $request->integer('location_id'),
            quantity: $request->integer('quantity'),
            referenceNumber: $request->string('reference_number')->toString(),
            userId: $request->user()->id,
        );

        return (new InventoryResource($inventory))
            ->additional([
                'success' => true,
                'message' => 'Stock received successfully.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    public function transfer(
        TransferStockRequest $request
    ): JsonResponse {
        $inventory = $this->inventoryService->transfer(
            productId: $request->integer('product_id'),
            sourceLocationId: $request->integer('source_location_id'),
            destinationLocationId: $request->integer('destination_location_id'),
            quantity: $request->integer('quantity'),
            referenceNumber: $request->string('reference_number')->toString(),
            userId: $request->user()->id,
        );

        return (new InventoryResource($inventory))
            ->additional([
                'success' => true,
                'message' => 'Stock transferred successfully.',
            ])
            ->response();
    }

    public function dispatch(
        DispatchStockRequest $request
    ): JsonResponse {
        $inventory = $this->inventoryService->dispatch(
            productId: $request->integer('product_id'),
            locationId: $request->integer('location_id'),
            quantity: $request->integer('quantity'),
            referenceNumber: $request->string('reference_number')->toString(),
            userId: $request->user()->id,
        );

        return (new InventoryResource($inventory))
            ->additional([
                'success' => true,
                'message' => 'Stock dispatched successfully.',
            ])
            ->response();
    }
}
