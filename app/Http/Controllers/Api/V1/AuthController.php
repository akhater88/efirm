<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function me(Request $request): UserResource
    {
        $user = $request->user();
        $user->load('workspaceMembers.workspace');

        return new UserResource($user);
    }
}
