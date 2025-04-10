<?php
namespace App\Filament\App\Tables\Administration;

use App\Models\AdministrationApp\UserAttendance;
use App\Models\CoreApp\Company;
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
            SelectFilter::make('company_id')
                ->label('Filter by company')
                ->options(fn() => Company::pluck('name', 'id')),

            SelectFilter::make('departement')
                ->multiple()
                ->label('Filter by departement')
                ->relationship('departement_relation', 'name', fn(Builder $query) => $query->with('company'))
                ->getOptionLabelFromRecordUsing(fn($record) => "{$record->name}")
                ->searchable()
                ->preload()
                ->query(function (Builder $query, array $data) {
                    if (isset($data['company_id'])) {
                        $query->where('company_id', $data['company_id']);
                    }
                }),

            SelectFilter::make('user_id')
                ->multiple()
                ->label('Filter by user')
                ->relationship('user', 'name', fn(Builder $query) => $query->with('company', 'employee'))
                ->getOptionLabelFromRecordUsing(fn($record) => "{$record->nip} | {$record->name}")
                ->searchable()
                ->preload()
                ->query(function (Builder $query, array $data) {
                    if (isset($data['company_id'])) {
                        $query->whereHas('company', fn($q) => $q->where('id', $data['company_id']));
                    }
                    if (!empty($data['departement'])) {
                        $query->whereHas('employee', fn($q) => $q->whereIn('departement_id', $data['departement']));
                    }
                }),

            SelectFilter::make('level')
                ->multiple()
                ->label('Filter by level')
                ->relationship('lvl_relation', 'name', fn(Builder $query) => $query->with('company', 'departement'))
                ->getOptionLabelFromRecordUsing(fn($record) => "{$record->name}")
                ->searchable()
                ->preload()
                ->query(function (Builder $query, array $data) {
                    if (isset($data['company_id'])) {
                        $query->where('company_id', $data['company_id']);
                    }
                }),

            SelectFilter::make('position')
                ->multiple()
                ->label('Filter by position')
                ->relationship('position_relation', 'name', fn(Builder $query) => $query->with('company', 'departement'))
                ->getOptionLabelFromRecordUsing(fn($record) => "{$record->name}")
                ->searchable()
                ->preload()
                ->query(function (Builder $query, array $data) {
                    if (isset($data['company_id'])) {
                        $query->where('company_id', $data['company_id']);
                    }
                }),

            DateRangeFilter::make('created_at')
                ->maxDate(now()->addMonth()),

            SelectFilter::make('status_in')
                ->options(UserAttendance::STATUS),

            SelectFilter::make('status_out')
                ->options(UserAttendance::STATUS),
        ];
    }
}
