<?php

namespace App\Filament\Resources\PetResource\RelationManagers;

use App\Models\Appointment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ClinicalRecordsRelationManager extends RelationManager
{
    protected static string $relationship = 'clinicalRecords';

    protected static ?string $title = 'Historial clínico';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('pet_id'),

                Forms\Components\Select::make('appointment_id')
                    ->label('Cita relacionada')
                    ->options(function () {
                        $petId = $this->getOwnerRecord()->id;

                        return Appointment::query()
                            ->with(['service', 'assignedUser'])
                            ->where('pet_id', $petId)
                            ->orderByDesc('appointment_date')
                            ->get()
                            ->mapWithKeys(fn($appointment) => [
                                $appointment->id =>
                                    $appointment->appointment_date->format('d/m/Y H:i')
                                    . ' | '
                                    . ($appointment->service?->name ?? 'Sin servicio')
                                    . ' | '
                                    . ($appointment->assignedUser?->name ?? 'Sin veterinario')
                                    . ' | '
                                    . $appointment->status,
                            ]);
                    })
                    ->searchable()
                    ->preload()
                    ->placeholder('Seleccione una cita de esta mascota'),

                Forms\Components\Select::make('veterinarian_id')
                    ->label('Responsable')
                    ->relationship('assignedUser', 'name')
                    ->searchable()
                    ->preload()
                    ->default(auth()->id())
                    ->required(),

                Forms\Components\DateTimePicker::make('consultation_date')
                    ->label('Fecha de consulta')
                    ->seconds(false)
                    ->default(now())
                    ->required(),

                Forms\Components\Textarea::make('chief_complaint')
                    ->label('Motivo de consulta')
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('symptoms')
                    ->label('Síntomas')
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('diagnosis')
                    ->label('Diagnóstico')
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('weight')
                    ->label('Peso (kg)')
                    ->numeric()
                    ->step(0.01),

                Forms\Components\TextInput::make('temperature')
                    ->label('Temperatura °C')
                    ->numeric()
                    ->step(0.1),

                Forms\Components\Textarea::make('treatment')
                    ->label('Tratamiento')
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('observations')
                    ->label('Observaciones')
                    ->columnSpanFull(),

                Forms\Components\DatePicker::make('next_control_date')
                    ->label('Próximo control'),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('consultation_date')
            ->columns([
                Tables\Columns\TextColumn::make('consultation_date')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->description(fn($record) => $record->appointment?->service?->name ?? 'Atención médica'),

                Tables\Columns\TextColumn::make('assignedUser.name')
                    ->label('Veterinario')
                    ->searchable(),

                Tables\Columns\TextColumn::make('chief_complaint')
                    ->label('Motivo')
                    ->limit(40),

                Tables\Columns\TextColumn::make('diagnosis')
                    ->label('Diagnóstico')
                    ->limit(50),

                Tables\Columns\TextColumn::make('weight')
                    ->label('Peso')
                    ->suffix(' kg'),

                Tables\Columns\TextColumn::make('temperature')
                    ->label('Temp.')
                    ->suffix(' °C'),

                Tables\Columns\TextColumn::make('next_control_date')
                    ->label('Próximo control')
                    ->date('d/m/Y')
                    ->placeholder('No definido'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Agregar atención'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('consultation_date', 'desc');
    }
}