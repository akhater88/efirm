<?php

namespace App\Filament\Resources\Library\Pages;

use App\Filament\Resources\Library\LibraryClauseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLibraryClauses extends ListRecords
{
    protected static string $resource = LibraryClauseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
