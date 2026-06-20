<?php

namespace App\Filament\Resources\ClinicalRecordResource\Pages;

use App\Filament\Resources\ClinicalRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClinicalRecords extends ListRecords
{
    protected static string $resource = ClinicalRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
