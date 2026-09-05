<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StockMovementIndexRequest;
use App\Http\Resources\StockMovementResource;
use App\Models\StockMovement;

class StockMovementController extends Controller
{
    public function index(StockMovementIndexRequest $request)
    {
        $filters = $request->validated();

        $movements = StockMovement::query()
            ->with([
                'product',
                'sourceLocation.warehouse',
                'destinationLocation.warehouse',
                'performedBy',
            ])
            ->when(
                $filters['product_id'] ?? null,
                fn($query, $value) =>
                $query->where('product_id', $value)
            )
            ->when(
                $filters['warehouse_id'] ?? null,
                function ($query, $warehouseId) {
                    $query->where(function ($query) use ($warehouseId) {
                        $query
                            ->whereHas(
                                'sourceLocation',
                                fn($query) =>
                                $query->where(
                                    'warehouse_id',
                                    $warehouseId
                                )
                            )
                            ->orWhereHas(
                                'destinationLocation',
                                fn($query) =>
                                $query->where(
                                    'warehouse_id',
                                    $warehouseId
                                )
                            );
                    });
                }
            )
            ->when(
                $filters['movement_type'] ?? null,
                fn($query, $value) =>
                $query->where('movement_type', $value)
            )
            ->when(
                $filters['from'] ?? null,
                fn($query, $value) =>
                $query->whereDate('created_at', '>=', $value)
            )
            ->when(
                $filters['to'] ?? null,
                fn($query, $value) =>
                $query->whereDate('created_at', '<=', $value)
            )
            ->latest()
            ->paginate(
                $request->integer('per_page', 15)
            );

        return StockMovementResource::collection($movements)
            ->additional([
                'success' => true,
                'message' => 'Stock movements retrieved successfully.',
            ]);
    }
}
