<?php
namespace App\Filament\App\Tables;

use App\Models\CoreApp\Departement;
use App\Models\CoreApp\JobLevel;
use App\Models\User;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Carbon;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
class TableUser
{
    public static function table()
    {
        return [
            ImageColumn::make('avatar'),
            TextColumn::make('company.name'),
            TextColumn::make('nip'),
            TextColumn::make('roles.name')
                ->searchable(),
            TextColumn::make('name')
                ->searchable(),
            TextColumn::make('email')
                ->searchable(),
            TextColumn::make('email_verified_at')
                ->dateTime()
                ->sortable(),
            TextColumn::make('status')
                ->badge()
                ->color(fn(string $state): string => match ($state) {
                    'inactive' => 'warning',
                    'active' => 'success',
                    'resign' => 'danger',
                }),
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
            SelectFilter::make('status')
                ->label('Filter by status')
                ->options(User::STATUS)
                ->preload(),
            SelectFilter::make('company')
                ->label('Filter by company')
                ->relationship('company', 'name')
                ->preload(),
            SelectFilter::make('roles')
                ->label('Filter by role')
                ->relationship('roles', 'name')
                ->preload(),
            DateRangeFilter::make('created_at')
                ->startDate(Carbon::now())
                ->endDate(Carbon::now())
        ];
    }
}
