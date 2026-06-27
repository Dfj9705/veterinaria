<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Filament\Resources\AppointmentResource;
use App\Models\Appointment;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditAppointment extends EditRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->validateScheduleConflict($data);

        return $data;
    }

    protected function validateScheduleConflict(array $data): void
    {
        $start = Carbon::parse($data['appointment_date'] . ' ' . $data['appointment_time']);
        $end = $start->copy()->addMinutes((int) $data['duration_minutes']);

        $hasConflict = Appointment::query()
            ->where('assigned_user_id', $data['assigned_user_id'])
            ->where('appointment_date', $data['appointment_date'])
            ->where('id', '!=', $this->record->id)
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

    protected function afterSave(): void
    {
        $users = $this->record->assignedUser;

        Notification::make()
            ->title('Cita Modificada')
            ->body('Se ha modificado una cita con ' . $this->record->customer->name . ' para el ' . $this->record->appointment_date->toDateString() . ' a las ' . $this->record->appointment_time . ' para el paciente ' . $this->record->pet->name)
            ->warning()
            ->sendToDatabase($users);
    }
}