<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Forms\FormUser;
use App\Filament\App\Resources\UserResource\Pages;
use App\Filament\App\Tables\TableUser;
use App\Models\AdministrationApp\UserAttendance;
use App\Models\User;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;

class UserResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Account Users';
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
    public static function getGloballySearchableAttributes(): array
    {
        return ['nip', 'email', 'name'];
    }
    public static function form(Form $form): Form
    {
        return $form->schema(FormUser::form());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(TableUser::table())
            ->filters(TableUser::filter(), layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('reset_device_id')
                        ->visible(fn() => auth()->user()->hasAnyRole(['super_admin', 'Administrator']))
                        ->icon('heroicon-o-device-phone-mobile')
                        ->requiresConfirmation()
                        ->action(fn(User $record) => $record->update(['device_id' => null])),
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
            ])
            ->paginated([5, 10, 15, 20]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
