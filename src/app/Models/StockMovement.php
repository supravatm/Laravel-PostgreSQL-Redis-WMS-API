<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'source_location_id',
        'destination_location_id',
        'quantity',
        'movement_type',
        'reference_number',
        'performed_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sourceLocation(): BelongsTo
    {
        return $this->belongsTo(
            Location::class,
            'source_location_id'
        );
    }

    public function destinationLocation(): BelongsTo
    {
        return $this->belongsTo(
            Location::class,
            'destination_location_id'
        );
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
