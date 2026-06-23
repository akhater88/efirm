<?php

use App\Filament\Resources\CourtReviews\CourtReviewResource;
use App\Filament\Resources\Hearings\HearingResource;
use App\Filament\Resources\Matters\MatterResource;
use App\Filament\Resources\ServiceLogEntries\ServiceLogEntryResource;

it('MatterResource has Case Management navigation group', function () {
    expect(MatterResource::getNavigationGroup())->toBe(__('navigation.groups.case_management'));
});

it('HearingResource has Case Management navigation group', function () {
    expect(HearingResource::getNavigationGroup())->toBe(__('navigation.groups.case_management'));
});

it('CourtReviewResource has Case Management navigation group', function () {
    expect(CourtReviewResource::getNavigationGroup())->toBe(__('navigation.groups.case_management'));
});

it('ServiceLogEntryResource has Case Management navigation group', function () {
    expect(ServiceLogEntryResource::getNavigationGroup())->toBe(__('navigation.groups.case_management'));
});
