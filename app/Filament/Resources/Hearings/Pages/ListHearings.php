<?php

namespace App\Filament\Resources\Hearings\Pages;

use App\Filament\Resources\Hearings\HearingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHearings extends ListRecords
{
    protected static string $resource = HearingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
