<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Forms\Attendance\FormAttendance;
use App\Filament\App\Forms\FormConfig;
use App\Filament\App\Resources\UserAttendanceResource\Pages;
use App\Filament\App\Resources\UserAttendanceResource\RelationManagers;
use App\Filament\App\Tables\Administration\TableAttendance;
use App\Models\AdministrationApp\UserAttendance;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;

class UserAttendanceResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = UserAttendance::class;
    protected static ?string $navigationLabel = 'Attendance';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?string $navigationIcon = 'gmdi-fingerprint-s';
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
                Section::make(FormAttendance::form())
                    ->columns(FormConfig::columns(1, 3, 4, 4))
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(TableAttendance::table())
            ->filters(TableAttendance::filter(), layout: FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ReplicateAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUserAttendances::route('/'),
        ];
    }
}
