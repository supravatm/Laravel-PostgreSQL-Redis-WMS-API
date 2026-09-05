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
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('location_id')
                ->constrained()
                ->restrictOnDelete();

            $table->unsignedBigInteger('quantity')->default(0);

            $table->timestamps();

            $table->unique(['product_id', 'location_id']);
        });

        DB::statement(
            'ALTER TABLE inventories ADD CONSTRAINT inventories_quantity_non_negative CHECK (quantity >= 0)'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
