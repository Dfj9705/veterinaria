<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Filament\Resources\ProductResource\RelationManagers\BatchesRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\MovementsRelationManager;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Bus\Events\BatchDispatched;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\InventoryCategory;
use App\Models\Unit;


class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationGroup = 'Inventario';
    protected static ?string $navigationLabel = 'Productos';
    protected static ?string $modelLabel = 'Producto';
    protected static ?string $pluralModelLabel = 'Productos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Información general')
                    ->schema([

                        Forms\Components\Select::make('inventory_category_id')
                            ->label('Categoría')
                            ->relationship(
                                'category',
                                'name',
                                fn($query) => $query->where('is_active', true)
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('type')
                            ->label('Tipo')
                            ->options([
                                'Medicamento' => 'Medicamento',
                                'Vacuna' => 'Vacuna',
                                'Insumo' => 'Insumo',
                                'Alimento' => 'Alimento',
                                'Accesorio' => 'Accesorio',
                                'Material quirúrgico' => 'Material quirúrgico',
                                'Laboratorio' => 'Laboratorio',
                                'Otro' => 'Otro',
                            ])
                            ->required(),
                        Forms\Components\Toggle::make('uses_batches')
                            ->label('Controlar por lotes')
                            ->helperText('Útil para medicamentos, vacunas y productos con vencimiento.')
                            ->default(false),

                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->columnSpanFull(),

                    ])
                    ->columns(2),

                Forms\Components\Section::make('Identificación')
                    ->schema([

                        Forms\Components\TextInput::make('internal_code')
                            ->label('Código interno')
                            ->default(fn() => Product::generateInternalCode())
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('barcode')
                            ->label('Código de barras')
                            ->unique(ignoreRecord: true)
                            ->maxLength(100),

                        Forms\Components\TextInput::make('brand')
                            ->label('Marca'),

                        Forms\Components\TextInput::make('presentation')
                            ->label('Presentación'),

                    ])
                    ->columns(2),

                Forms\Components\Section::make('Inventario')
                    ->schema([

                        Forms\Components\Select::make('unit_id')
                            ->label('Unidad')
                            ->relationship(
                                'unit',
                                'name',
                                fn($query) => $query->where('is_active', true)
                            )
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('current_stock')
                            ->numeric()
                            ->default(0)
                            ->disabled(),

                        Forms\Components\TextInput::make('minimum_stock')
                            ->numeric()
                            ->required()
                            ->default(0),

                    ])
                    ->columns(3),

                Forms\Components\Section::make('Precios')
                    ->schema([

                        Forms\Components\TextInput::make('cost_price')
                            ->numeric()
                            ->prefix('Q')
                            ->required(),

                        Forms\Components\TextInput::make('sale_price')
                            ->numeric()
                            ->prefix('Q')
                            ->required(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),

                    ])
                    ->columns(3),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([

                Tables\Columns\TextColumn::make('internal_code')
                    ->label('Código')
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría')
                    ->badge(),

                Tables\Columns\TextColumn::make('type')
                    ->badge(),

                Tables\Columns\TextColumn::make('current_stock')
                    ->label('Existencia')
                    ->numeric(2),

                Tables\Columns\TextColumn::make('minimum_stock')
                    ->label('Mínimo'),

                Tables\Columns\TextColumn::make('sale_price')
                    ->money('GTQ'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),

            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Editar'),
            ])
            ->filters([

                Tables\Filters\SelectFilter::make('inventory_category_id')
                    ->relationship('category', 'name'),

                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'Medicamento' => 'Medicamento',
                        'Vacuna' => 'Vacuna',
                        'Insumo' => 'Insumo',
                        'Alimento' => 'Alimento',
                        'Accesorio' => 'Accesorio',
                        'Material quirúrgico' => 'Material quirúrgico',
                        'Laboratorio' => 'Laboratorio',
                        'Otro' => 'Otro',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active'),

            ]);
    }

    public static function getRelations(): array
    {
        return [
            MovementsRelationManager::class,
            BatchesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_products') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_products') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_products') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete_products') ?? false;
    }
}
