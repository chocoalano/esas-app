<?php
namespace App\Filament\App\Tables\Config;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Carbon;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class TableJobPosition
{
    public static function table()
    {
        return [
            TextColumn::make('company.name')->searchable(),
            TextColumn::make('departement.name')->searchable(),
            TextColumn::make('name')->searchable(),
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
            SelectFilter::make('company')
                ->label('Filter by company')
                ->relationship('company', 'name')
                ->searchable()
                ->preload(),
            SelectFilter::make('department')
                ->label('Filter by departement')
                ->relationship('departement', 'name')
                ->searchable()
                ->preload(),
            DateRangeFilter::make('created_at')
                ->startDate(Carbon::now())
                ->endDate(Carbon::now())
        ];
    }
}
