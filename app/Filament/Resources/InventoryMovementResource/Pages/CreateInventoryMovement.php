<?php

namespace App\Filament\Resources\InventoryMovementResource\Pages;

use App\Filament\Resources\InventoryMovementResource;
use App\Models\ProductBatch;
use App\Services\Inventory\InventoryService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use App\Models\Product;

class CreateInventoryMovement extends CreateRecord
{
    protected static string $resource = InventoryMovementResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $product = Product::find($data['product_id']);

        if (!$product) {
            Notification::make()
                ->title('Error')
                ->body('Debe seleccionar un producto válido.')
                ->danger()
                ->send();
            return $data;
        }

        $stockBefore = $product->current_stock;
        $quantity = $data['quantity'];

        $stockAfter = match ($data['type']) {
            'Entrada', 'Ajuste +' => $stockBefore + $quantity,
            'Salida', 'Ajuste -' => $stockBefore - $quantity,
            default => $stockBefore,
        };

        if (
            $product->uses_batches
            && in_array($data['type'], ['Salida', 'Ajuste -'])
            && empty($data['product_batch_id'])
        ) {
            Notification::make()
                ->title('Error')
                ->body('Debe seleccionar el lote para realizar esta salida.')
                ->danger()
                ->send();
            return $data;
        }

        if (!empty($data['product_batch_id'])) {
            $batch = ProductBatch::find($data['product_batch_id']);

            if (!$batch || $batch->product_id != $product->id) {
                Notification::make()
                    ->title('Error')
                    ->body('El lote seleccionado no pertenece al producto.')
                    ->danger()
                    ->send();
                return $data;
            }

            if (in_array($data['type'], ['Salida', 'Ajuste -']) && $batch->quantity < $quantity) {
                Notification::make()
                    ->title('Error')
                    ->body('El lote seleccionado no tiene suficiente existencia.')
                    ->danger()
                    ->send();
                return $data;
            }
        }

        if ($stockAfter < 0) {
            Notification::make()
                ->title('Error')
                ->body('No hay suficiente existencia para realizar este movimiento.')
                ->danger()
                ->send();
            return $data;
        }

        $data['stock_before'] = $stockBefore;
        $data['stock_after'] = $stockAfter;
        $data['user_id'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        app(InventoryService::class)
            ->applyMovement($this->record);
    }
}
