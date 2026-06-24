<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'slug' => 'starter',
                'name' => 'Starter',
                'name_ar' => 'الأساسية',
                'description' => 'For solo practitioners and small firms getting started.',
                'description_ar' => 'للممارسين المنفردين والمكاتب الصغيرة في البداية.',
                'price_per_seat_usd' => 20.00,
                'max_seats' => 3,
                'max_matters' => 50,
                'max_contacts' => 100,
                'max_storage_mb' => 1024,
                'features' => [
                    'document_editor' => true,
                    'clause_library' => false,
                    'ai_operations' => false,
                    'obligations_tracking' => true,
                    'time_entries' => true,
                    'api_access' => false,
                ],
                'sort_order' => 1,
            ],
            [
                'slug' => 'pro',
                'name' => 'Pro',
                'name_ar' => 'المتقدمة',
                'description' => 'For growing firms that need AI and clause management.',
                'description_ar' => 'للمكاتب النامية التي تحتاج الذكاء الاصطناعي وإدارة البنود.',
                'price_per_seat_usd' => 25.00,
                'max_seats' => 10,
                'max_matters' => 200,
                'max_contacts' => 500,
                'max_storage_mb' => 5120,
                'features' => [
                    'document_editor' => true,
                    'clause_library' => true,
                    'ai_operations' => true,
                    'obligations_tracking' => true,
                    'time_entries' => true,
                    'api_access' => true,
                ],
                'sort_order' => 2,
            ],
            [
                'slug' => 'enterprise',
                'name' => 'Enterprise',
                'name_ar' => 'المؤسسية',
                'description' => 'For established firms with unlimited needs and priority support.',
                'description_ar' => 'للمكاتب الراسخة ذات الاحتياجات غير المحدودة والدعم ذي الأولوية.',
                'price_per_seat_usd' => 30.00,
                'max_seats' => null,
                'max_matters' => null,
                'max_contacts' => null,
                'max_storage_mb' => null,
                'features' => [
                    'document_editor' => true,
                    'clause_library' => true,
                    'ai_operations' => true,
                    'obligations_tracking' => true,
                    'time_entries' => true,
                    'api_access' => true,
                    'priority_support' => true,
                    'custom_branding' => true,
                ],
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $planData) {
            Plan::updateOrCreate(
                ['slug' => $planData['slug']],
                $planData
            );
        }
    }
}
