<?php

namespace App\Filament\Resources\ServiceLogEntries\Pages;

use App\Filament\Resources\ServiceLogEntries\ServiceLogEntryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditServiceLogEntry extends EditRecord
{
    protected static string $resource = ServiceLogEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
