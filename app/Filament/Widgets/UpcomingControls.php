<?php

namespace App\Filament\Widgets;

use App\Models\ClinicalRecord;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UpcomingControls extends BaseWidget
{
    protected static ?string $heading = 'Pacientes con próximo control';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ClinicalRecord::query()
                    ->with(['pet.customer', 'assignedUser'])
                    ->whereNotNull('next_control_date')
                    ->whereDate('next_control_date', '>=', today())
                    ->orderBy('next_control_date')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('next_control_date')
                    ->label('Fecha control')
                    ->date('d/m/Y'),

                Tables\Columns\TextColumn::make('pet.name')
                    ->label('Mascota'),

                Tables\Columns\TextColumn::make('pet.customer.name')
                    ->label('Cliente'),

                Tables\Columns\TextColumn::make('assignedUser.name')
                    ->label('Responsable'),

                Tables\Columns\TextColumn::make('diagnosis')
                    ->label('Diagnóstico')
                    ->limit(40),
            ]);
    }
}