<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Theme;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Aktywni kierowcy', User::where('is_banned', false)->count())
                ->description('Zarejestrowani użytkownicy systemu')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('Konfiguracje HUD', Theme::count())
                ->description('Gotowe motywy interfejsu')
                ->descriptionIcon('heroicon-m-cpu-chip')
                ->color('primary'),

            Stat::make('Moduły telemetrii', Category::count())
                ->description('Obsługiwane kategorie danych')
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('warning'),
        ];
    }
}
