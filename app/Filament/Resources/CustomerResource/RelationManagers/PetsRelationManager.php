<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Breed;
use Filament\Forms\Get;

class PetsRelationManager extends RelationManager
{
    protected static string $relationship = 'pets';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información general')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre de la mascota')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('species_id')
                            ->label('Especie')
                            ->relationship('species', 'name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required()
                            ->afterStateUpdated(fn(Forms\Set $set) => $set('breed_id', null)),

                        Forms\Components\Select::make('breed_id')
                            ->label('Raza')
                            ->options(fn(Get $get) => Breed::query()
                                ->where('species_id', $get('species_id'))
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('sex')
                            ->label('Sexo')
                            ->options([
                                'Macho' => 'Macho',
                                'Hembra' => 'Hembra',
                            ])
                            ->nullable(),

                        Forms\Components\DatePicker::make('birth_date')
                            ->label('Fecha de nacimiento'),

                        Forms\Components\TextInput::make('weight')
                            ->label('Peso')
                            ->numeric()
                            ->suffix('kg'),

                        Forms\Components\TextInput::make('color')
                            ->label('Color')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Información clínica inicial')
                    ->schema([
                        Forms\Components\Textarea::make('allergies')
                            ->label('Alergias')
                            ->rows(3),

                        Forms\Components\Textarea::make('observations')
                            ->label('Observaciones')
                            ->rows(3),

                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'Activo' => 'Activo',
                                'Inactivo' => 'Inactivo',
                                'Fallecido' => 'Fallecido',
                            ])
                            ->default('Activo')
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Mascota')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('species.name')
                    ->label('Especie')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('breed.name')
                    ->label('Raza')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('sex')
                    ->label('Sexo')
                    ->badge(),

                Tables\Columns\TextColumn::make('weight')
                    ->label('Peso')
                    ->suffix(' kg')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Activo' => 'success',
                        'Inactivo' => 'warning',
                        'Fallecido' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('birth_date')
                    ->label('Nacimiento')
                    ->date('d/m/Y')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('age')
                    ->label('Edad'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('species_id')
                    ->label('Especie')
                    ->relationship('species', 'name'),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Activo' => 'Activo',
                        'Inactivo' => 'Inactivo',
                        'Fallecido' => 'Fallecido',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->requiresConfirmation(),
                ]),
            ]);
    }
}
