<?php

namespace App\Filament\App\Resources\JobLevelResource\Pages;

use App\Filament\App\Resources\JobLevelResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageJobLevels extends ManageRecords
{
    protected static string $resource = JobLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->visible(auth()->user()->hasAnyRole(['super_admin', 'Administrator']) ? true : false),
        ];
    }
}
