<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
class InventoryMovement extends Model
{
    protected $fillable = [
        'product_id',
        'product_batch_id',
        'user_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'reference',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'stock_before' => 'decimal:2',
        'stock_after' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function batch()
    {
        return $this->belongsTo(ProductBatch::class, 'product_batch_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        static::creating(function (InventoryMovement $movement) {
            $product = $movement->product;

            if (!$product) {
                throw ValidationException::withMessages([
                    'product_id' => 'Debe seleccionar un producto válido.',
                ]);
            }

            $stockBefore = $product->current_stock;
            $quantity = $movement->quantity;

            $stockAfter = match ($movement->type) {
                'Entrada', 'Ajuste +' => $stockBefore + $quantity,
                'Salida', 'Ajuste -' => $stockBefore - $quantity,
                default => $stockBefore,
            };

            if ($stockAfter < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'No hay suficiente existencia para realizar este movimiento.',
                ]);
            }

            $movement->stock_before = $stockBefore;
            $movement->stock_after = $stockAfter;
            $movement->user_id = Auth::id();

            $product->update([
                'current_stock' => $stockAfter,
            ]);
        });

        static::deleting(function (InventoryMovement $movement) {
            $product = $movement->product;
            $stockBefore = $movement->stock_before;
            $quantity = $movement->quantity;
            $stockAfter = match ($movement->type) {
                'Entrada', 'Ajuste +' => $stockBefore - $quantity,
                'Salida', 'Ajuste -' => $stockBefore + $quantity,
                default => $stockBefore,
            };
            $product->update([
                'current_stock' => $stockAfter,
            ]);
        });
    }
}
