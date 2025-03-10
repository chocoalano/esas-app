<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Forms\ConfigGroup\FormAllmenuGroupConfig;
use App\Filament\App\Forms\FormConfig;
use App\Filament\App\Resources\CompanyResource\Pages;
use App\Filament\App\Tables\Config\TableCompany;
use App\Models\CoreApp\Company;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CompanyResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Company::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Config';

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
                Section::make(FormAllmenuGroupConfig::company())
                    ->columns(FormConfig::columns(1, 3, 4, 4))
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(TableCompany::table())
            ->filters(TableCompany::filter())
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ReplicateAction::make()
                        ->beforeReplicaSaved(function (Model $replica): void {
                            if (isset($replica->name)) {
                                $replica->name = $replica->name . '-Copy' . now()->format('YmdHis');
                                $replica->latitude = fake()->latitude();
                                $replica->longitude = fake()->longitude();
                                $replica->radius = fake()->numerify('##');
                                $replica->full_address = fake()->address();
                            }
                        }),
                    Tables\Actions\ViewAction::make()->visible(auth()->user()->hasAnyRole(['super_admin', 'administrator']) ? true : false),
                    Tables\Actions\EditAction::make()->visible(auth()->user()->hasAnyRole(['super_admin', 'administrator']) ? true : false),
                    Tables\Actions\DeleteAction::make()->visible(auth()->user()->hasAnyRole(['super_admin', 'administrator']) ? true : false),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->visible(auth()->user()->hasAnyRole(['super_admin', 'administrator']) ? true : false),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCompanies::route('/'),
        ];
    }
}
