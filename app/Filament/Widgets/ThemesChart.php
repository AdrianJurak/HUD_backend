<?php

namespace App\Filament\Widgets;

use App\Models\Theme;
use Filament\Widgets\ChartWidget;

class ThemesChart extends ChartWidget
{
    protected static ?string $heading = 'Aktywność tworzenia motywów';

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '250px';

    protected function getData(): array
    {
        $currentThemesCount = Theme::count();

        return [
            'datasets' => [
                [
                    'label' => 'Dodane konfiguracje',
                    'data' => [0, 1, 2, 1, 3, 2, $currentThemesCount],
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
