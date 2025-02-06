<?php

namespace App\Filament\App\Widgets;

use App\Repositories\Interfaces\AdministrationApp\PermitInterface;
use EightyNine\FilamentAdvancedWidget\AdvancedChartWidget;

class PermitChartWidget extends AdvancedChartWidget
{
    protected static ?string $heading = 'Permit chart';
    protected static string $color = 'info';
    protected static ?string $icon = 'heroicon-o-chart-bar';
    protected static ?string $iconColor = 'info';
    protected static ?string $iconBackgroundColor = 'info';
    protected static ?string $label = 'Monthly Permit chart';

    public ?string $filter = 'today';

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'week' => 'Last week',
            'month' => 'Last month',
            'year' => 'This year',
        ];
    }

    protected function getData(): array
    {
        $data = app(PermitInterface::class)->chart($this->filter);
        return [
            'datasets' => [
                [
                    'label' => 'Total Permit',
                    'data' => $data['total'],
                    'backgroundColor' => '#FCC737',
                    'borderColor' => '#FCC737',
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
