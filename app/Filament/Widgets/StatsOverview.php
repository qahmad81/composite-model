<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\InternalToken;
use App\Models\InternalTransaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Clients', Client::count()),
            Stat::make('Active Tokens', InternalToken::where('is_active', true)->count()),
            Stat::make('Transactions Today', InternalTransaction::whereDate('created_at', Carbon::today())->count()),
            Stat::make('Total Revenue', InternalTransaction::where('type', 'confirm')->sum('amount')),
        ];
    }
}
