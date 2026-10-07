<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Theme;
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
                    'data' => $data->map(fn(TrendValue $value) => $value->aggregate)->toArray(),
                    'backgroundColor' => '#3b82f6',
                    'borderColor' => '#3b82f6',
                ],
            ],
            'labels' => $data->map(fn(TrendValue $value) => $value->label)->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
