<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UpcomingAppointments extends BaseWidget
{
    protected static ?string $heading = 'Próximas citas';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Appointment::query()
                    ->with(['customer', 'pet', 'service', 'assignedUser'])
                    ->whereDate('appointment_date', '>=', today())
                    ->whereIn('status', [
                        'Programada',
                        'Confirmada',
                    ])
                    ->orderBy('appointment_date')
                    ->orderBy('appointment_time')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('appointment_date')
                    ->label('Fecha')
                    ->date('d/m/Y'),

                Tables\Columns\TextColumn::make('appointment_time')
                    ->label('Hora'),

                Tables\Columns\TextColumn::make('pet.name')
                    ->label('Mascota')
                    ->searchable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable(),

                Tables\Columns\TextColumn::make('service.name')
                    ->label('Servicio'),

                Tables\Columns\TextColumn::make('assignedUser.name')
                    ->label('Responsable'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'Programada' => 'warning',
                        'Confirmada' => 'success',
                        default => 'gray',
                    }),
            ]);
    }
}