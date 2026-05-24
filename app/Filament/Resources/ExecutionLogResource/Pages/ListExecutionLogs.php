<?php

namespace App\Filament\Resources\ExecutionLogResource\Pages;

use App\Filament\Resources\ExecutionLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExecutionLogs extends ListRecords
{
    protected static string $resource = ExecutionLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
