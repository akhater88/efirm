<?php

namespace App\Jobs;

use App\Services\PdplRetentionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PurgeExpiredWorkspacesJob implements ShouldQueue
{
    use Queueable;

    public function handle(PdplRetentionService $service): void
    {
        $expired = $service->getExpiredRetentions();

        foreach ($expired as $subscription) {
            $service->purgeExpiredWorkspace($subscription);
        }
    }
}
