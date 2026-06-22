<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FormTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FormTemplateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', FormTemplate::class);

        $query = FormTemplate::with('fields');

        if ($request->filled('applies_to_entity_type')) {
            $query->where('applies_to_entity_type', $request->input('applies_to_entity_type'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        return response()->json([
            'data' => $query->latest()->paginate(20),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', FormTemplate::class);

        $validated = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description' => 'nullable|string',
            'applies_to_entity_type' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
            'fields' => 'required|array|min:1',
            'fields.*.key' => 'required|string|max:100',
            'fields.*.label_ar' => 'required|string|max:255',
            'fields.*.label_en' => 'required|string|max:255',
            'fields.*.field_type' => 'required|string|in:text,textarea,number,currency,date,datetime,boolean,select,multiselect,file',
            'fields.*.is_required' => 'nullable|boolean',
            'fields.*.default_value' => 'nullable',
            'fields.*.options' => 'nullable|array',
            'fields.*.validation_rules' => 'nullable|array',
            'fields.*.help_text_ar' => 'nullable|string',
            'fields.*.help_text_en' => 'nullable|string',
            'fields.*.sort_order' => 'nullable|integer',
            'fields.*.is_pii' => 'nullable|boolean',
        ]);

        $template = FormTemplate::create([
            'name_ar' => $validated['name_ar'],
            'name_en' => $validated['name_en'],
            'description' => $validated['description'] ?? null,
            'applies_to_entity_type' => $validated['applies_to_entity_type'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'created_by_user_id' => $request->user()->id,
            'updated_by_user_id' => $request->user()->id,
        ]);

        foreach ($validated['fields'] as $i => $fieldData) {
            $template->fields()->create(array_merge($fieldData, [
                'sort_order' => $fieldData['sort_order'] ?? $i,
            ]));
        }

        return response()->json([
            'data' => $template->load('fields'),
        ], 201);
    }

    public function show(FormTemplate $formTemplate): JsonResponse
    {
        $this->authorize('view', $formTemplate);

        return response()->json([
            'data' => $formTemplate->load('fields'),
        ]);
    }

    public function update(Request $request, FormTemplate $formTemplate): JsonResponse
    {
        $this->authorize('update', $formTemplate);

        $validated = $request->validate([
            'name_ar' => 'sometimes|string|max:255',
            'name_en' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'applies_to_entity_type' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
            'fields' => 'sometimes|array|min:1',
            'fields.*.key' => 'required_with:fields|string|max:100',
            'fields.*.label_ar' => 'required_with:fields|string|max:255',
            'fields.*.label_en' => 'required_with:fields|string|max:255',
            'fields.*.field_type' => 'required_with:fields|string|in:text,textarea,number,currency,date,datetime,boolean,select,multiselect,file',
            'fields.*.is_required' => 'nullable|boolean',
            'fields.*.default_value' => 'nullable',
            'fields.*.options' => 'nullable|array',
            'fields.*.validation_rules' => 'nullable|array',
            'fields.*.help_text_ar' => 'nullable|string',
            'fields.*.help_text_en' => 'nullable|string',
            'fields.*.sort_order' => 'nullable|integer',
            'fields.*.is_pii' => 'nullable|boolean',
        ]);

        // Increment version when fields change
        $fieldsChanged = isset($validated['fields']);

        $formTemplate->update(array_merge(
            collect($validated)->except('fields')->toArray(),
            [
                'updated_by_user_id' => $request->user()->id,
                'version' => $fieldsChanged ? $formTemplate->version + 1 : $formTemplate->version,
            ],
        ));

        if ($fieldsChanged) {
            $formTemplate->fields()->delete();
            foreach ($validated['fields'] as $i => $fieldData) {
                $formTemplate->fields()->create(array_merge($fieldData, [
                    'sort_order' => $fieldData['sort_order'] ?? $i,
                ]));
            }
        }

        return response()->json([
            'data' => $formTemplate->fresh()->load('fields'),
        ]);
    }

    public function destroy(FormTemplate $formTemplate): JsonResponse
    {
        $this->authorize('delete', $formTemplate);

        $formTemplate->delete();

        return response()->json(null, 204);
    }
}
