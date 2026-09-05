<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\LocationRequest;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    public function index(Warehouse $warehouse, Request $request)
    {
        $locations = $warehouse->locations()
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return LocationResource::collection($locations)
            ->additional([
                'success' => true,
                'message' => 'Locations retrieved successfully.',
            ]);
    }

    public function store(
        LocationRequest $request,
        Warehouse $warehouse
    ): JsonResponse {
        $location = $warehouse->locations()->create(
            $request->validated()
        );

        return (new LocationResource($location))
            ->additional([
                'success' => true,
                'message' => 'Location created successfully.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Location $location): LocationResource
    {
        return (new LocationResource($location))
            ->additional([
                'success' => true,
                'message' => 'Location retrieved successfully.',
            ]);
    }

    public function update(
        LocationRequest $request,
        Location $location
    ): LocationResource {
        $location->update($request->validated());

        return (new LocationResource($location->refresh()))
            ->additional([
                'success' => true,
                'message' => 'Location updated successfully.',
            ]);
    }

    public function destroy(Location $location): JsonResponse
    {
        if ($location->inventories()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Location cannot be deleted because inventory exists.',
                'errors' => [],
            ], 409);
        }

        $location->delete();

        return response()->json([
            'success' => true,
            'message' => 'Location deleted successfully.',
            'data' => [],
        ]);
    }
}
