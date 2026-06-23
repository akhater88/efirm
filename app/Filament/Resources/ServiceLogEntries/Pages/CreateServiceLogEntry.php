<?php

namespace App\Filament\Resources\ServiceLogEntries\Pages;

use App\Filament\Resources\ServiceLogEntries\ServiceLogEntryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceLogEntry extends CreateRecord
{
    protected static string $resource = ServiceLogEntryResource::class;
}
