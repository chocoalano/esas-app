<?php
namespace App\Filament\App\Tables\Config;

use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Carbon;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class TableCompany
{
    public static function table()
    {
        return [
            TextColumn::make('name')->searchable(),
            TextColumn::make('latitude')->searchable(),
            TextColumn::make('longitude')->searchable(),
            TextColumn::make('radius')->searchable(),
            TextColumn::make('full_address')->limit(30)->searchable(),
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
            DateRangeFilter::make('created_at')
                ->startDate(Carbon::now())
                ->endDate(Carbon::now())
        ];
    }
}
