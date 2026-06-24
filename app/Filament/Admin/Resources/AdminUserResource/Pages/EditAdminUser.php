<?php

namespace App\Filament\Admin\Resources\AdminUserResource\Pages;

use App\Enums\AdminActivityEventType;
use App\Filament\Admin\Resources\AdminUserResource;
use App\Models\AdminUser;
use App\Services\AdminActivityLogService;
use Filament\Resources\Pages\EditRecord;

class EditAdminUser extends EditRecord
{
    protected static string $resource = AdminUserResource::class;

    protected function afterSave(): void
    {
        /** @var AdminUser $currentAdmin */
        $currentAdmin = auth()->guard('admin')->user();

        AdminActivityLogService::log(
            eventType: AdminActivityEventType::UserUpdated,
            admin: $currentAdmin,
            target: $this->record,
        );
    }
}
