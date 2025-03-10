<?php

namespace App\Filament\App\Resources\TimeWorkResource\Pages;

use App\Filament\App\Resources\TimeWorkResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageTimeWorks extends ManageRecords
{
    protected static string $resource = TimeWorkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->visible(auth()->user()->hasAnyRole(['super_admin', 'administrator']) ? true : false),
        ];
    }
}
