<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'movements';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('created_at')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->numeric(2),

                Tables\Columns\TextColumn::make('stock_before')
                    ->label('Antes')
                    ->numeric(2)
                    ->color('gray'),

                Tables\Columns\TextColumn::make('stock_after')
                    ->label('Después')
                    ->numeric(2)
                    ->badge(),

                Tables\Columns\TextColumn::make('batch.batch_number')
                    ->label('Lote')
                    ->placeholder('Sin lote'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario'),

                Tables\Columns\TextColumn::make('reference')
                    ->label('Referencia')
                    ->placeholder('-'),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
