<?php

namespace App\Filament\Pages;

use App\Filament\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\User;
use Filament\Pages\Page;

class AppointmentCalendar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static string $view = 'filament.pages.appointment-calendar';

    protected static ?string $navigationGroup = 'Agenda';

    protected static ?string $navigationLabel = 'Agenda';

    protected static ?string $title = 'Agenda de citas';

    public array $events = [];

    public array $veterinarians = [];

    public string $createUrl;

    public function mount(): void
    {
        $this->createUrl = AppointmentResource::getUrl('create');

        $this->veterinarians = User::query()
            ->orderBy('name')
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['Veterinario', 'Groomer', 'Administrador']);
            })
            ->pluck('name', 'id')
            ->toArray();

        $this->events = Appointment::query()
            ->with(['customer', 'pet', 'service', 'assignedUser'])
            ->get()
            ->map(function (Appointment $appointment) {
                $start = $appointment->appointment_date->format('Y-m-d') . 'T' . $appointment->appointment_time;

                $end = \Carbon\Carbon::parse($start)
                    ->addMinutes($appointment->duration_minutes)
                    ->format('Y-m-d\TH:i:s');

                return [
                    'id' => $appointment->id,
                    'title' => $appointment->appointment_time . ' - ' . $appointment->pet->name,
                    'start' => $start,
                    'end' => $end,
                    'url' => AppointmentResource::getUrl('edit', ['record' => $appointment]),
                    'backgroundColor' => match ($appointment->status) {
                        'Programada' => '#6b7280',
                        'Confirmada' => '#3b82f6',
                        'En atención' => '#f59e0b',
                        'Finalizada' => '#10b981',
                        'Cancelada' => '#ef4444',
                        'No asistió' => '#991b1b',
                        default => '#6b7280',
                    },
                    'borderColor' => match ($appointment->status) {
                        'Programada' => '#6b7280',
                        'Confirmada' => '#3b82f6',
                        'En atención' => '#f59e0b',
                        'Finalizada' => '#10b981',
                        'Cancelada' => '#ef4444',
                        'No asistió' => '#991b1b',
                        default => '#6b7280',
                    },
                    'extendedProps' => [
                        'assigned_user_id' => $appointment->assigned_user_id,
                        'cliente' => $appointment->customer->name,
                        'mascota' => $appointment->pet->name,
                        'servicio' => $appointment->service->name,
                        'veterinario' => $appointment->assignedUser->name,
                        'estado' => $appointment->status,
                    ],
                ];
            })
            ->toArray();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('view_appointments') ?? false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_appointments') ?? false;
    }
}