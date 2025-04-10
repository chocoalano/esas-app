<?php

namespace App\Filament\App\Widgets;

use App\Models\AdministrationApp\UserAttendance;
use App\Models\CoreApp\JobPosition;
use App\Repositories\Interfaces\AdministrationApp\AttendanceInterface;
use App\Repositories\Interfaces\AdministrationApp\PermitInterface;
use App\Repositories\Interfaces\CoreApp\DepartementInterface;
use App\Repositories\Interfaces\CoreApp\UserInterface;
use Carbon\Carbon;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $total_account = app(UserInterface::class)->countAll();
        $total_departement = app(DepartementInterface::class)->countAll();
        $total_position = JobPosition::count();
        $total_attendance = app(AttendanceInterface::class)->countAll();
        $total_permit = app(PermitInterface::class)->countAll();
        $total_attendance_today = UserAttendance::whereDate('created_at', Carbon::today())->count();
        $total_alpha_today = UserAttendance::whereNull('time_in')
            ->whereDate('created_at', Carbon::today())->count();
        $total_late_today = UserAttendance::where('status_in', 'late')
            ->whereDate('created_at', Carbon::today())->count();

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

            Stat::make('Total Position', $total_position)
                ->icon('heroicon-o-newspaper')
                ->description('The position in this period')
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

            Stat::make('Total Attendance Today', $total_attendance_today)
                ->icon('gmdi-fingerprint-o')
                ->description('The attendance calculation today')
                ->descriptionIcon('gmdi-dynamic-form-o', 'before')
                ->descriptionColor('primary')
                ->iconColor('secondary'),

            Stat::make('Total Alpha Today', $total_alpha_today)
                ->icon('gmdi-fingerprint-o')
                ->description('The alpha calculation today')
                ->descriptionIcon('gmdi-dynamic-form-o', 'before')
                ->descriptionColor('primary')
                ->iconColor('secondary'),

            Stat::make('Total Late Today', $total_late_today)
                ->icon('gmdi-fingerprint-o')
                ->description('The late calculation today')
                ->descriptionIcon('gmdi-dynamic-form-o', 'before')
                ->descriptionColor('primary')
                ->iconColor('secondary'),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
