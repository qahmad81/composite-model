<?php

namespace App\Filament\Resources\InternalTransactionResource\Pages;

use App\Filament\Resources\InternalTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewInternalTransaction extends ViewRecord
{
    protected static string $resource = InternalTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
