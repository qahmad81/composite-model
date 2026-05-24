<?php

namespace App\Filament\Resources\ProviderModelResource\Pages;

use App\Filament\Resources\ProviderModelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProviderModels extends ListRecords
{
    protected static string $resource = ProviderModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
