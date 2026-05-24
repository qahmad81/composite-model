<?php

namespace App\Filament\Resources\InternalTransactionResource\Pages;

use App\Filament\Resources\InternalTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInternalTransactions extends ListRecords
{
    protected static string $resource = InternalTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
