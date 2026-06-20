<?php

namespace App\Filament\Resources\PrescriptionResource\Pages;

use App\Filament\Resources\PrescriptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPrescription extends ViewRecord
{
    protected static string $resource = PrescriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('pdf')
                ->label('Generar PDF')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn() => route('prescriptions.pdf', $this->record))
                ->openUrlInNewTab(),

            Actions\EditAction::make(),
        ];
    }
}
