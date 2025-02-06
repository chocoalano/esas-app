<?php

namespace App\Filament\App\Widgets;

use App\Repositories\Interfaces\AdministrationApp\AttendanceInterface;
use EightyNine\FilamentAdvancedWidget\AdvancedChartWidget;

class AttendanceChartWidget extends AdvancedChartWidget
{
    protected static ?string $heading = 'Attendance Overviews';
    protected static string $color = 'info';
    protected static ?string $icon = 'heroicon-o-chart-bar';
    protected static ?string $iconColor = 'info';
    protected static ?string $iconBackgroundColor = 'info';
    protected static ?string $label = 'Attendance chart';

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
        $data = app(AttendanceInterface::class)->chart($this->filter);
        return [
            'datasets' => [
                [
                    'label' => 'Total Attendance Late',
                    'data' => $data['late'],
                    'backgroundColor' => '#FCC737',
                    'borderColor' => '#FCC737',
                ],
                [
                    'label' => 'Total Attendance Unlate',
                    'data' => $data['unlate'],
                    'backgroundColor' => '#36A2EB',
                    'borderColor' => '#9BD0F5',
                ],
                [
                    'label' => 'Total Attendance Normal',
                    'data' => $data['normal'],
                    'backgroundColor' => '#FA4032',
                    'borderColor' => '#FA4032',
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
