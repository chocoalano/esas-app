<?php
namespace App\Filament\App\Tables\Administration;

use App\Models\CoreApp\TimeWork;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
class TableTimeAttendanceSchedule
{
    public static function table()
    {
        return [
            TextColumn::make('user.nip')->label('NIK')->searchable(),
            TextColumn::make('user.name')->label('Name')->searchable(),
            TextColumn::make('timework.name')->label('Time Name')->searchable(),
            // SelectColumn::make('timework.id')
            //     ->options(TimeWork::with('')->all()->pluck('name', 'id'))
            //     ->rules(['required']),
            TextColumn::make('timework.in')->label('in')->searchable(),
            TextColumn::make('timework.out')->label('out')->searchable(),
            TextColumn::make('work_day')->date()->searchable(),
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
                ->relationship('user', 'name', fn(Builder $query) => $query->with('company'))
                ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->company->name} | {$record->name}")
                ->searchable()
                ->preload(),
            SelectFilter::make('time')
                ->label('Filter by time')
                ->relationship('timework', 'name', fn(Builder $query) => $query->with('department', 'company'))
                ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->company->name} | {$record->name} | {$record->department->name}")
                ->searchable()
                ->preload(),
            DateRangeFilter::make('work_day')
                ->minDate(Carbon::now()->subMonth())
                ->maxDate(Carbon::now()->addMonth()),
        ];
    }
}
