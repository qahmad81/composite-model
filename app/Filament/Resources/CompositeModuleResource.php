<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompositeModuleResource\Pages;
use App\Models\CompositeModule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CompositeModuleResource extends Resource
{
    protected static ?string $model = CompositeModule::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\TextInput::make('slug')->required(),
                Forms\Components\Textarea::make('description'),
                Forms\Components\TextInput::make('top_credit_limit')->numeric()->required(),
                Forms\Components\Toggle::make('is_enabled')->required(),
                Forms\Components\Textarea::make('flow_json')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('slug')->searchable(),
                Tables\Columns\TextColumn::make('top_credit_limit')->numeric(),
                Tables\Columns\ToggleColumn::make('is_enabled'),
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
            'index' => Pages\ListCompositeModules::route('/'),
            'create' => Pages\CreateCompositeModule::route('/create'),
            'edit' => Pages\EditCompositeModule::route('/{record}/edit'),
        ];
    }
}
