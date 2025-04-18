<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Forms\ConfigGroup\FormAllmenuGroupConfig;
use App\Filament\App\Forms\FormConfig;
use App\Filament\App\Resources\DepartementResource\Pages;
use App\Filament\App\Resources\DepartementResource\RelationManagers;
use App\Filament\App\Tables\Config\TableDepartement;
use App\Models\CoreApp\Departement;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;

class DepartementResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Departement::class;
    protected static ?string $navigationGroup = 'Config';
    protected static ?string $navigationIcon = 'heroicon-s-rectangle-group';

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'replicate',
            'delete',
            'delete_any',
            'export',
            'import',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(FormAllmenuGroupConfig::departement())
                    ->columns(FormConfig::columns(1, 2, 2, 2))
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(TableDepartement::table())
            ->filters(TableDepartement::filter(), layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(2)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ReplicateAction::make()->visible(auth()->user()->hasAnyRole(['super_admin', 'Administrator']) ? true : false),
                    Tables\Actions\ViewAction::make()->visible(auth()->user()->hasAnyRole(['super_admin', 'Administrator']) ? true : false),
                    Tables\Actions\EditAction::make()->visible(auth()->user()->hasAnyRole(['super_admin', 'Administrator']) ? true : false),
                    Tables\Actions\DeleteAction::make()->visible(auth()->user()->hasAnyRole(['super_admin', 'Administrator']) ? true : false),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->visible(auth()->user()->hasAnyRole(['super_admin', 'Administrator']) ? true : false),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageDepartements::route('/'),
        ];
    }
}
