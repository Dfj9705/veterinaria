<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuotationResource\Pages;
use App\Models\Customer;
use App\Models\Pet;
use App\Models\Quotation;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class QuotationResource extends Resource
{
    protected static ?string $model = Quotation::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static ?string $navigationGroup = 'Operación';

    protected static ?string $navigationLabel = 'Cotizaciones';

    protected static ?string $modelLabel = 'Cotización';

    protected static ?string $pluralModelLabel = 'Cotizaciones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos generales')
                    ->schema([
                        Forms\Components\TextInput::make('number')
                            ->label('Número')
                            ->default(fn() => 'COT-' . now()->format('Ymd-His'))
                            ->required()
                            ->unique(ignoreRecord: true),

                        Forms\Components\Select::make('customer_id')
                            ->label('Cliente')
                            ->options(fn() => Customer::query()
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('pet_id', null);
                            }),

                        Forms\Components\Select::make('pet_id')
                            ->label('Mascota')
                            ->options(function (Get $get) {
                                return Pet::query()
                                    ->when($get('customer_id'), fn($query, $customerId) => $query->where('customer_id', $customerId))
                                    ->orderBy('name')
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload(),

                        Forms\Components\DatePicker::make('quotation_date')
                            ->label('Fecha')
                            ->default(now())
                            ->required(),

                        Forms\Components\DatePicker::make('valid_until')
                            ->label('Válida hasta')
                            ->default(now()->addDays(30)),

                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'Borrador' => 'Borrador',
                                'Enviada' => 'Enviada',
                                'Aceptada' => 'Aceptada',
                                'Rechazada' => 'Rechazada',
                                'Vencida' => 'Vencida',
                                'Convertida' => 'Convertida',
                            ])
                            ->default('Borrador')
                            ->required(),
                    ])
                    ->columns(3)
                    ->disabled(fn($record) => $record?->status !== 'Borrador' && $record?->status !== null),

                Forms\Components\Section::make('Servicios cotizados')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->label('Servicios')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('service_id')
                                    ->label('Servicio')
                                    ->options(fn() => Service::query()
                                        ->where('is_active', true)
                                        ->orderBy('name')
                                        ->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        $service = Service::find($state);

                                        if ($service) {
                                            $set('description', $service->name);
                                            $set('reference_price', $service->price);
                                            $set('unit_price', $service->price);
                                            $set('quantity', 1);
                                            $set('subtotal', $service->price);
                                        }
                                    })
                                    ->required(),

                                Forms\Components\TextInput::make('description')
                                    ->label('Descripción')
                                    ->required(),

                                Forms\Components\TextInput::make('quantity')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        self::calculateItemSubtotal($get, $set);
                                    })
                                    ->required(),

                                Forms\Components\TextInput::make('reference_price')
                                    ->label('Precio ref.')
                                    ->numeric()
                                    ->prefix('Q')
                                    ->disabled()
                                    ->dehydrated(),

                                Forms\Components\TextInput::make('unit_price')
                                    ->label('Precio cotizado')
                                    ->numeric()
                                    ->prefix('Q')
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        self::calculateItemSubtotal($get, $set);
                                    })
                                    ->required(),

                                Forms\Components\TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->prefix('Q')
                                    ->disabled()
                                    ->dehydrated(),
                            ])
                            ->columns(6)
                            ->defaultItems(1)
                            ->reorderable(false)
                            ->columnSpanFull()
                            ->disabled(fn($record) => $record?->status !== 'Borrador' && $record?->status !== null),
                    ])->disabled(fn($record) => $record?->status !== 'Borrador' && $record?->status !== null),

                Forms\Components\Section::make('Totales')
                    ->schema([
                        Forms\Components\TextInput::make('discount')
                            ->label('Descuento')
                            ->numeric()
                            ->prefix('Q')
                            ->default(0),

                        Forms\Components\TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->prefix('Q')
                            ->disabled()
                            ->dehydrated()
                            ->live(),

                        Forms\Components\TextInput::make('total')
                            ->label('Total')
                            ->numeric()
                            ->prefix('Q')
                            ->disabled()
                            ->dehydrated()
                            ->live(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Observaciones')
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->disabled(fn($record) => $record?->status !== 'Borrador' && $record?->status !== null),
            ]);
    }

    public static function calculateItemSubtotal(Get $get, Set $set): void
    {
        $quantity = (float) ($get('quantity') ?? 0);
        $unitPrice = (float) ($get('unit_price') ?? 0);

        $set('subtotal', round($quantity * $unitPrice, 2));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label('Número')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pet.name')
                    ->label('Mascota')
                    ->searchable(),

                Tables\Columns\TextColumn::make('quotation_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('valid_until')
                    ->label('Válida hasta')
                    ->date('d/m/Y'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'gray' => 'Borrador',
                        'info' => 'Enviada',
                        'success' => 'Aceptada',
                        'danger' => 'Rechazada',
                        'warning' => 'Vencida',
                        'primary' => 'Convertida',
                    ]),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('GTQ')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'Borrador' => 'Borrador',
                        'Enviada' => 'Enviada',
                        'Aceptada' => 'Aceptada',
                        'Rechazada' => 'Rechazada',
                        'Vencida' => 'Vencida',
                        'Convertida' => 'Convertida',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('sendAndPrint')
                    ->label('Enviar e imprimir')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->action(function (Quotation $record) {

                        if ($record->status === 'Borrador') {
                            $record->update([
                                'status' => 'Enviada',
                            ]);
                        }

                        redirect()->away(
                            route('quotations.pdf', $record)
                        );
                    })->visible(fn() => auth()->user()->can('print_quotation'))
                    ->successNotificationTitle('Cotización enviada'),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuotations::route('/'),
            'create' => Pages\CreateQuotation::route('/create'),
            'edit' => Pages\EditQuotation::route('/{record}/edit'),
            'view' => Pages\ViewQuotation::route('/{record}/view'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('view_quotation') ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_quotation') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_quotation') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_quotation') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete_quotation') ?? false;
    }
}