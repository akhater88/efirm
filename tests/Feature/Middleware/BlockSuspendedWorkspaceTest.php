<?php

use App\Http\Middleware\BlockSuspendedWorkspace;
use App\Models\Subscription;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

beforeEach(function () {
    $this->workspace = Workspace::factory()->create();
    $this->middleware = new BlockSuspendedWorkspace;
});

function makeRequestWithSession(string $method, ?string $workspaceId = null): Request
{
    $request = Request::create('/test', $method);

    $session = app('session.store');
    if ($workspaceId) {
        $session->put('current_workspace_id', $workspaceId);
    }
    $request->setLaravelSession($session);

    return $request;
}

it('returns 403 for POST when workspace is suspended', function () {
    Subscription::factory()->suspended()->create([
        'workspace_id' => $this->workspace->id,
    ]);

    $request = makeRequestWithSession('POST', $this->workspace->id);

    $response = $this->middleware->handle($request, fn () => new Response('ok'));

    expect($response->getStatusCode())->toBe(403);
});

it('allows GET for suspended workspace', function () {
    Subscription::factory()->suspended()->create([
        'workspace_id' => $this->workspace->id,
    ]);

    $request = makeRequestWithSession('GET', $this->workspace->id);

    $response = $this->middleware->handle($request, fn () => new Response('ok'));

    expect($response->getStatusCode())->toBe(200);
});

it('allows POST for active workspace', function () {
    Subscription::factory()->active()->create([
        'workspace_id' => $this->workspace->id,
    ]);

    $request = makeRequestWithSession('POST', $this->workspace->id);

    $response = $this->middleware->handle($request, fn () => new Response('ok'));

    expect($response->getStatusCode())->toBe(200);
});

it('passes through when no subscription exists', function () {
    $request = makeRequestWithSession('POST', $this->workspace->id);

    $response = $this->middleware->handle($request, fn () => new Response('ok'));

    expect($response->getStatusCode())->toBe(200);
});

it('passes through when no workspace is selected', function () {
    $request = makeRequestWithSession('POST');

    $response = $this->middleware->handle($request, fn () => new Response('ok'));

    expect($response->getStatusCode())->toBe(200);
});

it('returns 403 for PUT when workspace is suspended', function () {
    Subscription::factory()->suspended()->create([
        'workspace_id' => $this->workspace->id,
    ]);

    $request = makeRequestWithSession('PUT', $this->workspace->id);

    $response = $this->middleware->handle($request, fn () => new Response('ok'));

    expect($response->getStatusCode())->toBe(403);
});

it('returns 403 for DELETE when workspace is suspended', function () {
    Subscription::factory()->suspended()->create([
        'workspace_id' => $this->workspace->id,
    ]);

    $request = makeRequestWithSession('DELETE', $this->workspace->id);

    $response = $this->middleware->handle($request, fn () => new Response('ok'));

    expect($response->getStatusCode())->toBe(403);
});
