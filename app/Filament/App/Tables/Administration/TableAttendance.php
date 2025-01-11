<?php
namespace App\Filament\App\Tables\Administration;

use App\Models\AdministrationApp\UserAttendance;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Carbon;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
class TableAttendance
{
    public static function table()
    {
        return [
            TextColumn::make('user.name'),
            TextColumn::make('schedule.work_day'),
            TextColumn::make('lat_in')
                ->searchable(),
            TextColumn::make('lat_out')
                ->searchable(),
            TextColumn::make('long_in')
                ->searchable(),
            TextColumn::make('long_out')
                ->searchable(),
            ImageColumn::make('image_in'),
            ImageColumn::make('image_out'),
            TextColumn::make('status_in'),
            TextColumn::make('status_out'),
            TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('updated_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    public static function filter()
    {
        return [
            SelectFilter::make('user')
                ->label('Filter by user')
                ->relationship('user', 'name')
                ->searchable()
                ->preload(),
            SelectFilter::make('day_work')
                ->label('Filter by day work')
                ->relationship('schedule', 'work_day')
                ->searchable()
                ->preload(),
            DateRangeFilter::make('created_at')
                ->minDate(Carbon::now()->subMonth())
                ->maxDate(Carbon::now()->addMonth()),
            SelectFilter::make('status_in')
                ->options(UserAttendance::STATUS),
            SelectFilter::make('status_out')
                ->options(UserAttendance::STATUS),
        ];
    }
}
