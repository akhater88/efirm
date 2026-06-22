<?php

namespace App\Http\Requests;

use App\Models\WorkspaceSsoConfig;
use Illuminate\Foundation\Http\FormRequest;

class StoreWorkspaceSsoConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', WorkspaceSsoConfig::class);
    }

    public function rules(): array
    {
        return [
            'provider_type' => 'required|string|in:saml2,oidc',
            'provider_name' => 'required|string|max:100',
            'idp_metadata_url' => 'nullable|url|max:500',
            'idp_metadata_xml' => 'nullable|string',
            'idp_entity_id' => 'required|string|max:255',
            'idp_sso_url' => 'required|url|max:500',
            'idp_certificate' => 'required|string',
            'sp_entity_id' => 'required|string|max:255',
            'attribute_mapping' => 'required|array',
            'enforce_for_domain' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
