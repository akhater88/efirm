<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\WorkspaceController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::get('me', [AuthController::class, 'me'])->name('api.v1.me');

    Route::post('workspaces', [WorkspaceController::class, 'store'])
        ->name('api.v1.workspaces.store');
    Route::post('workspaces/switch', [WorkspaceController::class, 'switch'])
        ->name('api.v1.workspaces.switch');

    Route::apiResource('contacts', ContactController::class);
});
