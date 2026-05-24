<?php

namespace App\Filament\Resources\ProviderModelResource\Pages;

use App\Filament\Resources\ProviderModelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProviderModel extends EditRecord
{
    protected static string $resource = ProviderModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
