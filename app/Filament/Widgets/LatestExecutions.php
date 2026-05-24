<?php

namespace App\Filament\Widgets;

use App\Models\ExecutionLog;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestExecutions extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ExecutionLog::latest()->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('request_id'),
                Tables\Columns\TextColumn::make('node_type'),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('cost'),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ]);
    }
}
