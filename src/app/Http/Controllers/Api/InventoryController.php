<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\InventoryIndexRequest;
use App\Models\Inventory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class InventoryController extends Controller
{
    public function index(InventoryIndexRequest $request): JsonResponse
    {
        $filters = $request->validated();

        $version = Cache::rememberForever(
            'inventory:version',
            fn () => 1
        );

        $page = $request->integer('page', 1);
        $perPage = $request->integer('per_page', 10);

        $cacheKey = 'inventory:v'.$version.':'.md5(
            json_encode([
                'filters' => $filters,
                'page' => $page,
                'per_page' => $perPage,
            ])
        );

        $inventories = Cache::remember(
            $cacheKey,
            now()->addMinutes(5),
            function () use ($filters, $page, $perPage) {

                $paginator = Inventory::query()
                    ->with([
                        'product',
                        'location.warehouse',
                    ])
                    ->when(
                        $filters['product_id'] ?? null,
                        fn ($query, $productId) => $query->where('product_id', $productId)
                    )
                    ->when(
                        $filters['location_id'] ?? null,
                        fn ($query, $locationId) => $query->where('location_id', $locationId)
                    )
                    ->when(
                        $filters['warehouse_id'] ?? null,
                        fn ($query, $warehouseId) => $query->whereHas(
                            'location',
                            fn ($query) => $query->where(
                                'warehouse_id',
                                $warehouseId
                            )
                        )
                    )
                    ->orderBy('id')
                    ->paginate(
                        $perPage,
                        ['*'],
                        'page',
                        $page
                    );

                return [
                    'items' => collect($paginator->items())
                        ->map(fn ($inventory) => $inventory->toArray())
                        ->values()
                        ->all(),

                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ];
            }
        );

        return response()->json([
            'success' => true,
            'message' => 'Inventory retrieved successfully.',
            'data' => $inventories['items'],
            'meta' => [
                'current_page' => $inventories['current_page'],
                'last_page' => $inventories['last_page'],
                'per_page' => $inventories['per_page'],
                'total' => $inventories['total'],
                'from' => $inventories['from'],
                'to' => $inventories['to'],
            ],
        ]);
    }
}
