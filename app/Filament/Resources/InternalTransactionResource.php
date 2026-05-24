<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InternalTransactionResource\Pages;
use App\Models\InternalTransaction;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InternalTransactionResource extends Resource
{
    protected static ?string $model = InternalTransaction::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.name')->sortable(),
                Tables\Columns\TextColumn::make('internalToken.name')->sortable(),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('amount')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('balance_before')->numeric(),
                Tables\Columns\TextColumn::make('balance_after')->numeric(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInternalTransactions::route('/'),
            'view' => Pages\ViewInternalTransaction::route('/{record}'),
        ];
    }
}
