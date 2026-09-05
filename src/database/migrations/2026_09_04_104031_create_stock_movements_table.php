<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('source_location_id')
                ->nullable()
                ->constrained('locations')
                ->restrictOnDelete();

            $table->foreignId('destination_location_id')
                ->nullable()
                ->constrained('locations')
                ->restrictOnDelete();

            $table->unsignedBigInteger('quantity');

            $table->string('movement_type');

            $table->string('reference_number')->unique();

            $table->foreignId('performed_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamps();

            $table->index(['product_id', 'created_at']);
            $table->index(['movement_type', 'created_at']);
            $table->index(['source_location_id', 'created_at']);
            $table->index(['destination_location_id', 'created_at']);
        });
        DB::statement(
            'ALTER TABLE stock_movements ADD CONSTRAINT stock_movements_quantity_positive CHECK (quantity > 0)'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
