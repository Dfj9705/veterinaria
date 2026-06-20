<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BreedResource\Pages;
use App\Filament\Resources\BreedResource\RelationManagers;
use App\Models\Breed;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BreedResource extends Resource
{
    protected static ?string $model = Breed::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Catálogos';

    protected static ?string $navigationLabel = 'Razas';

    protected static ?string $modelLabel = 'Raza';

    protected static ?string $pluralModelLabel = 'Razas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la raza')
                    ->schema([
                        Forms\Components\Select::make('species_id')
                            ->label('Especie')
                            ->relationship('species', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('species.name')
                    ->label('Especie')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Raza')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('species_id')
                    ->label('Especie')
                    ->relationship('species', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->trueLabel('Activas')
                    ->falseLabel('Inactivas')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->requiresConfirmation()->before(function ($record, Tables\Actions\DeleteAction $action) {
                    if ($record->pets()->exists()) {
                        Notification::make()
                            ->title('No se puede eliminar')
                            ->body('Esta raza ya está asociada a una o más mascotas.')
                            ->danger()
                            ->send();

                        $action->cancel();
                    }
                }),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
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
            'index' => Pages\ListBreeds::route('/'),
            'create' => Pages\CreateBreed::route('/create'),
            'edit' => Pages\EditBreed::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('view_breeds') ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_breeds') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_breeds') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_breeds') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete_breeds') ?? false;
    }
}
