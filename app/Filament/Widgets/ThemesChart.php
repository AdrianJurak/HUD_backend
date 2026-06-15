<?php

namespace App\Filament\Widgets;

use App\Models\Theme;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class ThemesChart extends ChartWidget
{
    protected static ?string $heading = 'Aktywność tworzenia motywów';

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '250px';

    protected function getData(): array
    {
        $data = Trend::model(Theme::class)
            ->between(
                start: now()->subDays(6),
                end: now(),
            )
            ->perDay()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Dodane konfiguracje',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => '#3b82f6',
                    'borderColor' => '#3b82f6',
                ],
            ],
            'labels' => ['Pon', 'Wto', 'Śro', 'Czw', 'Pią', 'Sob', 'Dzisiaj'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
