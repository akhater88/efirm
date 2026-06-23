<?php

namespace App\Filament\Resources\CourtReviews\Pages;

use App\Filament\Resources\CourtReviews\CourtReviewResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCourtReviews extends ListRecords
{
    protected static string $resource = CourtReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
