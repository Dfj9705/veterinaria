<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Filament\Resources\AppointmentResource;
use App\Models\Appointment;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;


class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->validateScheduleConflict($data);

        return $data;
    }

    protected function validateScheduleConflict(array $data): void
    {
        $start = Carbon::parse($data['appointment_date'] . ' ' . $data['appointment_time']);
        $end = $start->copy()->addMinutes((int) $data['duration_minutes']);

        $hasConflict = Appointment::query()
            ->where('veterinarian_id', $data['veterinarian_id'])
            ->where('appointment_date', $data['appointment_date'])
            ->whereNotIn('status', ['Cancelada', 'No asistió'])
            ->get()
            ->contains(function (Appointment $appointment) use ($start, $end) {
                $existingStart = Carbon::parse(
                    $appointment->appointment_date->format('Y-m-d') . ' ' . $appointment->appointment_time
                );

                $existingEnd = $existingStart->copy()->addMinutes((int) $appointment->duration_minutes);

                return $start->lt($existingEnd) && $end->gt($existingStart);
            });

        if ($hasConflict) {
            throw ValidationException::withMessages([
                'data.appointment_time' => 'El veterinario ya tiene una cita asignada en ese horario.',
            ]);
        }
    }

    protected function afterCreate(): void
    {
        $users = $this->record->veterinarian;

        Notification::make()
            ->title('Cita Programada')
            ->body('Se ha programado una cita con ' . $this->record->customer->name . ' para el ' . $this->record->appointment_date->toDateString() . ' a las ' . $this->record->appointment_time . ' para el paciente ' . $this->record->pet->name)
            ->warning()
            ->sendToDatabase($users);
    }
}