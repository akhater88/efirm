<?php

namespace App\Filament\Resources\Library\Pages;

use App\Filament\Resources\Library\LibraryClauseResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLibraryClause extends EditRecord
{
    protected static string $resource = LibraryClauseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
