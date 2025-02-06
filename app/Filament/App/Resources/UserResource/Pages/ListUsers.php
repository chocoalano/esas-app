<?php

namespace App\Filament\App\Resources\UserResource\Pages;

use App\Filament\App\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('setup_role_users')
                ->outlined()
                ->color('primary')
                ->icon('heroicon-o-key')
                ->visible(fn()=>auth()->user()->hasRole('super_admin'))
                ->form([
                    Select::make('role_id')
                        ->label('Select Role')
                        ->options(Role::all()->pluck('name', 'name'))
                        ->searchable()
                        ->preload()
                        ->multiple()
                        ->required()
                        ->columnSpanFull(),
                    CheckboxList::make('user_id')
                        ->label('Select Users')
                        ->options(User::all()->pluck('name', 'id'))
                        ->searchable()
                        ->columns(2)
                        ->gridDirection('row')
                ])
                ->action(function (array $data): void {
                    DB::transaction(function () use ($data) {
                        foreach ($data['user_id'] as $userId) {
                            $user = User::findOrFail($userId);
                            $user->syncRoles($data['role_id']);
                        }
                    });
                }),
            Actions\CreateAction::make(),
        ];
    }
}
