<?php

namespace App\Filament\Exports;

use App\Models\AdministrationApp\UserTimeworkSchedule;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class AttendanceScheduleExporter extends Exporter
{
    protected static ?string $model = UserTimeworkSchedule::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('user.nip'),
            ExportColumn::make('user.name'),
            ExportColumn::make('timework.name'),
            ExportColumn::make('timework.in'),
            ExportColumn::make('timework.out'),
            ExportColumn::make('work_day'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your attendance schedule export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
