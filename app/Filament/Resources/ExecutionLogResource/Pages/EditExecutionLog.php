<?php

namespace App\Filament\Resources\ExecutionLogResource\Pages;

use App\Filament\Resources\ExecutionLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExecutionLog extends EditRecord
{
    protected static string $resource = ExecutionLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
