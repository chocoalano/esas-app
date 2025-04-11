<?php
namespace App\Filament\App\Tables;

use App\Models\CoreApp\Departement;
use App\Models\CoreApp\JobLevel;
use App\Models\User;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Carbon;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
class TableUser
{
    public static function table()
    {
        return [
            ImageColumn::make('avatar')
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('company.name')
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('nip'),
            TextColumn::make('roles.name')
                ->listWithLineBreaks()
                ->searchable(),
            TextColumn::make('name')
                ->searchable(),
            TextColumn::make('email')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('email_verified_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            SelectColumn::make('status')
                ->options([
                    'inactive' => 'inactive',
                    'active' => 'active',
                    'resign' => 'resign',
                ])
                ->toggleable(isToggledHiddenByDefault: true),
            TextInputColumn::make('employee.saldo_cuti')
                ->label('Saldo Cuti')
                ->rules(['required', 'numeric', 'min:0', 'max:12'])
                ->beforeStateUpdated(function ($record, $state) {
                    // Simpan saldo_cuti baru ke relasi employee
                    if ($record->employee) {
                        $record->employee->saldo_cuti = $state;
                        $record->employee->save(); // Jangan lupa simpan perubahannya
                    }
                })
                ->toggleable(isToggledHiddenByDefault: true),
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
