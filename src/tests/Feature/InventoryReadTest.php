<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_view_inventory(): void
    {
        $this->getJson('/api/inventory')
            ->assertUnauthorized();
    }

    public function test_authenticated_user_can_view_inventory(): void
    {
        $user = User::factory()->create();

        $warehouse = Warehouse::factory()->create();
        $location = Location::factory()->create([
            'warehouse_id' => $warehouse->id,
        ]);
        $product = Product::factory()->create();

        Inventory::factory()->create([
            'product_id' => $product->id,
            'location_id' => $location->id,
            'quantity' => 100,
        ]);

        $this->actingAs($user)
            ->getJson('/api/inventory')
            ->assertOk()
            ->assertJsonPath('data.0.quantity', 100)
            ->assertJsonPath(
                'data.0.product_id',
                $product->id
            )
            ->assertJsonPath(
                'data.0.location_id',
                $location->id
            );
    }

    public function test_inventory_can_be_filtered_by_warehouse(): void
    {
        $user = User::factory()->create();

        $warehouse1 = Warehouse::factory()->create();
        $warehouse2 = Warehouse::factory()->create();

        $location1 = Location::factory()->create([
            'warehouse_id' => $warehouse1->id,
        ]);

        $location2 = Location::factory()->create([
            'warehouse_id' => $warehouse2->id,
        ]);

        $product = Product::factory()->create();

        Inventory::factory()->create([
            'product_id' => $product->id,
            'location_id' => $location1->id,
            'quantity' => 50,
        ]);

        Inventory::factory()->create([
            'product_id' => $product->id,
            'location_id' => $location2->id,
            'quantity' => 100,
        ]);

        $this->actingAs($user)
            ->getJson(
                "/api/inventory?warehouse_id={$warehouse1->id}"
            )
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.quantity', 50);
    }

    public function test_inventory_can_be_filtered_by_product(): void
    {
        $user = User::factory()->create();

        $warehouse = Warehouse::factory()->create();
        $location = Location::factory()->create([
            'warehouse_id' => $warehouse->id,
        ]);

        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        Inventory::factory()->create([
            'product_id' => $product1->id,
            'location_id' => $location->id,
            'quantity' => 25,
        ]);

        Inventory::factory()->create([
            'product_id' => $product2->id,
            'location_id' => $location->id,
            'quantity' => 75,
        ]);

        $this->actingAs($user)
            ->getJson(
                "/api/inventory?product_id={$product1->id}"
            )
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.quantity', 25);
    }
}
