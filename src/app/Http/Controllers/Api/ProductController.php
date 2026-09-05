<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * GET /api/products
     */
    public function index(Request $request)
    {
        $products = Product::query()
            ->latest()
            ->paginate(
                $request->integer('per_page', 15)
            );

        return ProductResource::collection($products)
            ->additional([
                'success' => true,
                'message' => 'Products retrieved successfully.',
            ]);
    }

    /**
     * POST /api/products
     */
    public function store(ProductRequest $request): JsonResponse
    {
        $product = Product::create(
            $request->validated()
        );

        return (new ProductResource($product))
            ->additional([
                'success' => true,
                'message' => 'Product created successfully.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/products/{product}
     */
    public function show(Product $product): ProductResource
    {
        return (new ProductResource($product))
            ->additional([
                'success' => true,
                'message' => 'Product retrieved successfully.',
            ]);
    }

    /**
     * PUT/PATCH /api/products/{product}
     */
    public function update(
        ProductRequest $request,
        Product $product
    ): ProductResource {
        $product->update(
            $request->validated()
        );

        return (new ProductResource($product->refresh()))
            ->additional([
                'success' => true,
                'message' => 'Product updated successfully.',
            ]);
    }

    /**
     * DELETE /api/products/{product}
     */
    public function destroy(Product $product): JsonResponse
    {
        /*
         * Do not delete a product if it already participates
         * in inventory or movement history.
         */
        if (
            $product->inventories()->exists() ||
            $product->stockMovements()->exists()
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Product cannot be deleted because inventory or movement history exists.',
                'errors' => [],
            ], 409);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully.',
            'data' => [],
        ]);
    }
}
