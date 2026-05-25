<?php

namespace App\Filament\Resources\CompositeModuleResource\Pages;

use App\Filament\Resources\CompositeModuleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompositeModule extends EditRecord
{
    protected static string $resource = CompositeModuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('openFlowBuilder')
                ->label('Open Flow Builder')
                ->url(fn ($record) => route('flow-editor', ['compositeModule' => $record->id]))
                ->openUrlInNewTab()
                ->color('primary')
                ->icon('heroicon-o-pencil-square'),
            Actions\DeleteAction::make(),
        ];
    }
}
