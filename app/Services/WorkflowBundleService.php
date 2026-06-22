<?php

namespace App\Services;

use App\Models\Automation;
use App\Models\DocumentTemplate;
use App\Models\FormTemplate;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\File;

class WorkflowBundleService
{
    private string $bundlePath;

    public function __construct()
    {
        $this->bundlePath = database_path('seeders/workflow_bundles');
    }

    /**
     * List all available workflow bundles.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listAvailable(): array
    {
        if (! File::isDirectory($this->bundlePath)) {
            return [];
        }

        $bundles = [];

        foreach (File::files($this->bundlePath) as $file) {
            if ($file->getExtension() !== 'json') {
                continue;
            }

            $content = File::get($file->getPathname());
            $data = json_decode($content, true);

            if (is_array($data)) {
                $bundles[] = [
                    'key' => $file->getFilenameWithoutExtension(),
                    'name_ar' => $data['name_ar'] ?? '',
                    'name_en' => $data['name_en'] ?? '',
                    'description' => $data['description'] ?? null,
                    'entities_count' => $this->countEntities($data),
                ];
            }
        }

        return $bundles;
    }

    /**
     * Activate a bundle in a workspace. Idempotent — skips entities that already exist by name.
     *
     * @return array<string, int> Counts of created entities by type.
     */
    public function activate(string $bundleKey, Workspace $workspace, ?User $user = null): array
    {
        $filePath = $this->bundlePath.'/'.$bundleKey.'.json';

        if (! File::exists($filePath)) {
            throw new \InvalidArgumentException("Workflow bundle not found: {$bundleKey}");
        }

        $content = File::get($filePath);
        $data = json_decode($content, true);

        if (! is_array($data)) {
            throw new \RuntimeException("Invalid bundle JSON: {$bundleKey}");
        }

        $counts = [
            'form_templates' => 0,
            'automations' => 0,
            'document_templates' => 0,
        ];

        // Create form templates
        foreach ($data['form_templates'] ?? [] as $templateData) {
            $existing = FormTemplate::withoutGlobalScope('workspace')
                ->where('workspace_id', $workspace->id)
                ->where('name_en', $templateData['name_en'])
                ->first();

            if ($existing) {
                continue;
            }

            $template = FormTemplate::withoutGlobalScope('workspace')->create([
                'workspace_id' => $workspace->id,
                'name_ar' => $templateData['name_ar'],
                'name_en' => $templateData['name_en'],
                'description' => $templateData['description'] ?? null,
                'applies_to_entity_type' => $templateData['applies_to_entity_type'] ?? null,
                'is_active' => true,
                'created_by_user_id' => $user?->id,
                'updated_by_user_id' => $user?->id,
            ]);

            foreach ($templateData['fields'] ?? [] as $i => $fieldData) {
                $template->fields()->create(array_merge($fieldData, [
                    'sort_order' => $fieldData['sort_order'] ?? $i,
                ]));
            }

            $counts['form_templates']++;
        }

        // Create automations
        foreach ($data['automations'] ?? [] as $automationData) {
            $existing = Automation::withoutGlobalScope('workspace')
                ->where('workspace_id', $workspace->id)
                ->where('name_en', $automationData['name_en'])
                ->first();

            if ($existing) {
                continue;
            }

            $automation = Automation::withoutGlobalScope('workspace')->create([
                'workspace_id' => $workspace->id,
                'name_ar' => $automationData['name_ar'],
                'name_en' => $automationData['name_en'],
                'description' => $automationData['description'] ?? null,
                'trigger_event' => $automationData['trigger_event'],
                'conditions' => $automationData['conditions'] ?? [],
                'is_active' => $automationData['is_active'] ?? true,
                'created_by_user_id' => $user?->id,
                'updated_by_user_id' => $user?->id,
            ]);

            foreach ($automationData['actions'] ?? [] as $i => $actionData) {
                $automation->actions()->create([
                    'sort_order' => $actionData['sort_order'] ?? $i,
                    'action_type' => $actionData['action_type'],
                    'action_payload' => $actionData['action_payload'] ?? [],
                    'stop_on_error' => $actionData['stop_on_error'] ?? true,
                ]);
            }

            $counts['automations']++;
        }

        // Create document templates
        foreach ($data['document_templates'] ?? [] as $docTemplateData) {
            $existing = DocumentTemplate::withoutGlobalScope('workspace')
                ->where('workspace_id', $workspace->id)
                ->where('name_en', $docTemplateData['name_en'])
                ->first();

            if ($existing) {
                continue;
            }

            DocumentTemplate::withoutGlobalScope('workspace')->create([
                'workspace_id' => $workspace->id,
                'name_ar' => $docTemplateData['name_ar'],
                'name_en' => $docTemplateData['name_en'],
                'description' => $docTemplateData['description'] ?? null,
                'document_type' => $docTemplateData['document_type'] ?? 'contract',
                'language' => $docTemplateData['language'] ?? 'bilingual',
                'body' => $docTemplateData['body'],
                'placeholder_schema' => $docTemplateData['placeholder_schema'] ?? null,
                'is_active' => true,
                'created_by_user_id' => $user?->id,
                'updated_by_user_id' => $user?->id,
            ]);

            $counts['document_templates']++;
        }

        return $counts;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function countEntities(array $data): int
    {
        return count($data['form_templates'] ?? [])
            + count($data['automations'] ?? [])
            + count($data['document_templates'] ?? []);
    }
}
