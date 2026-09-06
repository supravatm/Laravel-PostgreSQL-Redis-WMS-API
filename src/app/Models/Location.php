<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'name',
        'code',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function sourceStockMovements(): HasMany
    {
        return $this->hasMany(
            StockMovement::class,
            'source_location_id'
        );
    }

    public function destinationStockMovements(): HasMany
    {
        return $this->hasMany(
            StockMovement::class,
            'destination_location_id'
        );
    }
}
