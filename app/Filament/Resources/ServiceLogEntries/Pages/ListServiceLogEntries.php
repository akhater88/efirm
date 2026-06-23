<?php

namespace App\Filament\Resources\ServiceLogEntries\Pages;

use App\Filament\Resources\ServiceLogEntries\ServiceLogEntryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListServiceLogEntries extends ListRecords
{
    protected static string $resource = ServiceLogEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
