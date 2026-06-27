<?php

namespace App\Filament\Resources\PrescriptionResource\Pages;

use App\Filament\Resources\PrescriptionResource;
use App\Models\ClinicalRecord;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePrescription extends CreateRecord
{
    protected static string $resource = PrescriptionResource::class;

    public function mount(): void
    {
        parent::mount();

        $clinicalRecordId = request()->integer('clinical_record_id');

        if (!$clinicalRecordId) {
            return;
        }

        $record = ClinicalRecord::with([
            'pet.customer',
        ])->find($clinicalRecordId);

        if (!$record) {
            return;
        }

        $this->form->fill([
            'clinical_record_id' => $record->id,
            'pet_id' => $record->pet_id,
            'customer_id' => $record->pet->customer_id,
            'assigned_user_id' => $record->assigned_user_id,
        ]);
    }
}
