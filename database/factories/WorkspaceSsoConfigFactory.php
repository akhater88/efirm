<?php

namespace Database\Factories;

use App\Models\Workspace;
use App\Models\WorkspaceSsoConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkspaceSsoConfig>
 */
class WorkspaceSsoConfigFactory extends Factory
{
    protected $model = WorkspaceSsoConfig::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'provider_type' => 'saml2',
            'provider_name' => fake()->company().' SSO',
            'idp_metadata_url' => fake()->url(),
            'idp_metadata_xml' => null,
            'idp_entity_id' => 'urn:'.fake()->domainName(),
            'idp_sso_url' => fake()->url().'/sso/saml',
            'idp_certificate' => fake()->sha256().fake()->sha256(),
            'sp_entity_id' => 'urn:app:'.fake()->domainWord(),
            'attribute_mapping' => [
                'email' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress',
                'name' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name',
            ],
            'enforce_for_domain' => null,
            'is_active' => true,
        ];
    }

    public function withDomainEnforcement(string $domain = 'example.com'): static
    {
        return $this->state(['enforce_for_domain' => $domain]);
    }
}
