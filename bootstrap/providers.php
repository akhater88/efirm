<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\PlatformAdminPanelProvider;
use App\Providers\LlmServiceProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    PlatformAdminPanelProvider::class,
    LlmServiceProvider::class,
];
