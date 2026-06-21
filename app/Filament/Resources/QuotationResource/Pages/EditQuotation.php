<?php

namespace App\Filament\Resources\QuotationResource\Pages;

use App\Filament\Resources\QuotationResource;
use App\Models\Quotation;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQuotation extends EditRecord
{
    protected static string $resource = QuotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('sendAndPrint')
                ->label('Enviar e imprimir')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->action(function (Quotation $record) {

                    if ($record->status === 'Borrador') {
                        $record->update([
                            'status' => 'Enviada',
                        ]);
                    }

                    redirect()->away(
                        route('quotations.pdf', $record)
                    );
                })->visible(fn() => auth()->user()->can('print_quotation'))
                ->successNotificationTitle('Cotización enviada'),
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
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