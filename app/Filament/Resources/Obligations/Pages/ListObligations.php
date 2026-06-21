<?php

namespace App\Filament\Resources\Obligations\Pages;

use App\Filament\Resources\Obligations\ObligationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListObligations extends ListRecords
{
    protected static string $resource = ObligationResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
