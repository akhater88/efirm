<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmailIntegrationRequest;
use App\Http\Requests\UpdateEmailIntegrationRequest;
use App\Http\Resources\EmailAttachmentResource;
use App\Http\Resources\EmailIntegrationResource;
use App\Models\EmailAttachment;
use App\Models\EmailIntegration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EmailIntegrationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return EmailIntegrationResource::collection(
            EmailIntegration::query()->latest()->paginate(15)
        );
    }

    public function store(StoreEmailIntegrationRequest $request): JsonResponse
    {
        $integration = EmailIntegration::create(array_merge(
            $request->validated(),
            [
                'user_id' => $request->user()->id,
                'created_by_user_id' => $request->user()->id,
                'updated_by_user_id' => $request->user()->id,
            ]
        ));

        return (new EmailIntegrationResource($integration))
            ->response()
            ->setStatusCode(201);
    }

    public function show(EmailIntegration $emailIntegration): EmailIntegrationResource
    {
        $this->authorize('view', $emailIntegration);

        return new EmailIntegrationResource($emailIntegration);
    }

    public function update(UpdateEmailIntegrationRequest $request, EmailIntegration $emailIntegration): EmailIntegrationResource
    {
        $emailIntegration->update(array_merge(
            $request->validated(),
            ['updated_by_user_id' => $request->user()->id]
        ));

        return new EmailIntegrationResource($emailIntegration->fresh());
    }

    public function destroy(EmailIntegration $emailIntegration): JsonResponse
    {
        $this->authorize('delete', $emailIntegration);

        $emailIntegration->delete();

        return response()->json(null, 204);
    }

    /**
     * Attach an email to a resource (mock — stub for actual email fetch).
     */
    public function attachEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email_integration_id' => 'required|exists:email_integrations,id',
            'attached_to_type' => 'required|string|max:100',
            'attached_to_id' => 'required|string|max:26',
            'email_provider_id' => 'required|string|max:255',
            'subject' => 'required|string|max:500',
            'from_address' => 'required|email|max:255',
            'from_name' => 'nullable|string|max:255',
            'to_addresses' => 'required|array',
            'to_addresses.*' => 'email',
            'cc_addresses' => 'nullable|array',
            'cc_addresses.*' => 'email',
            'received_at' => 'required|date',
            'body_snippet' => 'required|string',
            'has_attachments' => 'sometimes|boolean',
            'attachment_files' => 'nullable|array',
            'is_outbound' => 'sometimes|boolean',
        ]);

        $attachment = EmailAttachment::create(array_merge(
            $validated,
            [
                'created_by_user_id' => $request->user()->id,
                'updated_by_user_id' => $request->user()->id,
            ]
        ));

        return (new EmailAttachmentResource($attachment))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Mock email fetch — stub for actual provider integration.
     */
    public function fetchEmails(EmailIntegration $emailIntegration): JsonResponse
    {
        $this->authorize('view', $emailIntegration);

        // Stub: In production, this would call the email provider's API
        return response()->json([
            'data' => [],
            'message' => __('integrations.email_fetch_stub'),
        ]);
    }

    /**
     * Mock email send — stub for actual provider integration.
     */
    public function sendEmail(Request $request, EmailIntegration $emailIntegration): JsonResponse
    {
        $this->authorize('view', $emailIntegration);

        $request->validate([
            'to' => 'required|array',
            'to.*' => 'email',
            'subject' => 'required|string|max:500',
            'body' => 'required|string',
        ]);

        // Stub: In production, this would call the email provider's API
        return response()->json([
            'message' => __('integrations.email_send_stub'),
        ]);
    }
}
