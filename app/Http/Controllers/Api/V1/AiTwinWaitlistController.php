<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAiTwinWaitlistRequest;
use App\Models\AiTwinWaitlistEntry;

class AiTwinWaitlistController extends Controller
{
    public function store(StoreAiTwinWaitlistRequest $request)
    {
        AiTwinWaitlistEntry::firstOrCreate(
            ['email' => $request->validated('email')],
            [
                'locale' => app()->getLocale(),
                'workspace_id' => $request->user()?->currentWorkspace?->id,
            ]
        );

        return response()->json([
            'message' => __('brand.waitlist_success'),
        ]);
    }
}
