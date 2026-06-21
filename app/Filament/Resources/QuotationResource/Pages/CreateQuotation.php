<?php

namespace App\Filament\Resources\QuotationResource\Pages;

use App\Filament\Resources\QuotationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateQuotation extends CreateRecord
{
    protected static string $resource = QuotationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['subtotal'] = $data['subtotal'] ?? 0;
        $data['total'] = $data['total'] ?? 0;
        $data['discount'] = $data['discount'] ?? 0;
        return $data;
    }

    protected function afterCreate(): void
    {
        $this->recalculateTotals();
    }

    protected function recalculateTotals(): void
    {
        $subtotal = $this->record->items()->sum('subtotal');
        $discount = (float) $this->record->discount;

        $this->record->update([
            'subtotal' => $subtotal,
            'total' => max($subtotal - $discount, 0),
        ]);
    }
}