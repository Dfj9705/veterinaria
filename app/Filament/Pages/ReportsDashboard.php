<?php

namespace App\Filament\Pages;

use App\Models\Appointment;
use App\Models\ClinicalRecord;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class ReportsDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static string $view = 'filament.pages.reports-dashboard';

    protected static ?string $navigationGroup = 'Reportes';

    protected static ?string $navigationLabel = 'Dashboard de reportes';

    protected static ?string $title = 'Dashboard de reportes';

    protected static ?int $navigationSort = 1;

    public function getStats(): array
    {
        return [
            'appointments_today' => Appointment::query()
                ->whereDate('appointment_date', today())
                ->count(),

            'finished_today' => Appointment::query()
                ->whereDate('appointment_date', today())
                ->where('status', 'Finalizada')
                ->count(),

            'patients_attended_month' => ClinicalRecord::query()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->distinct('pet_id')
                ->count('pet_id'),

            'next_controls' => ClinicalRecord::query()
                ->whereNotNull('next_control_date')
                ->whereDate('next_control_date', '>=', today())
                ->whereDate('next_control_date', '<=', today()->addDays(7))
                ->count(),
        ];
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