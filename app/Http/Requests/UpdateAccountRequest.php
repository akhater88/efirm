<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('account'));
    }

    public function rules(): array
    {
        return [
            'parent_id' => 'nullable|exists:accounts,id',
            'code' => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('accounts')
                    ->where('workspace_id', $this->user()->currentWorkspace()?->id)
                    ->ignore($this->route('account')),
            ],
            'name' => 'sometimes|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'account_type' => 'sometimes|string|in:asset,liability,equity,revenue,expense',
            'description' => 'nullable|string',
        ];
    }
}
