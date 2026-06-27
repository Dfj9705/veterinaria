<?php

namespace App\Filament\Pages;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;

class AppointmentsReport extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static string $view = 'filament.pages.appointments-report';

    protected static ?string $navigationGroup = 'Reportes';

    protected static ?string $navigationLabel = 'Citas por período';

    protected static ?string $title = 'Reporte de citas por período';

    protected static ?int $navigationSort = 2;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'from' => now()->startOfMonth()->toDateString(),
            'to' => now()->toDateString(),
            'status' => null,
            'service_id' => null,
            'assigned_user_id' => null,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('from')
                    ->label('Desde')
                    ->required()
                    ->live(),

                Forms\Components\DatePicker::make('to')
                    ->label('Hasta')
                    ->required()
                    ->live(),

                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->options([
                        'Programada' => 'Programada',
                        'Confirmada' => 'Confirmada',
                        'En atención' => 'En atención',
                        'Finalizada' => 'Finalizada',
                        'Cancelada' => 'Cancelada',
                        'No asistió' => 'No asistió',
                    ])
                    ->searchable()
                    ->native(false)
                    ->live(),

                Forms\Components\Select::make('service_id')
                    ->label('Servicio')
                    ->options(fn() => Service::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->live(),

                Forms\Components\Select::make('assigned_user_id')
                    ->label('Responsable')
                    ->options(fn() => User::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->live(),
            ])
            ->columns(3)
            ->statePath('data');
    }

    public function getAppointments()
    {
        $filters = $this->form->getState();

        return Appointment::query()
            ->with([
                'customer',
                'pet',
                'service',
                'assignedUser',
            ])
            ->when($filters['from'] ?? null, function (Builder $query, $date) {
                $query->whereDate('appointment_date', '>=', $date);
            })
            ->when($filters['to'] ?? null, function (Builder $query, $date) {
                $query->whereDate('appointment_date', '<=', $date);
            })
            ->when($filters['status'] ?? null, function (Builder $query, $status) {
                $query->where('status', $status);
            })
            ->when($filters['service_id'] ?? null, function (Builder $query, $serviceId) {
                $query->where('service_id', $serviceId);
            })
            ->when($filters['assigned_user_id'] ?? null, function (Builder $query, $assignedUserId) {
                $query->where('assigned_user_id', $assignedUserId);
            })
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get();
    }

    public function generatePdf()
    {
        $filters = $this->form->getState();

        return redirect()->route('reports.appointments.pdf', [
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
            'status' => $filters['status'] ?? null,
            'service_id' => $filters['service_id'] ?? null,
            'assigned_user_id' => $filters['assigned_user_id'] ?? null,
        ]);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('view_reports') ?? false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_reports') ?? false;
    }
}