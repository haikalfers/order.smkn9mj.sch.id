<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_name',
        'category',
        'quantity',
        'unit',
        'unit_price',
        'subtotal',
        'description',
        'specifications',
        'design_file',
        'sort_order',
    ];

    protected $casts = [
        'specifications' => 'array', // auto JSON encode/decode
        'unit_price'     => 'decimal:2',
        'subtotal'       => 'decimal:2',
    ];

    // ─── Booted ───────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::saving(function (OrderItem $item) {
            $item->subtotal = $item->quantity * $item->unit_price;
        });
    }

    // ─── Relationships ────────────────────────────────────────────────

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
