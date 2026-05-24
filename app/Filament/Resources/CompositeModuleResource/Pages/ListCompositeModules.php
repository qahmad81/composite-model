<?php

namespace App\Filament\Resources\CompositeModuleResource\Pages;

use App\Filament\Resources\CompositeModuleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCompositeModules extends ListRecords
{
    protected static string $resource = CompositeModuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
