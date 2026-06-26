<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnitResource\Pages;
use App\Filament\Resources\UnitResource\RelationManagers;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UnitResource extends Resource
{
    protected static ?string $model = Unit::class;

    protected static ?string $navigationIcon = 'fas-ruler';
    protected static ?string $navigationGroup = 'Inventario';
    protected static ?string $navigationLabel = 'Unidades de Medida';
    protected static ?string $modelLabel = 'Unidad de Medida';
    protected static ?string $pluralModelLabel = 'Unidades de Medida';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre')
                    ->required(),

                Forms\Components\TextInput::make('abbreviation')
                    ->label('Abreviatura')
                    ->maxLength(20),

                Forms\Components\Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('abbreviation')
                    ->label('Abrev.'),

                Tables\Columns\TextColumn::make('products_count')
                    ->label('Productos')
                    ->counts('products'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListUnits::route('/'),
            'create' => Pages\CreateUnit::route('/create'),
            'edit' => Pages\EditUnit::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_units') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_units') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_units') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete_units') ?? false;
    }
}
