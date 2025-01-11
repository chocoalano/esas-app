<?php

namespace App\Filament\App\Resources\UserAttendanceResource\Pages;

use App\Filament\App\Forms\Attendance\FormAttendance;
use App\Filament\App\Forms\FormConfig;
use App\Filament\App\Resources\UserAttendanceResource;
use App\Repositories\Interfaces\AdministrationApp\AttendanceInterface;
use Filament\Actions;
use Filament\Forms\Components\Section;
use Filament\Resources\Pages\ManageRecords;

class ManageUserAttendances extends ManageRecords
{
    protected static string $resource = UserAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('presence')
            ->visible(fn()=>auth()->user()->can('create_user::attendance'))
                ->outlined()
                ->form([
                    Section::make(FormAttendance::presence())
                        ->columns(FormConfig::columns(1, 2, 3, 3))
                ])
                ->action(function (array $data) {
                    $process = app(AttendanceInterface::class);
                    if ($data['type'] === 'in') {
                        return $process->presence_in($data);
                    } else {
                        return $process->presence_out($data);
                    }
                }),
        ];
    }
}
