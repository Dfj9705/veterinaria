<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClinicalRecordResource\Pages;
use App\Filament\Resources\ClinicalRecordResource\RelationManagers;
use App\Models\ClinicalRecord;
use Auth;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Appointment;
use App\Models\Pet;
use App\Models\User;

class ClinicalRecordResource extends Resource
{
    protected static ?string $model = ClinicalRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Atención Médica';

    protected static ?string $navigationLabel = 'Historial clínico';

    protected static ?string $modelLabel = 'Historial clínico';

    protected static ?string $pluralModelLabel = 'Historiales clínicos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información general')
                    ->schema([

                        Forms\Components\Select::make('pet_id')
                            ->label('Mascota')
                            ->options(
                                Pet::query()
                                    ->with('customer')
                                    ->get()
                                    ->mapWithKeys(fn($pet) => [
                                        $pet->id => "{$pet->name} - {$pet->customer->name}",
                                    ])
                            )
                            ->searchable()
                            ->preload()
                            ->default(fn() => request()->get('pet_id'))
                            ->live()
                            ->afterStateUpdated(fn(Set $set) => $set('appointment_id', null))
                            ->required(),

                        Forms\Components\Select::make('appointment_id')
                            ->label('Cita relacionada')
                            ->options(function (Get $get) {
                                $petId = $get('pet_id');

                                if (!$petId) {
                                    return [];
                                }

                                return Appointment::query()
                                    ->with(['pet', 'customer', 'service'])
                                    ->where('pet_id', $petId)
                                    ->orderByDesc('appointment_date')
                                    ->get()
                                    ->mapWithKeys(fn($appointment) => [
                                        $appointment->id =>
                                            $appointment->appointment_date->format('d/m/Y H:i')
                                            . ' | '
                                            . $appointment->pet->name
                                            . ' | '
                                            . $appointment->service->name,
                                    ]);
                            })
                            ->searchable()
                            ->preload()
                            ->disabled(fn(Get $get) => blank($get('pet_id')))
                            ->default(fn() => request()->get('appointment_id'))
                            ->placeholder('Seleccione primero una mascota'),

                        Forms\Components\Select::make('veterinarian_id')
                            ->label('Responsable')
                            ->relationship('assignedUser', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(fn() => request()->get('assigned_user_id') ?? auth()->id())
                        ,
                        Forms\Components\DateTimePicker::make('consultation_date')
                            ->label('Fecha de consulta')
                            ->seconds(false)
                            ->required()
                            ->default(now()),
                        Forms\Components\Placeholder::make('pet_info')
                            ->label('Información del paciente')
                            ->content(function (Get $get) {

                                $pet = \App\Models\Pet::find($get('pet_id'));

                                if (!$pet) {
                                    return 'Seleccione una mascota';
                                }

                                $age = $pet->birth_date
                                    ? $pet->birth_date->age . ' años'
                                    : 'No registrada';

                                return "Especie: {$pet->species->name} | Edad: {$age} | Peso actual: {$pet->weight} kg";
                            }),
                        Forms\Components\Placeholder::make('medical_alerts')
                            ->label('Alertas médicas')
                            ->content(function (Get $get) {

                                $pet = \App\Models\Pet::find($get('pet_id'));

                                if (!$pet) {
                                    return 'Seleccione una mascota';
                                }

                                return $pet->allergies
                                    ? "⚠️ Alergias registradas: {$pet->allergies}"
                                    : 'Sin alergias registradas';
                            }),

                    ])
                    ->columns(2),

                Forms\Components\Section::make('Consulta')
                    ->schema([

                        Forms\Components\Textarea::make('chief_complaint')
                            ->label('Motivo de consulta')
                            ->rows(3),

                        Forms\Components\Textarea::make('symptoms')
                            ->label('Síntomas')
                            ->rows(4),

                        Forms\Components\Textarea::make('diagnosis')
                            ->label('Diagnóstico')
                            ->rows(4),

                    ]),

                Forms\Components\Section::make('Signos clínicos')
                    ->schema([

                        Forms\Components\TextInput::make('weight')
                            ->label('Peso (kg)')
                            ->numeric()
                            ->step(0.01),

                        Forms\Components\TextInput::make('temperature')
                            ->label('Temperatura °C')
                            ->numeric()
                            ->step(0.1),

                    ])
                    ->columns(2),

                Forms\Components\Section::make('Tratamiento')
                    ->schema([

                        Forms\Components\Textarea::make('treatment')
                            ->label('Tratamiento')
                            ->rows(5),

                        Forms\Components\Textarea::make('observations')
                            ->label('Observaciones')
                            ->rows(5),

                        Forms\Components\DatePicker::make('next_control_date')
                            ->label('Próximo control'),

                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('consultation_date')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pet.name')
                    ->label('Mascota')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pet.customer.name')
                    ->label('Dueño')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('assignedUser.name')
                    ->label('Veterinario')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('diagnosis')
                    ->label('Diagnóstico')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('next_control_date')
                    ->label('Próximo control')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('No definido'),
                Tables\Columns\TextColumn::make('prescriptions_count')
                    ->counts('prescriptions')
                    ->label('Recetas'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('pet_id')
                    ->label('Mascota')
                    ->relationship('pet', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('assigned_user_id')
                    ->label('Responsable')
                    ->relationship('assignedUser', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('consultation_date')
                    ->label('Fecha de consulta')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Hasta'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['from'],
                                fn($query) => $query->whereDate('consultation_date', '>=', $data['from'])
                            )
                            ->when(
                                $data['until'],
                                fn($query) => $query->whereDate('consultation_date', '<=', $data['until'])
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('prescription')
                    ->label('Receta')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->url(fn($record) => route(
                        'filament.admin.resources.prescriptions.create',
                        [
                            'clinical_record_id' => $record->id,
                        ]
                    )),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('consultation_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClinicalRecords::route('/'),
            'create' => Pages\CreateClinicalRecord::route('/create'),
            'edit' => Pages\EditClinicalRecord::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('view_medical_records') ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_medical_records') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_medical_records') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_medical_records') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete_medical_records') ?? false;
    }
}
