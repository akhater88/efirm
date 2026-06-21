<?php

namespace App\Filament\Resources\Obligations\Pages;

use App\Filament\Resources\Obligations\ObligationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditObligation extends EditRecord
{
    protected static string $resource = ObligationResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
