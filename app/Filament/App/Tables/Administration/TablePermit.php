<?php
namespace App\Filament\App\Tables\Administration;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;

class TablePermit
{
    public static function table()
    {
        return [
            TextColumn::make('permit_numbers')
                ->searchable(),
            TextColumn::make('permitType.type')
                ->numeric()
                ->sortable(),
            TextColumn::make('userTimeworkSchedule.work_day')
                ->numeric()
                ->sortable(),
            TextColumn::make('timein_adjust')
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('timeout_adjust')
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('userTimeworkSchedule.timework.name')
                ->numeric()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('userTimeworkSchedule.timework.name')
                ->numeric()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('start_date')
                ->date()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('end_date')
                ->date()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('start_time'),
            TextColumn::make('end_time'),
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
            SelectFilter::make('type')
                ->label('Filter by type')
                ->relationship('permitType', 'type')
                ->preload()
                ->searchable(),
        ];
    }
    public static function tableType()
    {
        return [
            TextColumn::make('type')
                ->searchable(),
            ToggleColumn::make('is_payed')
                ->disabled(fn() => auth()->user()->can('update_permit::type') ? false : true),
            ToggleColumn::make('approve_line')
                ->disabled(fn() => auth()->user()->can('update_permit::type') ? false : true),
            ToggleColumn::make('approve_manager')
                ->disabled(fn() => auth()->user()->can('update_permit::type') ? false : true),
            ToggleColumn::make('approve_hr')
                ->disabled(fn() => auth()->user()->can('update_permit::type') ? false : true),
            ToggleColumn::make('with_file')
                ->disabled(fn() => auth()->user()->can('update_permit::type') ? false : true),
            ToggleColumn::make('show_mobile')
                ->disabled(fn() => auth()->user()->can('update_permit::type') ? false : true),
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
    public static function filterType()
    {
        return [];
    }
}
