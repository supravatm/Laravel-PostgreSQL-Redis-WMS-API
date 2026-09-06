<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\WarehouseRequest;
use App\Http\Resources\WarehouseResource;
use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $warehouses = Warehouse::query()
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return WarehouseResource::collection($warehouses)
            ->additional([
                'success' => true,
                'message' => 'Warehouses retrieved successfully.',
            ]);
    }

    public function store(WarehouseRequest $request): JsonResponse
    {
        $warehouse = Warehouse::create($request->validated());

        return (new WarehouseResource($warehouse))
            ->additional([
                'success' => true,
                'message' => 'Warehouse created successfully.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Warehouse $warehouse): WarehouseResource
    {
        return (new WarehouseResource($warehouse))
            ->additional([
                'success' => true,
                'message' => 'Warehouse retrieved successfully.',
            ]);
    }

    public function update(
        WarehouseRequest $request,
        Warehouse $warehouse
    ): WarehouseResource {
        $warehouse->update($request->validated());

        return (new WarehouseResource($warehouse->refresh()))
            ->additional([
                'success' => true,
                'message' => 'Warehouse updated successfully.',
            ]);
    }

    public function destroy(Warehouse $warehouse): JsonResponse
    {
        if ($warehouse->locations()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Warehouse cannot be deleted because locations exist.',
                'errors' => [],
            ], 409);
        }

        $warehouse->delete();

        return response()->json([
            'success' => true,
            'message' => 'Warehouse deleted successfully.',
            'data' => [],
        ]);
    }
}
