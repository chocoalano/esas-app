<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Forms\Attendance\FormAttendance;
use App\Filament\App\Forms\FormConfig;
use App\Filament\App\Resources\UserTimeworkScheduleResource\Pages;
use App\Filament\App\Resources\UserTimeworkScheduleResource\RelationManagers;
use App\Filament\App\Tables\Administration\TableTimeAttendanceSchedule;
use App\Models\AdministrationApp\UserTimeworkSchedule;
use App\Models\User;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;

class UserTimeworkScheduleResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = UserTimeworkSchedule::class;
    protected static ?string $navigationLabel = 'Time Attendance Schedule';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?string $navigationIcon = 'gmdi-calendar-month-o';
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
                Section::make(FormAttendance::timework_schedule())
                    ->columns(FormConfig::columns(1, 2, 2, 2))
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // ->modifyQueryUsing(function (Builder $query) {
            //     if(auth()->user()->hasRole('super_admin')||auth()->user()->hasRole('Administrator')){
            //         return $query;
            //     } else {
            //         $user = User::whereHas('employee', function($q){
            //             $q->where('departement_id', auth()->user()->employee->departement_id);
            //         })->get();
            //         $userId = [];
            //         foreach ($user as $k) {
            //             array_push($userId, $k['id']);
            //         }
            //         return $query->whereIn('user_id', $userId);
            //     }
            // })
            ->query(UserTimeworkSchedule::query())
            ->columns(TableTimeAttendanceSchedule::table())
            ->filters(TableTimeAttendanceSchedule::filter(), layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\DeleteAction::make()->visible(auth()->user()->hasAnyRole(['super_admin', 'Administrator']) ? true : false),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->visible(auth()->user()->hasAnyRole(['super_admin', 'Administrator']) ? true : false),
                ]),
            ])
            ->paginated([5,10,15,20]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUserTimeworkSchedules::route('/'),
        ];
    }
}
