<?php

namespace App\Filament\Resources\InternalTokenResource\Pages;

use App\Filament\Resources\InternalTokenResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInternalTokens extends ListRecords
{
    protected static string $resource = InternalTokenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
