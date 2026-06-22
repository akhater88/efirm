<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FormSubmission;
use App\Models\FormTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FormSubmissionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', FormSubmission::class);

        $query = FormSubmission::with('template');

        if ($request->filled('form_template_id')) {
            $query->where('form_template_id', $request->input('form_template_id'));
        }

        if ($request->filled('submittable_type') && $request->filled('submittable_id')) {
            $query->where('submittable_type', $request->input('submittable_type'))
                ->where('submittable_id', $request->input('submittable_id'));
        }

        return response()->json([
            'data' => $query->latest()->paginate(20),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', FormSubmission::class);

        $validated = $request->validate([
            'form_template_id' => 'required|string|size:26',
            'submittable_type' => 'required|string|max:100',
            'submittable_id' => 'required|string|size:26',
            'values' => 'required|array',
        ]);

        $template = FormTemplate::with('fields')->findOrFail($validated['form_template_id']);

        // Validate required fields
        $errors = [];
        foreach ($template->fields as $field) {
            if ($field->is_required && empty($validated['values'][$field->key])) {
                $errors['values.'.$field->key] = [__('forms.field_required', ['field' => $field->label_en])];
            }
        }

        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        $submission = FormSubmission::create([
            'form_template_id' => $template->id,
            'template_version_at_submission' => $template->version,
            'submittable_type' => $validated['submittable_type'],
            'submittable_id' => $validated['submittable_id'],
            'submitted_by_user_id' => $request->user()->id,
            'submitted_at' => now(),
            'values' => $validated['values'],
            'created_by_user_id' => $request->user()->id,
            'updated_by_user_id' => $request->user()->id,
        ]);

        return response()->json([
            'data' => $submission->load('template'),
        ], 201);
    }

    public function show(FormSubmission $formSubmission): JsonResponse
    {
        $this->authorize('view', $formSubmission);

        return response()->json([
            'data' => $formSubmission->load('template'),
        ]);
    }

    public function destroy(FormSubmission $formSubmission): JsonResponse
    {
        $this->authorize('delete', $formSubmission);

        $formSubmission->delete();

        return response()->json(null, 204);
    }
}
