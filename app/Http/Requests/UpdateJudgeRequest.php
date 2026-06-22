<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJudgeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('judge'));
    }

    public function rules(): array
    {
        return [
            'name_ar' => 'sometimes|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'court_id' => 'nullable|string|exists:courts,id',
            'title' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ];
    }
}
