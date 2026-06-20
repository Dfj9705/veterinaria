<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentResource\Pages;
use App\Filament\Resources\AppointmentResource\RelationManagers;
use App\Models\Appointment;
use App\Models\ClinicalRecord;
use App\Models\Pet;
use App\Models\Service;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Agenda';

    protected static ?string $navigationLabel = 'Citas';

    protected static ?string $modelLabel = 'Cita';

    protected static ?string $pluralModelLabel = 'Citas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos de la cita')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->label('Cliente')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set) {
                                $set('pet_id', null);
                            }),

                        Forms\Components\Select::make('pet_id')
                            ->label('Mascota')
                            ->options(function (Forms\Get $get) {
                                $customerId = $get('customer_id');

                                if (!$customerId) {
                                    return [];
                                }

                                return Pet::query()
                                    ->where('customer_id', $customerId)
                                    ->where('status', 'Activo')
                                    ->orderBy('name')
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('service_id')
                            ->label('Servicio')
                            ->relationship('service', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                $service = Service::find($state);

                                if ($service) {
                                    $set('duration_minutes', $service->duration_minutes);
                                }
                            }),

                        Forms\Components\Select::make('veterinarian_id')
                            ->label('Veterinario')
                            ->options(function () {
                                return User::whereHas('roles', function ($query) {
                                    $query->where('name', 'Veterinario')
                                        ->orWhere('name', 'Administrador');
                                })
                                    ->orderBy('name')
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\DatePicker::make('appointment_date')
                            ->label('Fecha')
                            ->native(false)
                            ->default(fn() => request('appointment_date'))
                            ->required()
                            ->minDate(today()),

                        Forms\Components\TimePicker::make('appointment_time')
                            ->label('Hora')
                            ->seconds(false)
                            ->default(fn() => request('appointment_time'))
                            ->required(),

                        Forms\Components\TextInput::make('duration_minutes')
                            ->label('Duración en minutos')
                            ->numeric()
                            ->required()
                            ->minValue(5)
                            ->default(30),

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
                            ->default('Programada')
                            ->required(),
                    ]),

                Forms\Components\Section::make('Detalle')
                    ->schema([
                        Forms\Components\Textarea::make('reason')
                            ->label('Motivo de consulta')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notas internas')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('appointment_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('appointment_time')
                    ->label('Hora')
                    ->time('H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pet.name')
                    ->label('Mascota')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('service.name')
                    ->label('Servicio')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('veterinarian.name')
                    ->label('Veterinario')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Duración')
                    ->suffix(' min')
                    ->sortable(),
                Tables\Columns\IconColumn::make('clinicalRecord.id')
                    ->label('Historial')
                    ->boolean(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Programada' => 'gray',
                        'Confirmada' => 'info',
                        'En atención' => 'warning',
                        'Finalizada' => 'success',
                        'Cancelada' => 'danger',
                        'No asistió' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
            ])
            ->defaultSort('appointment_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'Programada' => 'Programada',
                        'Confirmada' => 'Confirmada',
                        'En atención' => 'En atención',
                        'Finalizada' => 'Finalizada',
                        'Cancelada' => 'Cancelada',
                        'No asistió' => 'No asistió',
                    ]),

                Tables\Filters\SelectFilter::make('veterinarian_id')
                    ->label('Veterinario')
                    ->relationship('veterinarian', 'name'),

                Tables\Filters\Filter::make('appointment_date')
                    ->label('Fecha')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Desde')
                            ->native(false),

                        Forms\Components\DatePicker::make('until')
                            ->label('Hasta')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('appointment_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('appointment_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()->visible(fn($record) => $record->status !== 'Finalizada' && $record->status !== 'Cancelada' && $record->status !== 'No asistió'),
                Tables\Actions\Action::make('start_attention')
                    ->label('Iniciar atención')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('success')
                    ->visible(fn($record) => !ClinicalRecord::where('appointment_id', $record->id)->exists() && $record->status !== 'Cancelada' && $record->status !== 'No asistió')
                    ->url(fn($record) => route('filament.admin.resources.clinical-records.create', [
                        'appointment_id' => $record->id,
                        'pet_id' => $record->pet_id,
                        'veterinarian_id' => $record->veterinarian_id,
                    ])),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
