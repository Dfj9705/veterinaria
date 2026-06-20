<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PetResource\Pages;
use App\Filament\Resources\PetResource\RelationManagers;
use App\Models\Pet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Breed;
use Filament\Forms\Get;

class PetResource extends Resource
{
    protected static ?string $model = Pet::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Pacientes';
    protected static ?string $navigationLabel = 'Mascotas';
    protected static ?string $modelLabel = 'Mascota';
    protected static ?string $pluralModelLabel = 'Mascotas';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Propietario')
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->label('Cliente')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required()->createOptionForm([
                                    Forms\Components\TextInput::make('name')
                                        ->label('Nombre completo')
                                        ->required()
                                        ->maxLength(255),

                                    Forms\Components\TextInput::make('phone')
                                        ->label('Teléfono')
                                        ->tel()
                                        ->maxLength(255),

                                    Forms\Components\TextInput::make('email')
                                        ->label('Correo electrónico')
                                        ->email()
                                        ->maxLength(255),

                                    Forms\Components\TextInput::make('dpi')
                                        ->label('DPI')
                                        ->maxLength(255),

                                    Forms\Components\TextInput::make('nit')
                                        ->label('NIT')
                                        ->maxLength(255),

                                    Forms\Components\Textarea::make('address')
                                        ->label('Dirección')
                                        ->rows(3),

                                    Forms\Components\Textarea::make('notes')
                                        ->label('Notas')
                                        ->rows(3),
                                ])->createOptionModalHeading('Crear cliente')
                            ->createOptionAction(
                                fn($action) => $action->modalWidth('lg')
                            ),
                    ]),

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
                            ]),

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

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Mascota')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Propietario')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->toggleable(),

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
                    ->suffix(' kg'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Activo' => 'success',
                        'Inactivo' => 'warning',
                        'Fallecido' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('species_id')
                    ->label('Especie')
                    ->relationship('species', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'Activo' => 'Activo',
                        'Inactivo' => 'Inactivo',
                        'Fallecido' => 'Fallecido',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPets::route('/'),
            'create' => Pages\CreatePet::route('/create'),
            'edit' => Pages\EditPet::route('/{record}/edit'),
        ];
    }
}
