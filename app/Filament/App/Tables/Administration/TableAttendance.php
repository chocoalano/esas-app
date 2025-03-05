<?php
namespace App\Filament\App\Tables\Administration;

use App\Models\AdministrationApp\UserAttendance;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Carbon;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
class TableAttendance
{
    public static function table()
    {
        return [
            TextColumn::make('name'),
            TextColumn::make('nip'),
            TextColumn::make('departement'),
            TextColumn::make('position'),
            TextColumn::make('level'),
            TextColumn::make('work_day'),
            // TextColumn::make('user.name'),
            // TextColumn::make('getUserDeptAttribute'),
            // TextColumn::make('schedule.work_day')->searchable()->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('time_in')->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('time_out')->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('lat_in')
                ->searchable()->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('lat_out')
                ->searchable()->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('long_in')
                ->searchable()->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('long_out')
                ->searchable()->toggleable(isToggledHiddenByDefault: true),
            ImageColumn::make('image_in')->toggleable(isToggledHiddenByDefault: true),
            ImageColumn::make('image_out')->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('status_in')->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('status_out')->toggleable(isToggledHiddenByDefault: true),
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
                ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->company->name} | NIP:{$record->nip} | {$record->name}")
                ->searchable()
                ->preload(),
            SelectFilter::make('level')
                ->label('Filter by level')
                ->relationship('lvl_relation', 'name', fn(Builder $query) => $query->with('company', 'departement'))
                ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->company->name} | Dept:{$record->departement->name} |Lvl: {$record->name}")
                ->searchable()
                ->preload(),
            SelectFilter::make('position')
                ->label('Filter by position')
                ->relationship('position_relation', 'name', fn(Builder $query) => $query->with('company', 'departement'))
                ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->company->name} | Dept:{$record->departement->name} |Lvl: {$record->name}")
                ->searchable()
                ->preload(),
            SelectFilter::make('departement')
                ->label('Filter by departement')
                ->relationship('departement_relation', 'name', fn(Builder $query) => $query->with('company'))
                ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->company->name} | Dept:{$record->name}")
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
