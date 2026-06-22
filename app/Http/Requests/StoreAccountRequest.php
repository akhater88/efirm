<?php

namespace App\Http\Requests;

use App\Models\Account;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Account::class);
    }

    public function rules(): array
    {
        return [
            'parent_id' => 'nullable|exists:accounts,id',
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('accounts')->where('workspace_id', $this->user()->currentWorkspace()?->id),
            ],
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'account_type' => 'required|string|in:asset,liability,equity,revenue,expense',
            'is_system' => 'boolean',
            'description' => 'nullable|string',
        ];
    }
}
