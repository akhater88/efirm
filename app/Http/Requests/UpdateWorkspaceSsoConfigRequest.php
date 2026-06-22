<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkspaceSsoConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('ssoConfig'));
    }

    public function rules(): array
    {
        return [
            'provider_type' => 'sometimes|string|in:saml2,oidc',
            'provider_name' => 'sometimes|string|max:100',
            'idp_metadata_url' => 'nullable|url|max:500',
            'idp_metadata_xml' => 'nullable|string',
            'idp_entity_id' => 'sometimes|string|max:255',
            'idp_sso_url' => 'sometimes|url|max:500',
            'idp_certificate' => 'sometimes|string',
            'sp_entity_id' => 'sometimes|string|max:255',
            'attribute_mapping' => 'sometimes|array',
            'enforce_for_domain' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
