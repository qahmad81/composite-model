<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProviderModelResource\Pages;
use App\Models\ProviderModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProviderModelResource extends Resource
{
    protected static ?string $model = ProviderModel::class;
    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('provider_id')
                    ->relationship('provider', 'name')
                    ->required(),
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\TextInput::make('model_key')->required(),
                Forms\Components\TextInput::make('role')->required(),
                Forms\Components\TextInput::make('context_window')->numeric()->required(),
                Forms\Components\Toggle::make('is_enabled')->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('provider.name')->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('model_key'),
                Tables\Columns\TextColumn::make('role'),
                Tables\Columns\TextColumn::make('context_window')->numeric(),
                Tables\Columns\ToggleColumn::make('is_enabled'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('provider_id')
                    ->relationship('provider', 'name'),
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
            'index' => Pages\ListProviderModels::route('/'),
            'create' => Pages\CreateProviderModel::route('/create'),
            'edit' => Pages\EditProviderModel::route('/{record}/edit'),
        ];
    }
}
