<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrescriptionResource\Pages;
use App\Models\ClinicalRecord;
use App\Models\Prescription;
use App\Models\Pet;
use App\Models\Customer;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PrescriptionResource extends Resource
{
    protected static ?string $model = Prescription::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Atención Médica';
    protected static ?string $navigationLabel = 'Recetas médicas';
    protected static ?string $modelLabel = 'Receta médica';
    protected static ?string $pluralModelLabel = 'Recetas médicas';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Datos de la receta')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('prescription_number')
                        ->label('No. receta')
                        ->disabled()
                        ->dehydrated(false),

                    Forms\Components\Select::make('clinical_record_id')
                        ->label('Historial clínico')
                        ->options(
                            ClinicalRecord::query()
                                ->with(['pet.customer', 'veterinarian'])
                                ->latest()
                                ->get()
                                ->mapWithKeys(fn($record) => [
                                    $record->id => '#' . $record->id . ' - ' .
                                        $record->pet?->name . ' / ' .
                                        $record->pet?->customer?->name . ' - ' .
                                        $record->created_at?->format('d/m/Y H:i'),
                                ])
                        )
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            $record = ClinicalRecord::with(['pet.customer'])->find($state);

                            if (!$record) {
                                return;
                            }

                            $set('pet_id', $record->pet_id);
                            $set('customer_id', $record->pet?->customer_id);
                            $set('veterinarian_id', $record->veterinarian_id);
                        }),

                    Forms\Components\Select::make('pet_id')
                        ->label('Mascota')
                        ->options(
                            Pet::query()
                                ->with('customer')
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn($pet) => [
                                    $pet->id => $pet->name . ' - Dueño: ' . $pet->customer?->name,
                                ])
                        )
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Select::make('customer_id')
                        ->label('Cliente')
                        ->options(Customer::query()->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Select::make('veterinarian_id')
                        ->label('Veterinario')
                        ->options(User::query()->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Textarea::make('instructions')
                        ->label('Indicaciones generales')
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Medicamentos')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->label('Medicamentos indicados')
                        ->relationship()
                        ->schema([
                            Forms\Components\TextInput::make('medication')
                                ->label('Medicamento')
                                ->required(),

                            Forms\Components\TextInput::make('dosage')
                                ->label('Dosis')
                                ->required(),

                            Forms\Components\TextInput::make('frequency')
                                ->label('Frecuencia')
                                ->required(),

                            Forms\Components\TextInput::make('duration')
                                ->label('Duración')
                                ->required(),

                            Forms\Components\Textarea::make('notes')
                                ->label('Observaciones')
                                ->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->defaultItems(1)
                        ->addActionLabel('Agregar medicamento')
                        ->reorderable(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('prescription_number')
                    ->label('No. receta')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pet.name')
                    ->label('Mascota')
                    ->searchable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable(),

                Tables\Columns\TextColumn::make('veterinarian.name')
                    ->label('Veterinario'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn($record) => route('prescriptions.pdf', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrescriptions::route('/'),
            'create' => Pages\CreatePrescription::route('/create'),
            'view' => Pages\ViewPrescription::route('/{record}'),
            'edit' => Pages\EditPrescription::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('view_prescriptions') ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_prescriptions') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_prescriptions') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_prescriptions') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete_prescriptions') ?? false;
    }
}