<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BatchesRelationManager extends RelationManager
{
    protected static string $relationship = 'batches';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('batch_number')
                    ->label('Número de lote')
                    ->maxLength(255),

                Forms\Components\DatePicker::make('expiration_date')
                    ->label('Fecha de vencimiento'),

                Forms\Components\TextInput::make('cost_price')
                    ->label('Costo')
                    ->numeric()
                    ->prefix('Q'),

                Forms\Components\TextInput::make('quantity')
                    ->label('Existencia')
                    ->disabled()
                    ->dehydrated(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('expiration_date')
            ->columns([
                Tables\Columns\TextColumn::make('batch_number')
                    ->label('Lote'),

                Tables\Columns\TextColumn::make('expiration_date')
                    ->label('Vence')
                    ->date('d/m/Y'),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Existencia')
                    ->numeric(2),

                Tables\Columns\TextColumn::make('cost_price')
                    ->label('Costo')
                    ->money('GTQ'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make()
                    ->before(function (ProductBatch $record, Tables\Actions\DeleteAction $action) {

                        if ($record->quantity > 0) {

                            Notification::make()
                                ->title('No se puede eliminar el lote')
                                ->body('El lote todavía tiene existencias.')
                                ->danger()
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->bulkActions([]);
    }
}
