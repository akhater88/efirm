<?php

namespace App\Filament\Admin\Resources\AdminUserResource\Pages;

use App\Enums\AdminActivityEventType;
use App\Filament\Admin\Resources\AdminUserResource;
use App\Models\AdminUser;
use App\Services\AdminActivityLogService;
use Filament\Resources\Pages\CreateRecord;

class CreateAdminUser extends CreateRecord
{
    protected static string $resource = AdminUserResource::class;

    protected function afterCreate(): void
    {
        /** @var AdminUser $currentAdmin */
        $currentAdmin = auth()->guard('admin')->user();

        AdminActivityLogService::log(
            eventType: AdminActivityEventType::UserCreated,
            admin: $currentAdmin,
            target: $this->record,
        );
    }
}
