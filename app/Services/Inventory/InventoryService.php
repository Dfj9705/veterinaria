<?php

namespace App\Services\Inventory;

use App\Models\InventoryMovement;
use App\Models\ProductBatch;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function applyMovement(InventoryMovement $movement): void
    {
        $product = $movement->product;

        $product->update([
            'current_stock' => $movement->stock_after,
        ]);

        if ($movement->product_batch_id) {

            $batch = $movement->batch;

            $quantity = match ($movement->type) {
                'Entrada', 'Ajuste +' => $batch->quantity + $movement->quantity,
                'Salida', 'Ajuste -' => $batch->quantity - $movement->quantity,
            };

            if ($quantity < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'El lote seleccionado no tiene suficiente existencia.',
                ]);
            }

            $batch->update([
                'quantity' => $quantity,
            ]);
        }
    }
}