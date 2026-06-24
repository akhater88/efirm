<?php

namespace App\Filament\Admin\Resources\PlanResource\Pages;

use App\Enums\AdminActivityEventType;
use App\Filament\Admin\Resources\PlanResource;
use App\Services\AdminActivityLogService;
use Filament\Resources\Pages\CreateRecord;

class CreatePlan extends CreateRecord
{
    protected static string $resource = PlanResource::class;

    protected function afterCreate(): void
    {
        AdminActivityLogService::log(
            AdminActivityEventType::PlanCreated,
            $this->record,
            ['plan_slug' => $this->record->slug],
        );
    }
}
