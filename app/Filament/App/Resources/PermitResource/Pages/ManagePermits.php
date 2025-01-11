<?php

namespace App\Filament\App\Resources\PermitResource\Pages;

use App\Filament\App\Resources\PermitResource;
use App\Repositories\Interfaces\AdministrationApp\PermitInterface;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePermits extends ManageRecords
{
    protected static string $resource = PermitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->action(function (array $data) {
                    app(PermitInterface::class)->create($data);
                }),
        ];
    }
}
