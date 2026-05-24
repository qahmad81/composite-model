<?php

namespace App\Filament\Resources\ExecutionLogResource\Pages;

use App\Filament\Resources\ExecutionLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewExecutionLog extends ViewRecord
{
    protected static string $resource = ExecutionLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
