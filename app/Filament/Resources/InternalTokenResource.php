<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InternalTokenResource\Pages;
use App\Models\InternalToken;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class InternalTokenResource extends Resource
{
    protected static ?string $model = InternalToken::class;
    protected static ?string $navigationIcon = 'heroicon-o-key';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('client_id')
                    ->relationship('client', 'name')
                    ->required(),
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\Toggle::make('is_active')->required(),
                Forms\Components\TextInput::make('limit_balance')->numeric()->required(),
                Forms\Components\TextInput::make('pending_balance')->numeric()->disabled(),
                Forms\Components\TextInput::make('final_balance')->numeric()->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.name')->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\ToggleColumn::make('is_active'),
                Tables\Columns\TextColumn::make('limit_balance')->numeric(),
                Tables\Columns\TextColumn::make('pending_balance')->numeric(),
                Tables\Columns\TextColumn::make('final_balance')->numeric(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('generate_token')
                    ->form([
                        Forms\Components\Select::make('client_id')
                            ->relationship('client', 'name')
                            ->required(),
                        Forms\Components\TextInput::make('name')->required(),
                        Forms\Components\TextInput::make('limit_balance')->numeric()->default(1000000),
                    ])
                    ->action(function (array $data) {
                        $rawToken = 'cm_' . Str::random(40);
                        InternalToken::create([
                            'client_id' => $data['client_id'],
                            'name' => $data['name'],
                            'token_hash' => hash('sha256', $rawToken),
                            'limit_balance' => $data['limit_balance'],
                            'is_active' => true,
                            'rate_limit' => 60,
                            'final_balance' => 0,
                            'pending_balance' => 0,
                        ]);

                        Notification::make()
                            ->title('Token Generated')
                            ->body('Copy this token now, it will not be shown again: ' . $rawToken)
                            ->persistent()
                            ->send();
                    })
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
            'index' => Pages\ListInternalTokens::route('/'),
            'create' => Pages\CreateInternalToken::route('/create'),
            'edit' => Pages\EditInternalToken::route('/{record}/edit'),
        ];
    }
}
