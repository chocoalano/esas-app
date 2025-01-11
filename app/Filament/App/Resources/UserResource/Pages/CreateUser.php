<?php

namespace App\Filament\App\Resources\UserResource\Pages;

use App\Filament\App\Resources\UserResource;
use App\Repositories\Interfaces\CoreApp\UserInterface;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $proses = app(UserInterface::class)->create($data);
        return $proses ? $proses->toArray() : $data;
    }
}
