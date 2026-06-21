<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

function mockSocialiteUser(array $overrides = []): SocialiteUser
{
    $user = new SocialiteUser;
    $user->id = $overrides['id'] ?? 'google-123456';
    $user->name = $overrides['name'] ?? 'Test User';
    $user->email = $overrides['email'] ?? 'test@example.com';
    $user->avatar = $overrides['avatar'] ?? 'https://example.com/avatar.jpg';
    $user->token = 'fake-token';

    return $user;
}

it('redirects to Google OAuth', function () {
    $response = $this->get(route('auth.google.redirect'));

    $response->assertRedirect();
    expect($response->headers->get('Location'))->toContain('accounts.google.com');
});

it('creates user and workspace on first Google sign-in', function () {
    $socialiteUser = mockSocialiteUser();

    Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

    $this->get(route('auth.google.callback'));

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'google_id' => 'google-123456',
    ]);
    $this->assertDatabaseCount('workspaces', 1);
});

it('creates workspace member with owner role on first sign-in', function () {
    $socialiteUser = mockSocialiteUser();

    Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

    $this->get(route('auth.google.callback'));

    $user = User::where('email', 'test@example.com')->first();
    $member = WorkspaceMember::where('user_id', $user->id)->first();

    expect($member->role->value)->toBe('owner');
});

it('sets default locale to ar for new users', function () {
    $socialiteUser = mockSocialiteUser();

    Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

    $this->get(route('auth.google.callback'));

    $user = User::where('email', 'test@example.com')->first();
    expect($user->preferred_locale)->toBe('ar');
});

it('reuses existing user on second sign-in with same google_id', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'google_id' => 'google-123456',
    ]);

    $socialiteUser = mockSocialiteUser();

    Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

    $this->get(route('auth.google.callback'));

    expect(User::count())->toBe(1);
});

it('does not create a new workspace on second sign-in', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'google_id' => 'google-123456',
    ]);
    $workspace = Workspace::factory()->create(['created_by_user_id' => $user->id]);
    WorkspaceMember::factory()->owner()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);

    $socialiteUser = mockSocialiteUser();

    Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

    $this->get(route('auth.google.callback'));

    expect(Workspace::count())->toBe(1);
});

it('updates name and avatar on returning sign-in', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'google_id' => 'google-123456',
        'name' => 'Old Name',
        'avatar_url' => null,
    ]);

    $socialiteUser = mockSocialiteUser([
        'name' => 'New Name',
        'avatar' => 'https://example.com/new-avatar.jpg',
    ]);

    Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

    $this->get(route('auth.google.callback'));

    $user->refresh();
    expect($user->name)->toBe('New Name');
    expect($user->avatar_url)->toBe('https://example.com/new-avatar.jpg');
});

it('links google_id when existing user has null google_id', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'google_id' => null,
    ]);

    $socialiteUser = mockSocialiteUser();

    Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

    $this->get(route('auth.google.callback'));

    $user->refresh();
    expect($user->google_id)->toBe('google-123456');
});

it('rejects sign-in when google_id conflicts with existing user', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'google_id' => 'google-different-id',
    ]);

    $socialiteUser = mockSocialiteUser();

    Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

    $response = $this->get(route('auth.google.callback'));

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error');
});

it('redirects to login with error on google_id conflict', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'google_id' => 'google-other-id',
    ]);

    $socialiteUser = mockSocialiteUser();

    Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

    $response = $this->get(route('auth.google.callback'));

    $response->assertRedirect(route('login'));
});

it('redirects to login with error when Google OAuth fails', function () {
    Socialite::shouldReceive('driver->user')->andThrow(new Exception('OAuth failed'));

    $response = $this->get(route('auth.google.callback'));

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error');
});

it('sets current workspace in session after sign-in', function () {
    $socialiteUser = mockSocialiteUser();

    Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

    $this->get(route('auth.google.callback'));

    expect(session('current_workspace_id'))->not->toBeNull();
});

it('redirects to intended URL after sign-in', function () {
    $socialiteUser = mockSocialiteUser();

    Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

    $response = $this->get(route('auth.google.callback'));

    $response->assertRedirect(route('dashboard'));
});

it('logs out and invalidates session', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $response->assertRedirect(route('login'));
    $this->assertGuest();
});

it('regenerates CSRF token on logout', function () {
    $user = User::factory()->create();

    $oldToken = session()->token();

    $this->actingAs($user)->post(route('logout'));

    expect(session()->token())->not->toBe($oldToken);
});

it('redirects to login after logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $response->assertRedirect(route('login'));
});
