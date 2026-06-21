<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class MonthlyAppointmentsChart extends ChartWidget
{
    protected static ?string $heading = 'Citas por mes';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(function ($i) {
            return now()->subMonths($i);
        });

        return [
            'datasets' => [
                [
                    'label' => 'Citas',
                    'data' => $months->map(function (Carbon $date) {
                        return Appointment::whereYear('appointment_date', $date->year)
                            ->whereMonth('appointment_date', $date->month)
                            ->count();
                    })->toArray(),
                ],
            ],
            'labels' => $months->map(fn(Carbon $date) => $date->translatedFormat('M Y'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}