<?php

namespace App\Filament\App\Widgets;

use App\Repositories\Interfaces\AdministrationApp\AttendanceInterface;
use App\Repositories\Interfaces\AdministrationApp\PermitInterface;
use App\Repositories\Interfaces\CoreApp\DepartementInterface;
use App\Repositories\Interfaces\CoreApp\UserInterface;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $total_account = app(UserInterface::class)->countAll();
        $total_departement = app(DepartementInterface::class)->countAll();
        $total_attendance = app(AttendanceInterface::class)->countAll();
        $total_permit = app(PermitInterface::class)->countAll();
        return [
            Stat::make('Total Account', $total_account)
                ->icon('heroicon-m-user')
                ->iconColor('info')
                ->description('The posts in this period')
                ->descriptionIcon('heroicon-m-user-group', 'before')
                ->descriptionColor('primary'),
            Stat::make('Total Departement', $total_departement)
                ->icon('heroicon-o-newspaper')
                ->description('The posts in this period')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('warning'),
            Stat::make('Total Attendance', $total_attendance)
                ->icon('gmdi-fingerprint-o')
                ->description('The posts in this period')
                ->descriptionIcon('gmdi-perm-device-information-r', 'before')
                ->descriptionColor('primary')
                ->iconColor('danger'),
            Stat::make('Total Permit', $total_permit)
                ->icon('gmdi-dynamic-form-o')
                ->description('The posts in this period')
                ->descriptionIcon('gmdi-dynamic-form-o', 'before')
                ->descriptionColor('primary')
                ->iconColor('secondary'),
        ];
    }
}
