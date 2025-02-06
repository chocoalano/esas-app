<?php

namespace App\Filament\App\Resources\AnnouncementResource\Pages;

use App\Filament\App\Resources\AnnouncementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAnnouncement extends EditRecord
{
    protected static string $resource = AnnouncementResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
