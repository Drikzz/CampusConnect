<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Category;
use App\Models\User;



class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')->disabled()->label('ID')->columnSpan(2),
                Forms\Components\TextInput::make('name')->required()->label('Product Name')->columnSpan(2),
                Forms\Components\TextArea::make('description')->required()->cols(5)->rows(2),
                Forms\Components\TextInput::make('price')->required(),
                Forms\Components\TextInput::make('discount')->required(),
                Forms\Components\TextInput::make('discounted_price')->disabled()->label('Discounted Price'),
                Forms\Components\FileUpload::make('images')->image()->required()->directory('products'),
                Forms\Components\CheckboxList::make('category_id')->label('Categories')->options(Category::pluck('name', 'id'))->required(),
                Forms\Components\CheckboxList::make('options')->label('Options')
                ->options([
                    'is_buyable' => 'Buyable',
                    'is_tradable' => 'Tradable',
                ])
                ->columns(2) 
                ->default(fn ($record) => [
                    'is_buyable' => $record?->is_buyable,
                    'is_tradable' => $record?->is_tradable,
                ])
                ->afterStateUpdated(function ($state, $set) {
                    $set('is_buyable', in_array('is_buyable', $state));
                    $set('is_tradable', in_array('is_tradable', $state));
                }),
                Forms\Components\Radio::make('status')
                ->label('Status')
                ->options([
                    'Active' => 'Active',
                    'Inactive' => 'Inactive',
                ])
                ->default('Active')

                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Product Name')->searchable(),
                Tables\Columns\TextColumn::make('description')->limit(100),
               Tables\Columns\TextColumn::make('categories.name')->label('Category')->badge()
               
              
                
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
