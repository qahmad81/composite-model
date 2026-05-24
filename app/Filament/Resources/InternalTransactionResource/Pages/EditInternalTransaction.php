<?php

namespace App\Filament\Resources\InternalTransactionResource\Pages;

use App\Filament\Resources\InternalTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInternalTransaction extends EditRecord
{
    protected static string $resource = InternalTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
