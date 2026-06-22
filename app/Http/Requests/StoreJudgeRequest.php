<?php

namespace App\Http\Requests;

use App\Models\Judge;
use Illuminate\Foundation\Http\FormRequest;

class StoreJudgeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Judge::class);
    }

    public function rules(): array
    {
        return [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'court_id' => 'nullable|string|exists:courts,id',
            'title' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ];
    }
}
