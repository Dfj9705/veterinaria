<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceCategoryResource\Pages;
use App\Filament\Resources\ServiceCategoryResource\RelationManagers;
use App\Models\ServiceCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceCategoryResource extends Resource
{
    protected static ?string $model = ServiceCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';

    protected static ?string $navigationGroup = 'Catálogos';

    protected static ?string $navigationLabel = 'Categorías';

    protected static ?string $modelLabel = 'Categoría';

    protected static ?string $pluralModelLabel = 'Categorías';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información')
                    ->schema([

                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\ColorPicker::make('color')
                            ->label('Color')
                            ->nullable(),

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

                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\ColorColumn::make('color')
                    ->label('Color'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),

                Tables\Columns\TextColumn::make('services_count')
                    ->label('Servicios')
                    ->counts('services'),

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (ServiceCategory $record) {

                        if ($record->services()->exists()) {

                            Notification::make()
                                ->title('No se puede eliminar')
                                ->body('La categoría tiene servicios asociados.')
                                ->danger()
                                ->send();

                            throw new \Exception();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([

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
            'index' => Pages\ListServiceCategories::route('/'),
            'create' => Pages\CreateServiceCategory::route('/create'),
            'edit' => Pages\EditServiceCategory::route('/{record}/edit'),
        ];
    }
}
