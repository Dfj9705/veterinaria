<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryMovementResource\Pages;
use App\Filament\Resources\InventoryMovementResource\RelationManagers;
use App\Models\InventoryMovement;
use App\Models\ProductBatch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;
use App\Models\Product;
class InventoryMovementResource extends Resource
{
    protected static ?string $model = InventoryMovement::class;

    protected static ?string $navigationIcon = 'fas-arrow-right-arrow-left';
    protected static ?string $navigationGroup = 'Inventario';
    protected static ?string $navigationLabel = 'Movimientos';
    protected static ?string $modelLabel = 'Movimiento';
    protected static ?string $pluralModelLabel = 'Movimientos';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Movimiento de inventario')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Tipo de movimiento')
                            ->options([
                                'Entrada' => 'Entrada',
                                'Salida' => 'Salida',
                                'Ajuste +' => 'Ajuste +',
                                'Ajuste -' => 'Ajuste -',
                            ])
                            ->required()
                            ->live(),

                        Forms\Components\Select::make('product_id')
                            ->label('Producto')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live(),

                        Forms\Components\Select::make('product_batch_id')
                            ->label('Lote')
                            ->options(function (Forms\Get $get) {
                                $productId = $get('product_id');

                                if (!$productId) {
                                    return [];
                                }

                                return ProductBatch::query()
                                    ->where('product_id', $productId)
                                    ->orderBy('expiration_date')
                                    ->get()
                                    ->mapWithKeys(function ($batch) {
                                        $label = ($batch->batch_number ?: 'Sin lote')
                                            . ' | Stock: ' . number_format($batch->quantity, 2)
                                            . ($batch->expiration_date ? ' | Vence: ' . $batch->expiration_date->format('d/m/Y') : '');

                                        return [$batch->id => $label];
                                    });
                            })
                            ->searchable()
                            ->preload()
                            ->required(function (Forms\Get $get) {
                                $product = Product::find($get('product_id'));

                                return $product?->uses_batches
                                    && in_array($get('type'), ['Salida', 'Ajuste -']);
                            })
                            ->visible(function (Forms\Get $get) {
                                $product = Product::find($get('product_id'));

                                return $product?->uses_batches;
                            })
                            ->createOptionForm([
                                Forms\Components\TextInput::make('batch_number')
                                    ->label('Número de lote')
                                    ->maxLength(255),

                                Forms\Components\DatePicker::make('expiration_date')
                                    ->label('Fecha de vencimiento'),

                                Forms\Components\TextInput::make('cost_price')
                                    ->label('Costo del lote')
                                    ->numeric()
                                    ->prefix('Q')
                                    ->default(0),
                            ])
                            ->live()
                            ->createOptionUsing(function (array $data, Forms\Get $get) {
                                $data['product_id'] = $get('product_id');
                                $data['quantity'] = 0;

                                return ProductBatch::create($data)->getKey();
                            }),

                        Forms\Components\TextInput::make('quantity')
                            ->label('Cantidad')
                            ->numeric()
                            ->minValue(0.01)
                            ->required(),

                        Forms\Components\TextInput::make('reference')
                            ->label('Referencia')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('notes')
                            ->label('Observaciones')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i'),

                Tables\Columns\TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->color(fn($state) => match ($state) {
                        'Entrada' => 'success',
                        'Salida' => 'danger',
                        'Ajuste +' => 'success',
                        'Ajuste -' => 'danger',
                    })
                    ->badge(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Cantidad'),

                Tables\Columns\TextColumn::make('stock_before')
                    ->label('Antes')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('stock_after')
                    ->label('Después')
                    ->color('blue'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario'),

                Tables\Columns\TextColumn::make('reference')
                    ->label('Referencia'),
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
            'index' => Pages\ListInventoryMovements::route('/'),
            'create' => Pages\CreateInventoryMovement::route('/create'),
            'edit' => Pages\EditInventoryMovement::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_inventory_movements') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_inventory_movements') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_inventory_movements') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete_inventory_movements') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
