<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AuditLogController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', AuditLog::class);

        $query = AuditLog::query();

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('auditable_type')) {
            $query->where('auditable_type', $request->input('auditable_type'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        return AuditLogResource::collection(
            $query->latest()->paginate(25)
        );
    }

    public function show(AuditLog $auditLog): AuditLogResource
    {
        $this->authorize('view', $auditLog);

        return new AuditLogResource($auditLog);
    }
}
