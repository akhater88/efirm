<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Models\WorkspaceSsoConfig;
use Illuminate\Support\Facades\DB;

function createSsoAuthUser(string $role = 'owner'): array
{
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->{$role}()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);
    $user->switchWorkspace($workspace);

    return [$user, $workspace];
}

it('creates an SSO config as owner', function () {
    [$user, $workspace] = createSsoAuthUser('owner');

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/sso-configs', [
        'provider_type' => 'saml2',
        'provider_name' => 'Corporate IdP',
        'idp_entity_id' => 'urn:example.com',
        'idp_sso_url' => 'https://idp.example.com/sso',
        'idp_certificate' => 'MIIC...certificate-data',
        'sp_entity_id' => 'urn:app:legalws',
        'attribute_mapping' => ['email' => 'emailAddress', 'name' => 'displayName'],
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.provider_type', 'saml2');
    $response->assertJsonPath('data.provider_name', 'Corporate IdP');
});

it('lists SSO configs', function () {
    [$user, $workspace] = createSsoAuthUser('owner');

    WorkspaceSsoConfig::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/sso-configs');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

it('updates an SSO config', function () {
    [$user, $workspace] = createSsoAuthUser('owner');

    $config = WorkspaceSsoConfig::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->patchJson("/api/v1/sso-configs/{$config->id}", [
        'provider_name' => 'Updated IdP',
        'enforce_for_domain' => 'company.com',
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.provider_name', 'Updated IdP');
    $response->assertJsonPath('data.enforce_for_domain', 'company.com');
});

it('denies SSO config creation to non-owner roles', function () {
    [$user, $workspace] = createSsoAuthUser('member');

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/sso-configs', [
        'provider_type' => 'saml2',
        'provider_name' => 'Corporate IdP',
        'idp_entity_id' => 'urn:example.com',
        'idp_sso_url' => 'https://idp.example.com/sso',
        'idp_certificate' => 'certificate-data',
        'sp_entity_id' => 'urn:app:legalws',
        'attribute_mapping' => ['email' => 'emailAddress'],
    ]);

    $response->assertForbidden();
});

it('enforces enforce_for_domain field storage', function () {
    [$user, $workspace] = createSsoAuthUser('owner');

    $config = WorkspaceSsoConfig::factory()
        ->withDomainEnforcement('acme.com')
        ->create(['workspace_id' => $workspace->id]);

    expect($config->fresh()->enforce_for_domain)->toBe('acme.com');
});

it('encrypts idp_certificate at rest', function () {
    [$user, $workspace] = createSsoAuthUser('owner');

    $config = WorkspaceSsoConfig::factory()->create([
        'workspace_id' => $workspace->id,
        'idp_certificate' => 'my-secret-certificate-data',
    ]);

    $raw = DB::table('workspace_sso_configs')
        ->where('id', $config->id)
        ->first();

    expect($raw->idp_certificate)->not->toBe('my-secret-certificate-data');

    $fresh = WorkspaceSsoConfig::withoutGlobalScopes()->find($config->id);
    expect($fresh->idp_certificate)->toBe('my-secret-certificate-data');
});
