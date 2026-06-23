<?php

namespace App\Filament\Resources\Hearings\Pages;

use App\Filament\Resources\Hearings\HearingResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditHearing extends EditRecord
{
    protected static string $resource = HearingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
