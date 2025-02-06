<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Forms\Attendance\FormPermit;
use App\Filament\App\Forms\FormConfig;
use App\Filament\App\Resources\PermitTypeResource\Pages;
use App\Filament\App\Resources\PermitTypeResource\RelationManagers;
use App\Filament\App\Tables\Administration\TablePermit;
use App\Models\AdministrationApp\PermitType;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PermitTypeResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = PermitType::class;
    protected static ?string $navigationLabel = 'Permit Type';
    protected static ?string $navigationGroup = 'Config';
    protected static ?string $navigationIcon = 'gmdi-type-specimen-o';
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
                Section::make(FormPermit::type())
                    ->columns(FormConfig::columns(1, 2, 3, 4))
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(TablePermit::tableType())
            ->filters(TablePermit::filterType())
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
            'index' => Pages\ManagePermitTypes::route('/'),
        ];
    }
}
