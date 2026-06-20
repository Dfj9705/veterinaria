<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Catálogos';

    protected static ?string $navigationLabel = 'Servicios';

    protected static ?string $modelLabel = 'Servicio';

    protected static ?string $pluralModelLabel = 'Servicios';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del servicio')
                    ->schema([
                        Forms\Components\Select::make('service_category_id')
                            ->label('Categoría')
                            ->relationship(
                                name: 'category',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn($query) => $query->where('is_active', true)
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\ColorPicker::make('color')
                                    ->label('Color'),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Activo')
                                    ->default(true),
                            ]),

                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('duration_minutes')
                            ->label('Duración')
                            ->numeric()
                            ->suffix('min')
                            ->default(30)
                            ->required(),

                        Forms\Components\TextInput::make('price')
                            ->label('Precio')
                            ->numeric()
                            ->prefix('Q')
                            ->default(0)
                            ->required(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Estado')
                            ->default(true)
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Descripción')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Servicio')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Duración')
                    ->suffix(' min')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->money('GTQ')
                    ->sortable(),

                Tables\Columns\TextColumn::make('is_active')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state ? 'Activo' : 'Inactivo')
                    ->colors([
                        'success' => 'Activo',
                        'danger' => 'Inactivo',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('service_category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->trueLabel('Activo')
                    ->falseLabel('Inactivo')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('view_services') ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_services') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_services') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_services') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete_services') ?? false;
    }
}