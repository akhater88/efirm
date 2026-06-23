<?php

namespace App\Filament\Resources\CourtReviews\Pages;

use App\Filament\Resources\CourtReviews\CourtReviewResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditCourtReview extends EditRecord
{
    protected static string $resource = CourtReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
