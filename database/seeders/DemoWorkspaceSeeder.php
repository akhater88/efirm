<?php

namespace Database\Seeders;

use App\Enums\CourtLevel;
use App\Enums\CourtType;
use App\Enums\DocumentLanguage;
use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Enums\HearingStatus;
use App\Enums\HearingType;
use App\Enums\LawyerProfileStatus;
use App\Enums\LitigationStatus;
use App\Enums\MatterStatus;
use App\Enums\MatterTypeEnum;
use App\Enums\ObligationStatus;
use App\Enums\ObligationType;
use App\Enums\PracticeArea;
use App\Enums\RepresentationRole;
use App\Enums\ResponsibleParty;
use App\Enums\Role;
use App\Models\Contact;
use App\Models\Court;
use App\Models\Document;
use App\Models\Hearing;
use App\Models\LawyerProfile;
use App\Models\Matter;
use App\Models\Obligation;
use App\Models\Task;
use App\Models\TaskWorkflow;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\DocumentService;
use App\Services\KycService;
use Illuminate\Database\Seeder;

class DemoWorkspaceSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Users ────────────────────────────────────────────────────────────

        $owner = User::create([
            'name' => 'عبدالله القاضي',
            'email' => 'abdullah@demo.test',
            'password' => bcrypt('password'),
            'preferred_locale' => 'ar',
            'email_verified_at' => now(),
        ]);

        $admin = User::create([
            'name' => 'لمى الخطيب',
            'email' => 'lama@demo.test',
            'password' => bcrypt('password'),
            'preferred_locale' => 'ar',
            'email_verified_at' => now(),
        ]);

        $member = User::create([
            'name' => 'Sara Mansour',
            'email' => 'sara@demo.test',
            'password' => bcrypt('password'),
            'preferred_locale' => 'en',
            'email_verified_at' => now(),
        ]);

        $member2 = User::create([
            'name' => 'فادي بدر',
            'email' => 'fadi@demo.test',
            'password' => bcrypt('password'),
            'preferred_locale' => 'ar',
            'email_verified_at' => now(),
        ]);

        // ─── Workspace ───────────────────────────────────────────────────────

        $workspace = Workspace::create([
            'name' => 'مكتب القاضي للمحاماة',
            'slug' => 'al-qadi-law',
            'default_locale' => 'ar',
            'created_by_user_id' => $owner->id,
        ]);

        WorkspaceMember::create([
            'workspace_id' => $workspace->id,
            'user_id' => $owner->id,
            'role' => Role::Owner,
            'joined_at' => now()->subMonths(6),
        ]);

        WorkspaceMember::create([
            'workspace_id' => $workspace->id,
            'user_id' => $admin->id,
            'role' => Role::Admin,
            'joined_at' => now()->subMonths(3),
        ]);

        WorkspaceMember::create([
            'workspace_id' => $workspace->id,
            'user_id' => $member->id,
            'role' => Role::Member,
            'joined_at' => now()->subMonths(2),
        ]);

        WorkspaceMember::create([
            'workspace_id' => $workspace->id,
            'user_id' => $member2->id,
            'role' => Role::Member,
            'joined_at' => now()->subMonth(),
        ]);

        // Set workspace context for BelongsToWorkspace trait
        session(['current_workspace_id' => $workspace->id]);
        auth()->login($owner);
        $owner->switchWorkspace($workspace);

        // ─── Contacts — Organizations ─────────────────────────────────────────

        $jordanSupplies = Contact::create([
            'workspace_id' => $workspace->id,
            'type' => 'organization',
            'organization_name' => 'شركة الأردن للتوريدات',
            'display_name' => 'شركة الأردن للتوريدات',
            'email' => 'info@jordan-supplies.jo',
            'phone' => '+962-6-5551234',
            'is_client' => true,
            'is_counterparty' => false,
            'nationality' => 'JO',
            'city' => 'عمّان',
            'country' => 'JO',
            'address_line_1' => 'شارع الملك حسين، عمارة 42',
            'tax_registration_number' => 'JO-TAX-2024-001',
            'notes' => 'عميل رئيسي منذ 2023. قطاع التوريدات الغذائية.',
            'created_by_user_id' => $owner->id,
            'updated_by_user_id' => $owner->id,
        ]);

        $acmeMena = Contact::create([
            'workspace_id' => $workspace->id,
            'type' => 'organization',
            'organization_name' => 'Acme MENA Holdings Ltd.',
            'display_name' => 'Acme MENA Holdings Ltd.',
            'email' => 'legal@acme-mena.com',
            'phone' => '+971-4-3889000',
            'is_client' => false,
            'is_counterparty' => true,
            'nationality' => 'AE',
            'city' => 'Dubai',
            'country' => 'AE',
            'address_line_1' => 'DIFC Gate Village, Building 5',
            'notes' => 'Counterparty in SPA and NDA transactions.',
            'created_by_user_id' => $owner->id,
            'updated_by_user_id' => $owner->id,
        ]);

        $alNoorTech = Contact::create([
            'workspace_id' => $workspace->id,
            'type' => 'organization',
            'organization_name' => 'شركة النور للتكنولوجيا',
            'display_name' => 'شركة النور للتكنولوجيا',
            'email' => 'info@alnoor-tech.jo',
            'phone' => '+962-6-5559876',
            'is_client' => true,
            'is_counterparty' => false,
            'nationality' => 'JO',
            'city' => 'عمّان',
            'country' => 'JO',
            'address_line_1' => 'مجمع الحسين للأعمال، الطابق 7',
            'created_by_user_id' => $owner->id,
            'updated_by_user_id' => $owner->id,
        ]);

        $beirutConsulting = Contact::create([
            'workspace_id' => $workspace->id,
            'type' => 'organization',
            'organization_name' => 'بيروت للاستشارات',
            'display_name' => 'بيروت للاستشارات',
            'email' => 'contact@beirut-consulting.lb',
            'phone' => '+961-1-234567',
            'is_client' => false,
            'is_counterparty' => true,
            'nationality' => 'LB',
            'city' => 'بيروت',
            'country' => 'LB',
            'address_line_1' => 'شارع الحمرا، بناية سمير',
            'created_by_user_id' => $admin->id,
            'updated_by_user_id' => $admin->id,
        ]);

        // ─── Contacts — Persons ───────────────────────────────────────────────

        $khaled = Contact::create([
            'workspace_id' => $workspace->id,
            'type' => 'person',
            'first_name' => 'خالد',
            'middle_name' => 'محمد',
            'last_name' => 'الحسن',
            'display_name' => 'خالد محمد الحسن',
            'email' => 'khaled@jordan-supplies.jo',
            'phone' => '+962-79-5551111',
            'is_client' => false,
            'is_counterparty' => false,
            'nationality' => 'JO',
            'parent_organization_id' => $jordanSupplies->id,
            'notes' => 'المدير التنفيذي لشركة الأردن للتوريدات',
            'created_by_user_id' => $owner->id,
            'updated_by_user_id' => $owner->id,
        ]);

        $johnSmith = Contact::create([
            'workspace_id' => $workspace->id,
            'type' => 'person',
            'first_name' => 'John',
            'last_name' => 'Smith',
            'display_name' => 'John Smith',
            'email' => 'john.smith@acme-mena.com',
            'phone' => '+971-50-1234567',
            'is_client' => false,
            'is_counterparty' => true,
            'nationality' => 'GB',
            'parent_organization_id' => $acmeMena->id,
            'notes' => 'General Counsel at Acme MENA',
            'created_by_user_id' => $admin->id,
            'updated_by_user_id' => $admin->id,
        ]);

        $rania = Contact::create([
            'workspace_id' => $workspace->id,
            'type' => 'person',
            'first_name' => 'رنيا',
            'last_name' => 'العمري',
            'display_name' => 'رنيا العمري',
            'email' => 'rania@alnoor-tech.jo',
            'phone' => '+962-79-8887777',
            'is_client' => true,
            'is_counterparty' => false,
            'nationality' => 'JO',
            'parent_organization_id' => $alNoorTech->id,
            'created_by_user_id' => $member->id,
            'updated_by_user_id' => $member->id,
        ]);

        // ─── Matters ──────────────────────────────────────────────────────────

        $spaMatter = Matter::create([
            'workspace_id' => $workspace->id,
            'title' => 'اتفاقية شراء أسهم — شركة الأردن للتوريدات',
            'client_id' => $jordanSupplies->id,
            'practice_area' => PracticeArea::MA,
            'status' => MatterStatus::Active,
            'stage' => 'تفاوض',
            'description' => 'صفقة استحواذ Acme MENA على حصة 60% من شركة الأردن للتوريدات. قيمة الصفقة المتوقعة USD 500,000.',
            'internal_reference' => 'MAT-2026-001',
            'lead_lawyer_id' => $admin->id,
            'opened_at' => now()->subWeeks(3),
            'tags' => ['استحواذ', 'أردن', 'عاجل'],
            'created_by_user_id' => $owner->id,
            'updated_by_user_id' => $admin->id,
        ]);

        $spaMatter->counterparties()->attach($acmeMena->id, ['representing' => 'they_represent']);
        $spaMatter->lawyers()->attach($admin->id, ['role' => 'lead']);
        $spaMatter->lawyers()->attach($member->id, ['role' => 'associate']);

        $ndaMatter = Matter::create([
            'workspace_id' => $workspace->id,
            'title' => 'اتفاقية سرية متبادلة — النور للتكنولوجيا',
            'client_id' => $alNoorTech->id,
            'practice_area' => PracticeArea::CommercialContracts,
            'status' => MatterStatus::Active,
            'stage' => 'مراجعة',
            'description' => 'اتفاقية سرية متبادلة بين النور للتكنولوجيا وبيروت للاستشارات قبل بدء مشروع مشترك.',
            'internal_reference' => 'MAT-2026-002',
            'lead_lawyer_id' => $member->id,
            'opened_at' => now()->subWeeks(2),
            'tags' => ['سرية', 'تكنولوجيا'],
            'created_by_user_id' => $admin->id,
            'updated_by_user_id' => $member->id,
        ]);

        $ndaMatter->counterparties()->attach($beirutConsulting->id, ['representing' => 'they_represent']);
        $ndaMatter->lawyers()->attach($member->id, ['role' => 'lead']);

        $supplyMatter = Matter::create([
            'workspace_id' => $workspace->id,
            'title' => 'Supply Agreement — Jordan Supplies & Acme MENA',
            'client_id' => $jordanSupplies->id,
            'practice_area' => PracticeArea::CommercialContracts,
            'status' => MatterStatus::Active,
            'stage' => 'Drafting',
            'description' => 'Annual supply agreement for industrial equipment. Contract value USD 250,000.',
            'internal_reference' => 'MAT-2026-003',
            'lead_lawyer_id' => $admin->id,
            'opened_at' => now()->subWeek(),
            'tags' => ['supply', 'annual'],
            'created_by_user_id' => $admin->id,
            'updated_by_user_id' => $admin->id,
        ]);

        $supplyMatter->counterparties()->attach($acmeMena->id, ['representing' => 'they_represent']);
        $supplyMatter->lawyers()->attach($admin->id, ['role' => 'lead']);
        $supplyMatter->lawyers()->attach($member2->id, ['role' => 'associate']);

        $closedMatter = Matter::create([
            'workspace_id' => $workspace->id,
            'title' => 'اتفاقية ترخيص برمجيات — النور للتكنولوجيا',
            'client_id' => $alNoorTech->id,
            'practice_area' => PracticeArea::CommercialContracts,
            'status' => MatterStatus::Closed,
            'stage' => 'موقّع',
            'description' => 'ترخيص برمجيات ERP لصالح النور للتكنولوجيا. تم التوقيع بنجاح.',
            'internal_reference' => 'MAT-2026-000',
            'lead_lawyer_id' => $owner->id,
            'opened_at' => now()->subMonths(2),
            'closed_at' => now()->subWeeks(4),
            'tags' => ['ترخيص', 'برمجيات', 'مغلق'],
            'created_by_user_id' => $owner->id,
            'updated_by_user_id' => $owner->id,
        ]);

        // ─── Documents ────────────────────────────────────────────────────────

        $documentService = app(DocumentService::class);

        // Document 1: SPA (Arabic, multiple versions)
        $spaBody = [
            'type' => 'doc',
            'content' => [
                ['type' => 'heading', 'attrs' => ['level' => 1], 'content' => [
                    ['type' => 'text', 'text' => 'اتفاقية شراء أسهم'],
                ]],
                ['type' => 'paragraph', 'content' => [
                    ['type' => 'text', 'text' => 'أبرمت هذه الاتفاقية بتاريخ 17 يونيو 2026 بين شركة الأردن للتوريدات (المشار إليها فيما يلي بـ "البائع") وشركة Acme MENA Holdings (المشار إليها فيما يلي بـ "المشتري").'],
                ]],
                ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [
                    ['type' => 'text', 'text' => '1. موضوع الاتفاقية'],
                ]],
                ['type' => 'paragraph', 'content' => [
                    ['type' => 'text', 'text' => 'يقوم البائع ببيع جميع الأسهم العادية للشركة محل الصفقة إلى المشتري وفقاً للشروط المنصوص عليها في هذه الاتفاقية.'],
                ]],
                ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [
                    ['type' => 'text', 'text' => '2. ثمن الشراء'],
                ]],
                ['type' => 'paragraph', 'content' => [
                    ['type' => 'text', 'text' => 'يبلغ ثمن الشراء الإجمالي مبلغ 500,000 دولار أمريكي (خمسمائة ألف دولار أمريكي)، يُدفع على النحو التالي:'],
                ]],
                ['type' => 'bulletList', 'content' => [
                    ['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => '50% عند التوقيع']]]]],
                    ['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => '25% خلال 30 يوماً من تاريخ الإغلاق']]]]],
                    ['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => '25% خلال 90 يوماً من تاريخ الإغلاق']]]]],
                ]],
                ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [
                    ['type' => 'text', 'text' => '3. الضمانات والإقرارات'],
                ]],
                ['type' => 'paragraph', 'content' => [
                    ['type' => 'text', 'text' => 'يقر البائع ويضمن أن الشركة '],
                    ['type' => 'text', 'marks' => [['type' => 'bold']], 'text' => 'ليس عليها أي التزامات مالية غير مفصح عنها'],
                    ['type' => 'text', 'text' => ' وأنها ملتزمة بجميع القوانين المعمول بها في المملكة الأردنية الهاشمية.'],
                ]],
                ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [
                    ['type' => 'text', 'text' => '4. القانون الواجب التطبيق'],
                ]],
                ['type' => 'paragraph', 'content' => [
                    ['type' => 'text', 'text' => 'تخضع هذه الاتفاقية للقانون الأردني، ويختص بنظر أي نزاع ينشأ عنها محكمة الاستئناف الأردنية في عمّان.'],
                ]],
            ],
        ];

        $spaDoc = $documentService->createDocument(
            $spaMatter,
            'اتفاقية شراء أسهم — شركة الأردن للتوريدات',
            $spaBody,
            $admin,
            [
                'document_type' => DocumentType::Contract,
                'language_primary' => DocumentLanguage::Arabic,
                'change_summary' => 'الإصدار الأول — مسودة أولية',
            ],
        );

        // Version 2: updated liability clause
        $spaBodyV2 = $spaBody;
        $spaBodyV2['content'][8]['content'] = [
            ['type' => 'text', 'text' => 'يقر البائع ويضمن أن الشركة '],
            ['type' => 'text', 'marks' => [['type' => 'bold']], 'text' => 'ليس عليها أي التزامات مالية غير مفصح عنها'],
            ['type' => 'text', 'text' => ' وأنها ملتزمة بجميع القوانين المعمول بها. تقتصر مسؤولية البائع الإجمالية على 25% من ثمن الشراء.'],
        ];
        $documentService->createVersion($spaDoc, $spaBodyV2, $admin, 'تحديث بند المسؤولية — رفع الحد إلى 25%');

        // Version 3: added governing law detail
        $spaBodyV3 = $spaBodyV2;
        $spaBodyV3['content'][10]['content'] = [
            ['type' => 'text', 'text' => 'تخضع هذه الاتفاقية للقانون الأردني. يختص بنظر أي نزاع ينشأ عنها محكمة الاستئناف في عمّان. يكون التحكيم بموجب قواعد غرفة تجارة عمّان بديلاً عن القضاء إذا اتفق الطرفان.'],
        ];
        $documentService->createVersion($spaDoc, $spaBodyV3, $member, 'إضافة خيار التحكيم في بند القانون الواجب التطبيق');

        $spaDoc->update(['status' => DocumentStatus::UnderReview]);

        // Document 2: Bilingual NDA (under the NDA matter)
        $ndaBody = [
            'type' => 'doc',
            'content' => [
                ['type' => 'heading', 'attrs' => ['level' => 1], 'content' => [
                    ['type' => 'text', 'text' => 'اتفاقية عدم إفصاح متبادلة'],
                ]],
                ['type' => 'heading', 'attrs' => ['level' => 1], 'content' => [
                    ['type' => 'text', 'text' => 'Mutual Non-Disclosure Agreement'],
                ]],
                ['type' => 'paragraph', 'content' => [
                    ['type' => 'text', 'text' => 'أبرمت هذه الاتفاقية بتاريخ 10 يونيو 2026 بين شركة النور للتكنولوجيا ("الطرف المفصح") وشركة بيروت للاستشارات ("الطرف المتلقي").'],
                ]],
                ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [
                    ['type' => 'text', 'text' => '1. تعريفات — Definitions'],
                ]],
                ['type' => 'paragraph', 'content' => [
                    ['type' => 'text', 'text' => '"المعلومات السرية" تعني أي معلومات تقنية أو تجارية أو مالية يفصح عنها أحد الطرفين للآخر.'],
                ]],
                ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [
                    ['type' => 'text', 'text' => '2. Obligations of the Receiving Party'],
                ]],
                ['type' => 'paragraph', 'content' => [
                    ['type' => 'text', 'text' => 'The Receiving Party shall hold all Confidential Information in strict confidence and shall not disclose such information to any third party without the prior written consent of the Disclosing Party.'],
                ]],
                ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [
                    ['type' => 'text', 'text' => '3. المدة والإنهاء — Term and Termination'],
                ]],
                ['type' => 'paragraph', 'content' => [
                    ['type' => 'text', 'text' => 'تسري هذه الاتفاقية لمدة سنتين (2) من تاريخ التوقيع. This Agreement shall remain in force for two (2) years from the date of execution.'],
                ]],
            ],
        ];

        $ndaDoc = $documentService->createDocument(
            $ndaMatter,
            'اتفاقية سرية متبادلة — النور وبيروت للاستشارات',
            $ndaBody,
            $member,
            [
                'document_type' => DocumentType::Contract,
                'language_primary' => DocumentLanguage::Bilingual,
                'change_summary' => 'مسودة أولى — ثنائية اللغة',
            ],
        );

        // Document 3: English Supply Agreement (draft)
        $supplyBody = [
            'type' => 'doc',
            'content' => [
                ['type' => 'heading', 'attrs' => ['level' => 1], 'content' => [
                    ['type' => 'text', 'text' => 'Supply Agreement'],
                ]],
                ['type' => 'paragraph', 'content' => [
                    ['type' => 'text', 'text' => 'This Supply Agreement ("Agreement") is entered into as of June 17, 2026, by and between Jordan Supplies Co. (the "Supplier") and Acme MENA Holdings Ltd. (the "Buyer").'],
                ]],
                ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [
                    ['type' => 'text', 'text' => '1. Scope of Supply'],
                ]],
                ['type' => 'paragraph', 'content' => [
                    ['type' => 'text', 'text' => 'The Supplier agrees to supply the goods described in Schedule A attached hereto, in accordance with the specifications, quantities, and delivery schedule set forth therein.'],
                ]],
                ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [
                    ['type' => 'text', 'text' => '2. Pricing and Payment'],
                ]],
                ['type' => 'paragraph', 'content' => [
                    ['type' => 'text', 'text' => 'The total contract value is USD 250,000.00 (Two Hundred Fifty Thousand United States Dollars).'],
                ]],
                ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [
                    ['type' => 'text', 'text' => '3. Warranties'],
                ]],
                ['type' => 'paragraph', 'content' => [
                    ['type' => 'text', 'text' => 'The Supplier warrants that all goods delivered shall be '],
                    ['type' => 'text', 'marks' => [['type' => 'italic']], 'text' => 'free from defects in material and workmanship'],
                    ['type' => 'text', 'text' => ' for a period of twelve (12) months from the date of delivery.'],
                ]],
                ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [
                    ['type' => 'text', 'text' => '4. Governing Law'],
                ]],
                ['type' => 'paragraph', 'content' => [
                    ['type' => 'text', 'text' => 'This Agreement shall be governed by the laws of the Hashemite Kingdom of Jordan.'],
                ]],
            ],
        ];

        $supplyDoc = $documentService->createDocument(
            $supplyMatter,
            'Supply Agreement — Jordan Supplies & Acme MENA',
            $supplyBody,
            $admin,
            [
                'document_type' => DocumentType::Contract,
                'language_primary' => DocumentLanguage::English,
                'change_summary' => 'Initial draft',
            ],
        );

        // Document 4: Signed license agreement (closed matter)
        $licenseBody = [
            'type' => 'doc',
            'content' => [
                ['type' => 'heading', 'attrs' => ['level' => 1], 'content' => [
                    ['type' => 'text', 'text' => 'اتفاقية ترخيص برمجيات'],
                ]],
                ['type' => 'paragraph', 'content' => [
                    ['type' => 'text', 'text' => 'أبرمت هذه الاتفاقية بين شركة النور للتكنولوجيا ("المرخص له") والشركة المطورة ("المرخص").'],
                ]],
                ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [
                    ['type' => 'text', 'text' => '1. نطاق الترخيص'],
                ]],
                ['type' => 'paragraph', 'content' => [
                    ['type' => 'text', 'text' => 'يمنح المرخص بموجب هذه الاتفاقية ترخيصاً غير حصري وغير قابل للتحويل لاستخدام نظام ERP في أعمال المرخص له.'],
                ]],
                ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [
                    ['type' => 'text', 'text' => '2. مدة الترخيص'],
                ]],
                ['type' => 'paragraph', 'content' => [
                    ['type' => 'text', 'text' => 'يسري هذا الترخيص لمدة ثلاث سنوات (3) اعتباراً من تاريخ التوقيع، ويتجدد تلقائياً لفترات سنوية ما لم يخطر أحد الطرفين الآخر قبل 60 يوماً.'],
                ]],
            ],
        ];

        $licenseDoc = $documentService->createDocument(
            $closedMatter,
            'اتفاقية ترخيص برمجيات ERP',
            $licenseBody,
            $owner,
            [
                'document_type' => DocumentType::Contract,
                'language_primary' => DocumentLanguage::Arabic,
                'change_summary' => 'النسخة النهائية الموقعة',
            ],
        );

        $licenseDoc->update(['status' => DocumentStatus::Signed]);

        // Document 5: Memo (under SPA matter)
        $memoBody = [
            'type' => 'doc',
            'content' => [
                ['type' => 'heading', 'attrs' => ['level' => 1], 'content' => [
                    ['type' => 'text', 'text' => 'مذكرة قانونية — مخاطر صفقة الاستحواذ'],
                ]],
                ['type' => 'paragraph', 'content' => [
                    ['type' => 'text', 'text' => 'تم إعداد هذه المذكرة بتاريخ 15 يونيو 2026 لتقييم المخاطر القانونية المحتملة في صفقة استحواذ Acme MENA على حصة من شركة الأردن للتوريدات.'],
                ]],
                ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [
                    ['type' => 'text', 'text' => '1. الخلاصة التنفيذية'],
                ]],
                ['type' => 'paragraph', 'content' => [
                    ['type' => 'text', 'text' => 'تتضمن الصفقة مخاطر متوسطة تتعلق بالامتثال الضريبي للشركة الهدف. ننصح بإجراء فحص نافية للجهالة شامل قبل الإغلاق.'],
                ]],
                ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [
                    ['type' => 'text', 'text' => '2. التوصيات'],
                ]],
                ['type' => 'paragraph', 'content' => [
                    ['type' => 'text', 'text' => 'نوصي بتضمين بند ضمانات ضريبية مفصل في اتفاقية الشراء، مع حد أقصى للمسؤولية لا يقل عن 25% من ثمن الشراء.'],
                ]],
            ],
        ];

        $memoDoc = $documentService->createDocument(
            $spaMatter,
            'مذكرة قانونية — مخاطر صفقة الاستحواذ',
            $memoBody,
            $member,
            [
                'document_type' => DocumentType::Memo,
                'language_primary' => DocumentLanguage::Arabic,
                'change_summary' => 'مسودة المذكرة',
            ],
        );

        // ─── Tasks with Workflow Assignments ──────────────────────────────────

        $genericWorkflow = TaskWorkflow::where('workspace_id', $workspace->id)
            ->where('is_default', true)
            ->first();

        if ($genericWorkflow) {
            $todoStage = $genericWorkflow->stages->firstWhere('key', 'todo');
            $inProgressStage = $genericWorkflow->stages->firstWhere('key', 'in_progress');
            $doneStage = $genericWorkflow->stages->firstWhere('key', 'done');

            $tasks = [
                // To Do tasks
                ['title' => 'مراجعة مسودة اتفاقية الشراء', 'matter' => $spaMatter, 'stage' => $todoStage, 'assignee' => $admin, 'priority' => 'high', 'due' => now()->addDays(3)],
                ['title' => 'إعداد قائمة الضمانات المطلوبة', 'matter' => $spaMatter, 'stage' => $todoStage, 'assignee' => $member, 'priority' => 'normal', 'due' => now()->addDays(5)],
                ['title' => 'Prepare NDA for counterparty review', 'matter' => $ndaMatter, 'stage' => $todoStage, 'assignee' => $member, 'priority' => 'urgent', 'due' => now()->addDay()],
                ['title' => 'تحديث بيانات العميل في النظام', 'matter' => $ndaMatter, 'stage' => $todoStage, 'assignee' => $member2, 'priority' => 'low', 'due' => now()->addWeek()],

                // In Progress tasks
                ['title' => 'التفاوض على بند المسؤولية', 'matter' => $spaMatter, 'stage' => $inProgressStage, 'assignee' => $admin, 'priority' => 'high', 'due' => now()->addDays(2)],
                ['title' => 'Draft supply agreement terms', 'matter' => $supplyMatter, 'stage' => $inProgressStage, 'assignee' => $admin, 'priority' => 'normal', 'due' => now()->addDays(4)],
                ['title' => 'مراجعة تقرير الفحص النافي للجهالة', 'matter' => $spaMatter, 'stage' => $inProgressStage, 'assignee' => $member, 'priority' => 'urgent', 'due' => now()->addDay()],

                // Done tasks
                ['title' => 'إرسال خطاب التكليف للعميل', 'matter' => $spaMatter, 'stage' => $doneStage, 'assignee' => $owner, 'priority' => 'normal', 'due' => now()->subDays(3)],
                ['title' => 'Collect counterparty contact details', 'matter' => $ndaMatter, 'stage' => $doneStage, 'assignee' => $member, 'priority' => 'normal', 'due' => now()->subDays(5)],
                ['title' => 'تسجيل بيانات الطرف المقابل', 'matter' => $supplyMatter, 'stage' => $doneStage, 'assignee' => $member2, 'priority' => 'low', 'due' => now()->subWeek()],
            ];

            foreach ($tasks as $taskData) {
                Task::create([
                    'workspace_id' => $workspace->id,
                    'title' => $taskData['title'],
                    'taskable_type' => 'matter',
                    'taskable_id' => $taskData['matter']->id,
                    'task_workflow_id' => $genericWorkflow->id,
                    'current_stage_id' => $taskData['stage']->id,
                    'assigned_to_user_id' => $taskData['assignee']->id,
                    'priority' => $taskData['priority'],
                    'due_date' => $taskData['due'],
                    'status' => $taskData['stage'] === $doneStage ? 'done' : 'todo',
                    'completed_at' => $taskData['stage'] === $doneStage ? now() : null,
                    'created_by_user_id' => $owner->id,
                    'updated_by_user_id' => $owner->id,
                ]);
            }
        }

        // ─── Lawyer Profiles ──────────────────────────────────────────────────

        LawyerProfile::create([
            'user_id' => $owner->id,
            'bar_admission_number' => 'JBA-2015-1234',
            'bar_admission_country' => 'JO',
            'bar_admission_date' => '2015-03-15',
            'jurisdictions' => [['country' => 'JO'], ['country' => 'AE']],
            'practice_areas' => ['commercial_contracts', 'mna'],
            'languages_spoken' => ['ar', 'en'],
            'default_hourly_rate' => '200.00',
            'default_currency' => 'USD',
            'position_title_ar' => 'محامي شريك',
            'position_title_en' => 'Managing Partner',
            'status' => LawyerProfileStatus::Active,
            'joined_firm_date' => '2015-03-01',
            'created_by_user_id' => $owner->id,
            'updated_by_user_id' => $owner->id,
        ]);

        LawyerProfile::create([
            'user_id' => $admin->id,
            'bar_admission_number' => 'JBA-2018-5678',
            'bar_admission_country' => 'JO',
            'bar_admission_date' => '2018-09-01',
            'jurisdictions' => [['country' => 'JO']],
            'practice_areas' => ['commercial_contracts', 'corporate_governance'],
            'languages_spoken' => ['ar', 'en'],
            'default_hourly_rate' => '150.00',
            'default_currency' => 'USD',
            'position_title_ar' => 'محامية أولى',
            'position_title_en' => 'Senior Associate',
            'status' => LawyerProfileStatus::Active,
            'joined_firm_date' => '2018-09-01',
            'created_by_user_id' => $owner->id,
            'updated_by_user_id' => $owner->id,
        ]);

        LawyerProfile::create([
            'user_id' => $member->id,
            'bar_admission_number' => 'JBA-2021-9012',
            'bar_admission_country' => 'JO',
            'bar_admission_date' => '2021-06-15',
            'jurisdictions' => [['country' => 'JO']],
            'practice_areas' => ['commercial_contracts'],
            'languages_spoken' => ['ar', 'en', 'fr'],
            'default_hourly_rate' => '100.00',
            'default_currency' => 'USD',
            'position_title_ar' => 'محامية',
            'position_title_en' => 'Associate',
            'status' => LawyerProfileStatus::Active,
            'joined_firm_date' => '2021-06-15',
            'created_by_user_id' => $owner->id,
            'updated_by_user_id' => $owner->id,
        ]);

        // ─── Litigation Matter with Court + Hearings ─────────────────────────

        $litigationMatter = Matter::create([
            'workspace_id' => $workspace->id,
            'title' => 'قضية تجارية — شركة الأردن ضد شركة النور',
            'client_id' => $jordanSupplies->id,
            'practice_area' => PracticeArea::CommercialContracts,
            'status' => MatterStatus::Active,
            'stage' => 'بينات',
            'description' => 'نزاع تجاري حول عقد توريد بين شركة الأردن للتوريدات وشركة النور للتكنولوجيا. قيمة المطالبة JOD 75,000.',
            'internal_reference' => 'MAT-2026-005',
            'lead_lawyer_id' => $admin->id,
            'opened_at' => now()->subMonths(2),
            'is_litigation' => true,
            'matter_type' => MatterTypeEnum::CommercialLitigation,
            'litigation_status' => LitigationStatus::InEvidence,
            'court_level' => CourtLevel::FirstInstance,
            'court_case_number' => '2026/أساس/1234',
            'filed_date' => now()->subMonths(2),
            'representation_role' => RepresentationRole::Plaintiff,
            'tags' => ['نزاع', 'توريد', 'أردن'],
            'created_by_user_id' => $owner->id,
            'updated_by_user_id' => $admin->id,
        ]);

        $litigationMatter->counterparties()->attach($alNoorTech->id, ['representing' => 'they_represent']);
        $litigationMatter->lawyers()->attach($admin->id, ['role' => 'lead']);
        $litigationMatter->lawyers()->attach($member->id, ['role' => 'associate']);

        $ammanCourt = Court::create([
            'workspace_id' => $workspace->id,
            'name_ar' => 'محكمة بداية عمّان',
            'name_en' => 'Amman First Instance Court',
            'court_type' => CourtType::FirstInstance,
            'jurisdiction_country' => 'JO',
            'city' => 'Amman',
            'created_by_user_id' => $owner->id,
            'updated_by_user_id' => $owner->id,
        ]);

        // Hearing 1: Held (past)
        $hearing1 = Hearing::create([
            'workspace_id' => $workspace->id,
            'matter_id' => $litigationMatter->id,
            'hearing_date' => now()->subWeeks(3),
            'court_id' => $ammanCourt->id,
            'hearing_type' => HearingType::FirstSession,
            'status' => HearingStatus::Held,
            'held_at' => now()->subWeeks(3),
            'outcome' => 'تم تحديد جلسة بينات المدعي',
            'assigned_lawyer_user_id' => $admin->id,
            'created_by_user_id' => $owner->id,
            'updated_by_user_id' => $owner->id,
        ]);

        // Hearing 2: Scheduled (future)
        $hearing2 = Hearing::create([
            'workspace_id' => $workspace->id,
            'matter_id' => $litigationMatter->id,
            'hearing_date' => now()->addWeeks(2),
            'court_id' => $ammanCourt->id,
            'hearing_type' => HearingType::PlaintiffEvidence,
            'status' => HearingStatus::Scheduled,
            'assigned_lawyer_user_id' => $admin->id,
            'created_by_user_id' => $owner->id,
            'updated_by_user_id' => $owner->id,
        ]);

        // Hearing 3: Postponed (linked to hearing 2)
        Hearing::create([
            'workspace_id' => $workspace->id,
            'matter_id' => $litigationMatter->id,
            'hearing_date' => now()->subWeek(),
            'court_id' => $ammanCourt->id,
            'hearing_type' => HearingType::NotificationSession,
            'status' => HearingStatus::Postponed,
            'postponed_to_hearing_id' => $hearing2->id,
            'postponement_reason_ar' => 'عدم حضور الطرف المدعى عليه رغم تبليغه حسب الأصول',
            'postponement_initiated_by' => 'court',
            'assigned_lawyer_user_id' => $admin->id,
            'created_by_user_id' => $owner->id,
            'updated_by_user_id' => $owner->id,
        ]);

        // ─── Obligations ─────────────────────────────────────────────────────

        Obligation::create([
            'workspace_id' => $workspace->id,
            'document_id' => $spaDoc->id,
            'title' => 'دفعة أولى — 50% من ثمن الشراء',
            'obligation_type' => ObligationType::Payment,
            'responsible_party' => ResponsibleParty::Counterparty,
            'due_date' => now()->addDays(15),
            'status' => ObligationStatus::Pending,
            'monetary_amount' => 250000,
            'monetary_currency' => 'USD',
            'created_by_user_id' => $owner->id,
            'updated_by_user_id' => $owner->id,
        ]);

        Obligation::create([
            'workspace_id' => $workspace->id,
            'document_id' => $spaDoc->id,
            'title' => 'تسليم مستندات الفحص النافي للجهالة',
            'obligation_type' => ObligationType::Delivery,
            'responsible_party' => ResponsibleParty::Us,
            'due_date' => now()->subDays(5),
            'status' => ObligationStatus::Overdue,
            'created_by_user_id' => $owner->id,
            'updated_by_user_id' => $owner->id,
        ]);

        Obligation::create([
            'workspace_id' => $workspace->id,
            'document_id' => $licenseDoc->id,
            'title' => 'دفع رسوم الترخيص السنوية',
            'obligation_type' => ObligationType::Payment,
            'responsible_party' => ResponsibleParty::Us,
            'due_date' => now()->subWeeks(2),
            'status' => ObligationStatus::Completed,
            'completed_at' => now()->subWeeks(2),
            'completed_by_id' => $owner->id,
            'monetary_amount' => 15000,
            'monetary_currency' => 'USD',
            'created_by_user_id' => $owner->id,
            'updated_by_user_id' => $owner->id,
        ]);

        Obligation::create([
            'workspace_id' => $workspace->id,
            'document_id' => $supplyDoc->id,
            'title' => 'إخطار المورد بجدول التسليم ربع السنوي',
            'obligation_type' => ObligationType::Notification,
            'responsible_party' => ResponsibleParty::Mutual,
            'due_date' => now()->addDays(30),
            'status' => ObligationStatus::Pending,
            'created_by_user_id' => $admin->id,
            'updated_by_user_id' => $admin->id,
        ]);

        // ─── Time Entries ────────────────────────────────────────────────────

        TimeEntry::create([
            'workspace_id' => $workspace->id,
            'user_id' => $admin->id,
            'matter_id' => $spaMatter->id,
            'description' => 'مراجعة مسودة اتفاقية شراء الأسهم',
            'duration_minutes' => 120,
            'started_at' => now()->subDays(2)->setHour(9)->setMinute(0),
            'ended_at' => now()->subDays(2)->setHour(11)->setMinute(0),
            'is_billable' => true,
            'billing_rate_per_hour' => '150.00',
            'currency' => 'USD',
            'created_by_user_id' => $admin->id,
            'updated_by_user_id' => $admin->id,
        ]);

        TimeEntry::create([
            'workspace_id' => $workspace->id,
            'user_id' => $member->id,
            'matter_id' => $ndaMatter->id,
            'description' => 'Drafting mutual NDA — first pass',
            'duration_minutes' => 90,
            'started_at' => now()->subDays(3)->setHour(14)->setMinute(0),
            'ended_at' => now()->subDays(3)->setHour(15)->setMinute(30),
            'is_billable' => true,
            'billing_rate_per_hour' => '100.00',
            'currency' => 'USD',
            'created_by_user_id' => $member->id,
            'updated_by_user_id' => $member->id,
        ]);

        TimeEntry::create([
            'workspace_id' => $workspace->id,
            'user_id' => $owner->id,
            'matter_id' => $spaMatter->id,
            'description' => 'اجتماع مع العميل لمناقشة شروط الصفقة',
            'duration_minutes' => 60,
            'started_at' => now()->subDays(1)->setHour(10)->setMinute(0),
            'ended_at' => now()->subDays(1)->setHour(11)->setMinute(0),
            'is_billable' => true,
            'billing_rate_per_hour' => '200.00',
            'currency' => 'USD',
            'created_by_user_id' => $owner->id,
            'updated_by_user_id' => $owner->id,
        ]);

        TimeEntry::create([
            'workspace_id' => $workspace->id,
            'user_id' => $admin->id,
            'matter_id' => $supplyMatter->id,
            'description' => 'Review supply agreement pricing schedule',
            'duration_minutes' => 45,
            'started_at' => now()->subDays(1)->setHour(14)->setMinute(0),
            'ended_at' => now()->subDays(1)->setHour(14)->setMinute(45),
            'is_billable' => true,
            'billing_rate_per_hour' => '150.00',
            'currency' => 'USD',
            'created_by_user_id' => $admin->id,
            'updated_by_user_id' => $admin->id,
        ]);

        TimeEntry::create([
            'workspace_id' => $workspace->id,
            'user_id' => $member2->id,
            'matter_id' => $supplyMatter->id,
            'description' => 'بحث قانوني حول شروط الضمانات في عقود التوريد',
            'duration_minutes' => 180,
            'started_at' => now()->subDays(4)->setHour(9)->setMinute(0),
            'ended_at' => now()->subDays(4)->setHour(12)->setMinute(0),
            'is_billable' => true,
            'billing_rate_per_hour' => '100.00',
            'currency' => 'USD',
            'created_by_user_id' => $member2->id,
            'updated_by_user_id' => $member2->id,
        ]);

        TimeEntry::create([
            'workspace_id' => $workspace->id,
            'user_id' => $admin->id,
            'matter_id' => $litigationMatter->id,
            'description' => 'إعداد لائحة الدعوى وتقديمها للمحكمة',
            'duration_minutes' => 240,
            'started_at' => now()->subWeeks(6)->setHour(8)->setMinute(0),
            'ended_at' => now()->subWeeks(6)->setHour(12)->setMinute(0),
            'is_billable' => true,
            'billing_rate_per_hour' => '150.00',
            'currency' => 'USD',
            'created_by_user_id' => $admin->id,
            'updated_by_user_id' => $admin->id,
        ]);

        // ─── KYC Checklist ───────────────────────────────────────────────────

        app(KycService::class)->start($jordanSupplies, $owner);

        // ─── Summary ──────────────────────────────────────────────────────────

        $this->command->info('');
        $this->command->info('Demo workspace seeded successfully!');
        $this->command->info('');
        $this->command->table(
            ['Entity', 'Count'],
            [
                ['Users', '4'],
                ['Workspace', '1 (مكتب القاضي للمحاماة)'],
                ['Contacts (Organizations)', '4'],
                ['Contacts (Persons)', '3'],
                ['Matters', '5 (3 active transactional, 1 active litigation, 1 closed)'],
                ['Documents', '5 (SPA, NDA, Supply, License, Memo)'],
                ['Document Versions', '7 (SPA has 3 versions)'],
                ['Tasks', '10 (4 To Do, 3 In Progress, 3 Done)'],
                ['Lawyer Profiles', '3 (Owner, Admin, Member)'],
                ['Courts', '1 (محكمة بداية عمّان)'],
                ['Hearings', '3 (1 held, 1 scheduled, 1 postponed)'],
                ['Obligations', '4 (1 pending, 1 overdue, 1 completed, 1 upcoming)'],
                ['Time Entries', '6 (across 4 matters)'],
                ['KYC Checklists', '1 (شركة الأردن للتوريدات)'],
            ],
        );
        $this->command->info('');
        $this->command->info('Login URLs (dev only):');
        $this->command->info("  Owner:  http://localhost:8000/dev/login/{$owner->id}");
        $this->command->info("  Admin:  http://localhost:8000/dev/login/{$admin->id}");
        $this->command->info("  Member: http://localhost:8000/dev/login/{$member->id}");
        $this->command->info('  Default: http://localhost:8000/dev/login');
        $this->command->info('');
    }
}
