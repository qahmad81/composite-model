<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompositeModuleResource\Pages;
use App\Models\CompositeModule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Support\HtmlString;

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
                
                Forms\Components\Section::make('Flow Configuration')
                    ->schema([
                        Forms\Components\Textarea::make('flow_json')
                            ->required()
                            ->rows(10)
                            ->disabled(fn ($record) => $record !== null)
                            ->helperText(fn ($record) => $record === null 
                                ? 'Save the record first to use the visual Flow Builder.' 
                                : 'Use the visual editor above for changes. This field is read-only in edit mode.'),
                        
                        Placeholder::make('flow_builder_link')
                            ->label('Visual Editor')
                            ->visible(fn ($record) => $record !== null)
                            ->content(fn ($record) => new HtmlString("
                                <a href='" . route('flow-editor', ['compositeModule' => $record->id]) . "' 
                                   target='_blank' 
                                   class='fi-btn fi-btn-size-md fi-btn-color-primary fi-color-primary fi-color-custom relative inline-grid grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-btn-bg-fill px-3 py-2 text-sm shadow-sm'
                                   style='--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600); background-color: var(--primary-600); color: white;'>
                                    Open Flow Builder
                                </a>
                            ")),
                    ])
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
