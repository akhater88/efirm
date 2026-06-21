<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\AuthService;
use Laravel\Socialite\Two\User as SocialiteUser;

function createSocialiteUser(array $overrides = []): SocialiteUser
{
    $user = new SocialiteUser;
    $user->id = $overrides['id'] ?? 'google-999';
    $user->name = $overrides['name'] ?? 'Service Test User';
    $user->email = $overrides['email'] ?? 'service-test@example.com';
    $user->avatar = $overrides['avatar'] ?? 'https://example.com/avatar.jpg';
    $user->token = 'fake-token';

    return $user;
}

it('creates a new user from google data', function () {
    $service = new AuthService;
    $socialiteUser = createSocialiteUser();

    $user = $service->findOrCreateUserFromGoogle($socialiteUser);

    expect($user)->toBeInstanceOf(User::class);
    expect($user->email)->toBe('service-test@example.com');
    expect($user->google_id)->toBe('google-999');
});

it('creates a workspace named after user first name', function () {
    $service = new AuthService;
    $socialiteUser = createSocialiteUser(['name' => 'Ahmad Al-Hassan']);

    $service->findOrCreateUserFromGoogle($socialiteUser);

    $workspace = Workspace::first();
    expect($workspace->name)->toBe("Ahmad's Workspace");
});

it('creates owner membership for new user', function () {
    $service = new AuthService;
    $socialiteUser = createSocialiteUser();

    $user = $service->findOrCreateUserFromGoogle($socialiteUser);

    $member = WorkspaceMember::where('user_id', $user->id)->first();
    expect($member)->not->toBeNull();
    expect($member->role->value)->toBe('owner');
});

it('wraps creation in a database transaction', function () {
    $service = new AuthService;
    $socialiteUser = createSocialiteUser();

    $user = $service->findOrCreateUserFromGoogle($socialiteUser);

    // If transaction works, all three records exist
    expect(User::count())->toBe(1);
    expect(Workspace::count())->toBe(1);
    expect(WorkspaceMember::count())->toBe(1);
});

it('returns existing user when google_id matches', function () {
    $existing = User::factory()->create([
        'email' => 'service-test@example.com',
        'google_id' => 'google-999',
    ]);

    $service = new AuthService;
    $socialiteUser = createSocialiteUser();

    $user = $service->findOrCreateUserFromGoogle($socialiteUser);

    expect($user->id)->toBe($existing->id);
    expect(User::count())->toBe(1);
});

it('links google_id to user when google_id is null', function () {
    $existing = User::factory()->create([
        'email' => 'service-test@example.com',
        'google_id' => null,
    ]);

    $service = new AuthService;
    $socialiteUser = createSocialiteUser();

    $user = $service->findOrCreateUserFromGoogle($socialiteUser);

    expect($user->id)->toBe($existing->id);
    expect($user->google_id)->toBe('google-999');
});

it('returns null when google_id conflicts', function () {
    User::factory()->create([
        'email' => 'service-test@example.com',
        'google_id' => 'google-different',
    ]);

    $service = new AuthService;
    $socialiteUser = createSocialiteUser();

    $result = $service->findOrCreateUserFromGoogle($socialiteUser);

    expect($result)->toBeNull();
});

it('sets email_verified_at on new user creation', function () {
    $service = new AuthService;
    $socialiteUser = createSocialiteUser();

    $user = $service->findOrCreateUserFromGoogle($socialiteUser);

    expect($user->email_verified_at)->not->toBeNull();
});
