<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\InvitationController;
use App\Http\Controllers\Api\V1\MatterController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\WorkspaceController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::get('me', [AuthController::class, 'me'])->name('api.v1.me');

    Route::post('workspaces', [WorkspaceController::class, 'store'])
        ->name('api.v1.workspaces.store');
    Route::post('workspaces/switch', [WorkspaceController::class, 'switch'])
        ->name('api.v1.workspaces.switch');

    Route::get('workspaces/{workspace}/invitations', [InvitationController::class, 'index'])
        ->name('api.v1.invitations.index');
    Route::post('workspaces/{workspace}/invitations', [InvitationController::class, 'store'])
        ->name('api.v1.invitations.store');
    Route::delete('workspaces/{workspace}/invitations/{invitation}', [InvitationController::class, 'destroy'])
        ->name('api.v1.invitations.destroy');
    Route::post('invitations/accept', [InvitationController::class, 'accept'])
        ->name('api.v1.invitations.accept');

    Route::get('search', SearchController::class)->name('api.v1.search');

    Route::apiResource('contacts', ContactController::class);

    Route::apiResource('matters', MatterController::class);
    Route::post('matters/{matter}/counterparties', [MatterController::class, 'attachCounterparty'])
        ->name('api.v1.matters.counterparties.attach');
    Route::delete('matters/{matter}/counterparties/{contact}', [MatterController::class, 'detachCounterparty'])
        ->name('api.v1.matters.counterparties.detach');
    Route::post('matters/{matter}/lawyers', [MatterController::class, 'attachLawyer'])
        ->name('api.v1.matters.lawyers.attach');
    Route::delete('matters/{matter}/lawyers/{user}', [MatterController::class, 'detachLawyer'])
        ->name('api.v1.matters.lawyers.detach');
});
