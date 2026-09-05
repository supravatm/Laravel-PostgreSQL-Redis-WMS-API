<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_products(): void
    {
        $this->getJson('/api/products')
            ->assertUnauthorized();
    }

    public function test_authenticated_user_can_create_product(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/products', [
                'sku' => 'SKU-1001',
                'name' => 'Keyboard',
                'description' => 'Mechanical keyboard',
                'unit' => 'pcs',
                'status' => 'active',
                'low_stock_threshold' => 10,
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath(
                'data.sku',
                'SKU-1001'
            );

        $this->assertDatabaseHas('products', [
            'sku' => 'SKU-1001',
            'name' => 'Keyboard',
        ]);
    }

    public function test_duplicate_sku_is_rejected(): void
    {
        $user = User::factory()->create();

        Product::factory()->create([
            'sku' => 'SKU-1001',
        ]);

        $this->actingAs($user)
            ->postJson('/api/products', [
                'sku' => 'SKU-1001',
                'name' => 'Another Product',
                'description' => null,
                'unit' => 'pcs',
                'status' => 'active',
                'low_stock_threshold' => 10,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'sku',
            ]);
    }

    public function test_product_can_be_updated(): void
    {
        $user = User::factory()->create();

        $product = Product::factory()->create([
            'sku' => 'SKU-1001',
        ]);

        $this->actingAs($user)
            ->putJson("/api/products/{$product->id}", [
                'sku' => 'SKU-1001',
                'name' => 'Updated Product',
                'description' => 'Updated',
                'unit' => 'pcs',
                'status' => 'inactive',
                'low_stock_threshold' => 5,
            ])
            ->assertOk();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
            'status' => 'inactive',
        ]);
    }
}
