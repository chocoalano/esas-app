<?php

namespace App\Filament\App\Resources\PermitTypeResource\Pages;

use App\Filament\App\Resources\PermitTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePermitTypes extends ManageRecords
{
    protected static string $resource = PermitTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->visible(auth()->user()->hasAnyRole(['super_admin', 'Administrator']) ? true : false),
        ];
    }
}
