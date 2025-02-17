<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\OrderItem;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')->disabled()->label('ID')->columnSpan(2),
                Forms\Components\TextInput::make('user.id')->readonly()->label('User ID'),
                Forms\Components\TextInput::make('user.seller_code')->readonly()->label('Seller Code'),
                Forms\Components\TextInput::make('product.id')->disabled()->label('Product ID')->columnSpan(2),
                Forms\Components\TextArea::make('address')->readonly()->label('Address')->rows(2)->cols(2),
                Forms\Components\TextArea::make('delivery_estimate')->readonly()->label('Estimated Delivery')->rows(2)->cols(2),
                Forms\Components\TextInput::make('phone')->readonly()->label('Phone Number'),
                Forms\Components\TextInput::make('email')->readonly()->label('Email'),
                Forms\Components\TextInput::make('price')->readonly()->label('Price'),
                Forms\Components\TextInput::make('sub_total')->readonly()->label('Sub-Total'),
                Forms\Components\TextInput::make('quantity')->readonly()->label('Quantity'),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('Name')->searchable()->sortable(),

            ])
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
