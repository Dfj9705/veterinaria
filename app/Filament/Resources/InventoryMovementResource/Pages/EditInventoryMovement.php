<?php

namespace App\Filament\Resources\InventoryMovementResource\Pages;

use App\Filament\Resources\InventoryMovementResource;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\User;
use App\Services\Inventory\InventoryService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditInventoryMovement extends EditRecord
{
    protected static string $resource = InventoryMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
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

        if ($stockAfter <= $product->minimum_stock) {
            $users = User::role(['Administrador', 'Veterinario'])->get();

            Notification::make()
                ->title('Stock Bajo')
                ->body('El stock de ' . $product->name . ' está por debajo o igual al mínimo.')
                ->warning()
                ->sendToDatabase($users);
        }

        $data['stock_before'] = $stockBefore;
        $data['stock_after'] = $stockAfter;
        $data['user_id'] = auth()->id();

        return $data;
    }

    protected function afterSave(): void
    {
        app(InventoryService::class)
            ->applyMovement($this->record);
    }
}
