<?php

namespace App\Filament\App\Resources\JobPositionResource\Pages;

use App\Filament\App\Resources\JobPositionResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageJobPositions extends ManageRecords
{
    protected static string $resource = JobPositionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
