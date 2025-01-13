<?php

namespace App\Filament\App\Resources\BugReportResource\Pages;

use App\Filament\App\Resources\BugReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageBugReports extends ManageRecords
{
    protected static string $resource = BugReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->mutateFormDataUsing(function (array $data): array {
                $data['company_id'] = auth()->user()->company_id;
                $data['status'] = false;
                $data['user_id'] = auth()->id();

                return $data;
            }),
        ];
    }
}
