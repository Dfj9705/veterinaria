<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\ClinicalRecord;
use App\Models\Customer;
use App\Models\Pet;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Pacientes activos', Pet::where('status', 'Activo')->count())
                ->icon('heroicon-o-heart')
                ->color('success'),

            Stat::make('Clientes registrados', Customer::count())
                ->icon('heroicon-o-users')
                ->color('info'),

            Stat::make('Citas hoy', Appointment::whereDate('appointment_date', today())->count())
                ->icon('heroicon-o-calendar-days')
                ->color('warning'),

            Stat::make('Próximos controles', ClinicalRecord::whereNotNull('next_control_date')
                ->whereDate('next_control_date', '>=', today())
                ->count())
                ->icon('heroicon-o-clock')
                ->color('primary'),
        ];
    }
}