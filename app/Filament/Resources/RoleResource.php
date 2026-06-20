<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'Seguridad';
    protected static ?string $navigationLabel = 'Roles';
    protected static ?string $modelLabel = 'Rol';
    protected static ?string $pluralModelLabel = 'Roles';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos del rol')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('guard_name')
                            ->label('Guard')
                            ->default('web')
                            ->required()
                            ->maxLength(255),
                    ]),

                Forms\Components\Section::make('Permisos')
                    ->schema([
                        Forms\Components\CheckboxList::make('permissions')
                            ->label('Permisos asignados')
                            ->relationship('permissions', 'name')
                            ->options(fn() => Permission::query()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray())
                            ->columns(3)
                            ->searchable()
                            ->bulkToggleable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Rol')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('permissions_count')
                    ->label('Permisos')
                    ->counts('permissions')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn(Role $record): bool => !in_array($record->name, [
                        'Administrador',
                        'Recepción',
                        'Veterinario',
                        'Auxiliar',
                    ])),
            ])
            ->bulkActions([]);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage_users') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage_users') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage_users') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('manage_users') ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}