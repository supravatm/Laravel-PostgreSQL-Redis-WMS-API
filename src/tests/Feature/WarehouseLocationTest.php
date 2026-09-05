<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseLocationTest extends TestCase
{
    use RefreshDatabase;

    /*
    * Warehouse Tests
    */

    public function test_unauthenticated_user_cannot_access_warehouses(): void
    {
        $this->getJson('/api/warehouses')
            ->assertUnauthorized();
    }

    public function test_authenticated_user_can_create_warehouse(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/warehouses', [
                'name' => 'Kolkata Warehouse',
                'code' => 'WH-KOL-01',
                'address' => 'Kolkata',
                'status' => 'active',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', 'Kolkata Warehouse')
            ->assertJsonPath('data.code', 'WH-KOL-01');

        $this->assertDatabaseHas('warehouses', [
            'name' => 'Kolkata Warehouse',
            'code' => 'WH-KOL-01',
        ]);
    }

    public function test_duplicate_warehouse_code_is_rejected(): void
    {
        $user = User::factory()->create();

        Warehouse::factory()->create([
            'code' => 'WH-KOL-01',
        ]);

        $this->actingAs($user)
            ->postJson('/api/warehouses', [
                'name' => 'Another Warehouse',
                'code' => 'WH-KOL-01',
                'address' => 'Kolkata',
                'status' => 'active',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);
    }

    public function test_warehouse_can_be_updated(): void
    {
        $user = User::factory()->create();

        $warehouse = Warehouse::factory()->create([
            'name' => 'Old Warehouse',
            'code' => 'WH-OLD',
        ]);

        $this->actingAs($user)
            ->putJson("/api/warehouses/{$warehouse->id}", [
                'name' => 'Updated Warehouse',
                'code' => 'WH-UPDATED',
                'address' => 'Kolkata',
                'status' => 'active',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated Warehouse');

        $this->assertDatabaseHas('warehouses', [
            'id' => $warehouse->id,
            'name' => 'Updated Warehouse',
            'code' => 'WH-UPDATED',
        ]);
    }

    public function test_warehouse_can_be_retrieved(): void
    {
        $user = User::factory()->create();

        $warehouse = Warehouse::factory()->create();

        $this->actingAs($user)
            ->getJson("/api/warehouses/{$warehouse->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $warehouse->id);
    }

    public function test_warehouse_can_be_deleted_when_no_locations_exist(): void
    {
        $user = User::factory()->create();

        $warehouse = Warehouse::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/api/warehouses/{$warehouse->id}")
            ->assertOk();

        $this->assertDatabaseMissing('warehouses', [
            'id' => $warehouse->id,
        ]);
    }

    public function test_warehouse_cannot_be_deleted_when_locations_exist(): void
    {
        $user = User::factory()->create();

        $warehouse = Warehouse::factory()->create();

        Location::factory()->create([
            'warehouse_id' => $warehouse->id,
        ]);

        $this->actingAs($user)
            ->deleteJson("/api/warehouses/{$warehouse->id}")
            ->assertConflict()
            ->assertJson([
                'success' => false,
                'message' => 'Warehouse cannot be deleted because locations exist.',
            ]);

        $this->assertDatabaseHas('warehouses', [
            'id' => $warehouse->id,
        ]);
    }

    /*
    * Location Tests
    */

    public function test_authenticated_user_can_create_location(): void
    {
        $user = User::factory()->create();

        $warehouse = Warehouse::factory()->create();

        $response = $this->actingAs($user)
            ->postJson("/api/warehouses/{$warehouse->id}/locations", [
                'code' => 'A-01',
                'name' => 'Rack A - Shelf 01',
                'status' => 'active',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.code', 'A-01')
            ->assertJsonPath('data.warehouse_id', $warehouse->id);

        $this->assertDatabaseHas('locations', [
            'warehouse_id' => $warehouse->id,
            'code' => 'A-01',
        ]);
    }

    public function test_duplicate_location_code_in_same_warehouse_is_rejected(): void
    {
        $user = User::factory()->create();

        $warehouse = Warehouse::factory()->create();

        Location::factory()->create([
            'warehouse_id' => $warehouse->id,
            'code' => 'A-01',
        ]);

        $this->actingAs($user)
            ->postJson("/api/warehouses/{$warehouse->id}/locations", [
                'code' => 'A-01',
                'name' => 'Another Rack',
                'status' => 'active',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);
    }

    public function test_same_location_code_is_allowed_in_different_warehouses(): void
    {
        $user = User::factory()->create();

        $warehouseOne = Warehouse::factory()->create();
        $warehouseTwo = Warehouse::factory()->create();

        Location::factory()->create([
            'warehouse_id' => $warehouseOne->id,
            'code' => 'A-01',
        ]);

        $this->actingAs($user)
            ->postJson("/api/warehouses/{$warehouseTwo->id}/locations", [
                'code' => 'A-01',
                'name' => 'Rack A - Shelf 01',
                'status' => 'active',
            ])
            ->assertCreated();

        $this->assertDatabaseCount('locations', 2);
    }

    public function test_locations_can_be_listed_for_warehouse(): void
    {
        $user = User::factory()->create();

        $warehouse = Warehouse::factory()->create();

        Location::factory()->count(3)->create([
            'warehouse_id' => $warehouse->id,
        ]);

        $this->actingAs($user)
            ->getJson("/api/warehouses/{$warehouse->id}/locations")
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_location_can_be_updated(): void
    {
        $user = User::factory()->create();

        $warehouse = Warehouse::factory()->create();

        $location = Location::factory()->create([
            'warehouse_id' => $warehouse->id,
            'code' => 'A-01',
            'name' => 'Old Location',
        ]);

        $this->actingAs($user)
            ->putJson("/api/locations/{$location->id}", [
                'code' => 'A-02',
                'name' => 'Updated Location',
                'status' => 'active',
            ])
            ->assertOk()
            ->assertJsonPath('data.code', 'A-02')
            ->assertJsonPath('data.name', 'Updated Location');

        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'code' => 'A-02',
            'name' => 'Updated Location',
        ]);
    }

    public function test_location_can_be_deleted_when_no_inventory_exists(): void
    {
        $user = User::factory()->create();

        $warehouse = Warehouse::factory()->create();

        $location = Location::factory()->create([
            'warehouse_id' => $warehouse->id,
        ]);

        $this->actingAs($user)
            ->deleteJson("/api/locations/{$location->id}")
            ->assertOk();

        $this->assertDatabaseMissing('locations', [
            'id' => $location->id,
        ]);
    }
}
