<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Forms\Attendance\FormAttendance;
use App\Filament\App\Forms\FormConfig;
use App\Filament\App\Resources\UserAttendanceResource\Pages;
use App\Filament\App\Resources\UserAttendanceResource\RelationManagers;
use App\Filament\App\Tables\Administration\TableAttendance;
use App\Models\AdministrationApp\UserAttendance;
use App\Models\User;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use App\Models\views\AttendanceView;
use App\Repositories\Interfaces\AdministrationApp\AttendanceInterface;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

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
            ->query(AttendanceView::query())
            ->modifyQueryUsing(function (Builder $query) {
                if (auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('Administrator')) {
                    return $query;
                } else {
                    $id = auth()->user()->employee->departement_id;
                    return $query->where('departement_id', $id);
                }
            })
            ->columns(TableAttendance::table())
            ->filters(TableAttendance::filter(), layout: FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ReplicateAction::make()->visible(auth()->user()->hasAnyRole(['super_admin']) ? true : false),
                    Tables\Actions\ViewAction::make()->visible(auth()->user()->hasAnyRole(['super_admin', 'Administrator']) ? true : false),
                    Tables\Actions\EditAction::make()->visible(auth()->user()->hasAnyRole(['super_admin', 'Administrator']) ? true : false),
                    Tables\Actions\Action::make('koreksi')
                        ->icon('heroicon-o-pencil-square')
                        ->form(FormAttendance::koreksi_absen())
                        ->action(function (array $data, UserAttendance $record): void {
                            $proses = app(AttendanceInterface::class)->correction($record, $data);

                            if ($proses === true) {
                                Notification::make()
                                    ->title('Saved successfully')
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Saved unsuccessfully')
                                    ->body(is_string($proses) ? $proses : 'Unknown error')
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->visible(fn() => Auth::user()->hasRole(['Administrator', 'super_admin'])),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUserAttendances::route('/'),
        ];
    }
}
