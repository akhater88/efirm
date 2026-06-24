<?php

namespace App\Filament\Admin\Resources\PlanResource\Pages;

use App\Enums\AdminActivityEventType;
use App\Filament\Admin\Resources\PlanResource;
use App\Services\AdminActivityLogService;
use Filament\Resources\Pages\EditRecord;

class EditPlan extends EditRecord
{
    protected static string $resource = PlanResource::class;

    protected function afterSave(): void
    {
        AdminActivityLogService::log(
            AdminActivityEventType::PlanUpdated,
            $this->record,
            ['plan_slug' => $this->record->slug],
        );
    }
}
