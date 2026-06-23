<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLawyerProfileRequest;
use App\Http\Requests\UpdateLawyerProfileRequest;
use App\Http\Resources\LawyerProfileResource;
use App\Models\LawyerProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LawyerProfileController extends Controller
{
    /**
     * Restricted fields that only Owner/Admin can update.
     */
    private const RESTRICTED_FIELDS = [
        'bar_admission_number',
        'bar_admission_country',
        'bar_admission_date',
        'default_hourly_rate',
        'default_currency',
        'jurisdictions',
        'practice_areas',
        'joined_firm_date',
        'status',
    ];

    public function index(Request $request): AnonymousResourceCollection
    {
        $workspace = $request->user()->currentWorkspace();

        $query = LawyerProfile::query()
            ->whereHas('user', function ($q) use ($workspace) {
                $q->whereHas('workspaceMembers', function ($wm) use ($workspace) {
                    $wm->where('workspace_id', $workspace->id);
                });
            });

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('practice_area')) {
            $query->whereJsonContains('practice_areas', $request->input('practice_area'));
        }

        return LawyerProfileResource::collection(
            $query->with('user')->latest()->paginate(15)
        );
    }

    public function store(StoreLawyerProfileRequest $request): JsonResponse
    {
        $profile = LawyerProfile::create(array_merge(
            $request->validated(),
            [
                'created_by_user_id' => $request->user()->id,
                'updated_by_user_id' => $request->user()->id,
            ]
        ));

        return (new LawyerProfileResource($profile->load('user')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(LawyerProfile $lawyerProfile): LawyerProfileResource
    {
        $this->authorize('view', $lawyerProfile);

        return new LawyerProfileResource($lawyerProfile->load('user'));
    }

    public function update(UpdateLawyerProfileRequest $request, LawyerProfile $lawyerProfile): LawyerProfileResource
    {
        $validated = $request->validated();

        // If the user is updating their own profile and is NOT Owner/Admin,
        // strip restricted fields from the validated data.
        $user = $request->user();
        $isOwnProfile = $lawyerProfile->user_id === $user->id;
        $isPrivileged = in_array($user->roleInWorkspace($user->currentWorkspace()), [Role::Owner, Role::Admin], true);

        if ($isOwnProfile && ! $isPrivileged) {
            $validated = array_diff_key($validated, array_flip(self::RESTRICTED_FIELDS));
        }

        $lawyerProfile->update(array_merge(
            $validated,
            ['updated_by_user_id' => $user->id]
        ));

        return new LawyerProfileResource($lawyerProfile->fresh()->load('user'));
    }

    public function destroy(LawyerProfile $lawyerProfile): JsonResponse
    {
        $this->authorize('delete', $lawyerProfile);

        $lawyerProfile->delete();

        return response()->json(null, 204);
    }
}
