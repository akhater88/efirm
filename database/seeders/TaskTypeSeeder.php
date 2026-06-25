<?php

namespace Database\Seeders;

use App\Models\TaskType;
use App\Models\Workspace;
use Illuminate\Database\Seeder;

class TaskTypeSeeder extends Seeder
{
    public function run(): void
    {
        $workspaces = Workspace::all();

        foreach ($workspaces as $workspace) {
            $this->seedForWorkspace($workspace);
        }
    }

    private function seedForWorkspace(Workspace $workspace): void
    {
        $types = [
            [
                'name_en' => 'General Task',
                'name_ar' => "\u0645\u0647\u0645\u0629 \u0639\u0627\u0645\u0629",
                'slug' => 'general',
                'icon' => 'clipboard',
                'color' => '#78716C',
                'custom_fields' => null,
                'sort_order' => 0,
            ],
            [
                'name_en' => 'Contract Review',
                'name_ar' => "\u0645\u0631\u0627\u062c\u0639\u0629 \u0639\u0642\u062f",
                'slug' => 'contract-review',
                'icon' => 'file-text',
                'color' => '#0D5C2E',
                'custom_fields' => [
                    [
                        'key' => 'contract_value',
                        'label_en' => 'Contract Value',
                        'label_ar' => "\u0642\u064a\u0645\u0629 \u0627\u0644\u0639\u0642\u062f",
                        'type' => 'number',
                        'required' => false,
                        'options' => null,
                    ],
                    [
                        'key' => 'jurisdiction',
                        'label_en' => 'Jurisdiction',
                        'label_ar' => "\u0627\u0644\u0627\u062e\u062a\u0635\u0627\u0635 \u0627\u0644\u0642\u0636\u0627\u0626\u064a",
                        'type' => 'select',
                        'required' => true,
                        'options' => ['Jordan', 'Lebanon', 'Palestine', 'Iraq'],
                    ],
                    [
                        'key' => 'review_deadline',
                        'label_en' => 'Review Deadline',
                        'label_ar' => "\u0645\u0648\u0639\u062f \u0627\u0644\u0645\u0631\u0627\u062c\u0639\u0629",
                        'type' => 'date',
                        'required' => false,
                        'options' => null,
                    ],
                ],
                'sort_order' => 1,
            ],
            [
                'name_en' => 'Court Filing',
                'name_ar' => "\u062a\u0642\u062f\u064a\u0645 \u0645\u062d\u0643\u0645\u0629",
                'slug' => 'court-filing',
                'icon' => 'briefcase',
                'color' => '#2563EB',
                'custom_fields' => [
                    [
                        'key' => 'court_name',
                        'label_en' => 'Court Name',
                        'label_ar' => "\u0627\u0633\u0645 \u0627\u0644\u0645\u062d\u0643\u0645\u0629",
                        'type' => 'text',
                        'required' => false,
                        'options' => null,
                    ],
                    [
                        'key' => 'case_number',
                        'label_en' => 'Case Number',
                        'label_ar' => "\u0631\u0642\u0645 \u0627\u0644\u0642\u0636\u064a\u0629",
                        'type' => 'text',
                        'required' => false,
                        'options' => null,
                    ],
                    [
                        'key' => 'filing_type',
                        'label_en' => 'Filing Type',
                        'label_ar' => "\u0646\u0648\u0639 \u0627\u0644\u062a\u0642\u062f\u064a\u0645",
                        'type' => 'select',
                        'required' => true,
                        'options' => ['Motion', 'Brief', 'Petition', 'Response'],
                    ],
                ],
                'sort_order' => 2,
            ],
        ];

        foreach ($types as $typeData) {
            TaskType::withoutGlobalScopes()->updateOrCreate(
                [
                    'workspace_id' => $workspace->id,
                    'slug' => $typeData['slug'],
                ],
                array_merge($typeData, ['workspace_id' => $workspace->id])
            );
        }
    }
}
