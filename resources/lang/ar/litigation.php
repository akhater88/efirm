<?php

// [HARD-STOP-LAWYER-REQUIRED] All litigation strings require legal review before production.

return [
    // --- General ---
    'litigation' => 'التقاضي',
    'litigation_matters' => 'ملفات التقاضي',
    'commercial_matters' => 'الملفات التجارية',
    'is_litigation' => 'ملف تقاضي',

    // --- Matter litigation fields ---
    'court' => 'المحكمة',
    'judge' => 'القاضي',
    'court_case_number' => 'رقم الدعوى',
    'case_number_internal' => 'الرقم الداخلي للقضية',
    'litigation_status' => 'حالة التقاضي',
    'filed_date' => 'تاريخ رفع الدعوى',
    'next_hearing_date' => 'تاريخ الجلسة القادمة',
    'representation_role' => 'صفة التمثيل',

    // --- Litigation statuses [PROVISIONAL-FOUNDER-DECIDED] ---
    // Per advisor input: docs/02_advisor_meeting_log.md Conversation 1, Decision #1
    'status_pre_filing' => 'قبل رفع الدعوى',
    'status_fee_payment_and_registration' => 'قيد الدعوى ودفع الرسوم',
    'status_filed' => 'مرفوعة',
    'status_notification_pending' => 'بانتظار التبليغ',
    'status_in_evidence' => 'في مرحلة الإثبات',
    'status_referred_to_expert' => 'الإحالة للخبرة',
    'status_in_judgment' => 'في مرحلة الحكم',
    'status_appealed' => 'مستأنفة',
    'status_closed_won' => 'مغلقة (ربح)',
    'status_closed_lost' => 'مغلقة (خسارة)',
    'status_settled' => 'تسوية',
    'status_withdrawn' => 'منسحبة',

    // --- Representation roles [PROVISIONAL-FOUNDER-DECIDED] ---
    'role_plaintiff' => 'مدّعي',
    'role_defendant' => 'مدّعى عليه',
    'role_intervenor' => 'متدخل',
    'role_third_party' => 'طرف ثالث',

    // --- Courts ---
    'courts' => 'المحاكم',
    'court_name_ar' => 'اسم المحكمة (عربي)',
    'court_name_en' => 'اسم المحكمة (إنجليزي)',
    'court_type' => 'نوع المحكمة',
    'jurisdiction_country' => 'بلد الاختصاص',
    'jurisdiction_governorate' => 'المحافظة',
    'city' => 'المدينة',
    'address' => 'العنوان',
    'phone' => 'الهاتف',
    'notes' => 'ملاحظات',

    // --- Court types [PROVISIONAL-FOUNDER-DECIDED] ---
    'court_type_magistrate' => 'محكمة الصلح',
    'court_type_first_instance' => 'محكمة البداية',
    'court_type_appeal' => 'محكمة الاستئناف',
    'court_type_cassation' => 'محكمة التمييز',
    'court_type_specialized_commercial' => 'المحكمة التجارية المتخصصة',
    'court_type_specialized_labor' => 'محكمة العمل المتخصصة',
    'court_type_specialized_family' => 'محكمة الأسرة المتخصصة',
    'court_type_administrative' => 'المحكمة الإدارية',
    'court_type_sharia' => 'المحكمة الشرعية',
    'court_type_arbitration' => 'التحكيم',

    // --- Judges ---
    'judges' => 'القضاة',
    'judge_name_ar' => 'اسم القاضي (عربي)',
    'judge_name_en' => 'اسم القاضي (إنجليزي)',
    'judge_title' => 'اللقب',

    // --- Hearings ---
    'hearing' => 'جلسة',
    'hearings' => 'الجلسات',
    'hearing_date' => 'تاريخ الجلسة',
    'hearing_type' => 'نوع الجلسة',
    'hearing_status' => 'حالة الجلسة',
    'held_at' => 'عُقدت في',
    'outcome' => 'النتيجة',
    'next_action_required' => 'الإجراء التالي المطلوب',
    'postponed_to' => 'مؤجلة إلى',
    'our_attendee' => 'الحاضر عنّا',

    // --- Hearing types [PROVISIONAL-FOUNDER-DECIDED] ---
    // Per advisor input: docs/02_advisor_meeting_log.md Conversation 1, Decision #2
    'hearing_type_first_session' => 'الجلسة الأولى',
    'hearing_type_evidence' => 'إثبات', // [DEPRECATED] — تم تقسيمه إلى بينات المدعي/المدعى عليه
    'hearing_type_plaintiff_evidence' => 'بينات المدعي',
    'hearing_type_defendant_evidence' => 'بينات المدعى عليه',
    'hearing_type_notification_session' => 'جلسة تبليغ',
    'hearing_type_expert_witness' => 'شاهد خبير',
    'hearing_type_witness_testimony' => 'شهادة شهود',
    'hearing_type_final_arguments' => 'المرافعات الختامية',
    'hearing_type_judgment' => 'حكم',
    'hearing_type_enforcement' => 'تنفيذ',
    'hearing_type_other' => 'أخرى',

    // --- Hearing statuses ---
    'hearing_status_scheduled' => 'مجدولة',
    'hearing_status_held' => 'انعقدت',
    'hearing_status_postponed' => 'مؤجلة',
    'hearing_status_cancelled' => 'ملغاة',

    // --- Court Reviews ---
    'court_review' => 'مراجعة قضائية',
    'court_reviews' => 'المراجعات القضائية',
    'decision_date' => 'تاريخ القرار',
    'decision_type' => 'نوع القرار',
    'decision_outcome' => 'النتيجة',
    'summary_ar' => 'الملخص (عربي)',
    'summary_en' => 'الملخص (إنجليزي)',
    'decision_document' => 'مستند القرار',
    'appealable' => 'قابل للاستئناف',
    'appeal_deadline_date' => 'موعد الاستئناف الأخير',
    'appeal_filed' => 'تم تقديم استئناف',
    'next_steps' => 'الخطوات التالية',

    // --- Decision types [PROVISIONAL-FOUNDER-DECIDED] ---
    'decision_type_interim_order' => 'أمر مؤقت',
    'decision_type_procedural_ruling' => 'قرار إجرائي',
    'decision_type_expert_appointment' => 'تعيين خبير',
    'decision_type_evidence_ruling' => 'قرار إثبات',
    'decision_type_partial_judgment' => 'حكم جزئي',
    'decision_type_final_judgment' => 'حكم نهائي',
    'decision_type_appeal_decision' => 'قرار استئنافي',
    'decision_type_enforcement_order' => 'أمر تنفيذ',
    'decision_type_other' => 'أخرى',

    // --- Decision outcomes [PROVISIONAL-FOUNDER-DECIDED] ---
    'outcome_favourable' => 'لصالحنا',
    'outcome_adverse' => 'ضدنا',
    'outcome_mixed' => 'مختلط',
    'outcome_procedural_only' => 'إجرائي فقط',

    // --- Service Log ---
    'service_log' => 'سجل التبليغات',
    'service_log_entries' => 'إدخالات سجل التبليغات',
    'served_party' => 'الطرف المُبلَّغ',
    'service_method' => 'طريقة التبليغ',
    'service_date' => 'تاريخ التبليغ',
    'service_address' => 'عنوان التبليغ',
    'served_by_name' => 'المُبلِّغ',
    'served_to_recipient_name' => 'المُبلَّغ إليه',
    'proof_document' => 'مستند الإثبات',
    'service_status' => 'حالة التبليغ',

    // --- Service methods [PROVISIONAL-FOUNDER-DECIDED] ---
    'service_method_personal_service' => 'تبليغ شخصي',
    'service_method_registered_mail' => 'بريد مسجل',
    'service_method_court_bailiff' => 'محضر المحكمة',
    'service_method_substituted_service' => 'تبليغ بالبدل',
    'service_method_publication' => 'نشر',
    'service_method_electronic' => 'إلكتروني',
    'service_method_foreign_service' => 'تبليغ خارجي',

    // --- Service statuses ---
    'service_status_successful' => 'ناجح',
    'service_status_failed_no_response' => 'فشل - لا استجابة',
    'service_status_failed_refused' => 'فشل - رفض',
    'service_status_failed_invalid_address' => 'فشل - عنوان غير صحيح',
    'service_status_pending_proof' => 'بانتظار الإثبات',

    // --- Opposing Counsel ---
    'opposing_counsel' => 'محامي الخصم',
    'is_opposing_counsel' => 'محامي خصم',

    // --- Court levels (per matter) [PROVISIONAL-FOUNDER-DECIDED] ---
    'court_level_magistrate' => 'محكمة الصلح',
    'court_level_first_instance' => 'محكمة البداية',
    'court_level_appeal' => 'محكمة الاستئناف',
    'court_level_cassation' => 'محكمة التمييز',
    'court_level_specialized_commercial' => 'المحكمة التجارية المتخصصة',
    'court_level_specialized_labor' => 'محكمة العمل المتخصصة',
    'court_level_administrative' => 'المحكمة الإدارية',
    'court_level_sharia' => 'المحكمة الشرعية',
    'court_level_arbitration' => 'التحكيم',

    // --- Judgment presence types [PROVISIONAL-FOUNDER-DECIDED] ---
    'judgment_wijahi' => 'وجاهي',
    'judgment_mithla_wijahi' => 'بمثابة الوجاهي',
    'judgment_ghyabi' => 'غيابي',

    // --- Appeal deadline ---
    'appeal_deadline' => 'موعد الاستئناف',
    'requires_input' => 'يتطلب إدخال يدوي',

    // --- Hearing Session Content (F-FIX-02.1, Decision #28) ---
    'judge_statement' => 'قرار القاضي',
    'judge_statement_ar' => 'قرار القاضي (عربي)',
    'judge_statement_en' => 'قرار القاضي (إنجليزي)',
    'outcome_summary' => 'ملخص النتيجة',
    'outcome_summary_ar' => 'ملخص النتيجة (عربي)',
    'outcome_summary_en' => 'ملخص النتيجة (إنجليزي)',
    'our_submissions_made' => 'المذكرات المقدمة منّا',
    'opposing_submissions_made' => 'المذكرات المقدمة من الخصم',
    'next_session_required_actions' => 'الإجراءات المطلوبة للجلسة القادمة',
    'next_session_required_actions_ar' => 'الإجراءات المطلوبة للجلسة القادمة (عربي)',
    'next_session_required_actions_en' => 'الإجراءات المطلوبة للجلسة القادمة (إنجليزي)',
    'session_attended_by' => 'حاضرو الجلسة',
    'session_content_requires_held_status' => 'لا يمكن تسجيل محتوى الجلسة إلا للجلسات بحالة "انعقدت".',
    'action_items' => 'بنود الإجراءات',
    'action_item' => 'بند إجراء',
    'action_item_description' => 'الوصف',
    'action_item_due_date' => 'تاريخ الاستحقاق',
    'action_item_status' => 'الحالة',
    'action_item_status_pending' => 'معلّق',
    'action_item_status_completed' => 'مكتمل',
    'action_item_status_waived' => 'ملغى',
    'sessions_timeline' => 'الجدول الزمني للجلسات',

    // --- Court Review Dispatch (F-FIX-02.2, Decision #29) ---
    'dispatched_to' => 'مُرسَل إلى',
    'dispatched_at' => 'تاريخ الإرسال',
    'completed_by' => 'أُنجز بواسطة',
    'location_in_courthouse' => 'الموقع في المحكمة',
    'location_in_courthouse_ar' => 'الموقع في المحكمة (عربي)',
    'location_in_courthouse_en' => 'الموقع في المحكمة (إنجليزي)',
    'expected_outcome' => 'النتيجة المتوقعة',
    'expected_outcome_ar' => 'النتيجة المتوقعة (عربي)',
    'expected_outcome_en' => 'النتيجة المتوقعة (إنجليزي)',
    'completion_notes' => 'ملاحظات الإنجاز',
    'evidence_document' => 'مستند الإثبات',
    'dispatched_to_me' => 'المُرسَلة إليّ',
    'dispatch' => 'إرسال',
    'dispatch_complete' => 'إتمام الإرسال',

    // --- Hearing Postponement Chain (F-FIX-02.5, Decision #30) ---
    'postponement_reason' => 'سبب التأجيل',
    'postponement_reason_ar' => 'سبب التأجيل (عربي)',
    'postponement_reason_en' => 'سبب التأجيل (إنجليزي)',
    'postponement_initiated_by' => 'التأجيل بمبادرة من',
    'postponement_initiated_our_side' => 'جانبنا',
    'postponement_initiated_opposing_side' => 'الطرف المقابل',
    'postponement_initiated_court' => 'المحكمة',
    'postponement_initiated_unknown' => 'غير معروف',
    'postponement_chain' => 'سلسلة التأجيلات',
    'circular_postponement_reference' => 'لا يمكن تعيين هدف التأجيل: تم اكتشاف مرجع دائري.',

    // --- Quick Timer (F-FIX-02.4, Decision #31) ---
    'timer_already_active' => 'لديك مؤقت نشط بالفعل. أوقفه قبل بدء مؤقت جديد.',

    // --- Success messages ---
    'court_created_success' => 'تم إنشاء المحكمة بنجاح',
    'court_updated_success' => 'تم تحديث المحكمة بنجاح',
    'court_deleted_success' => 'تم حذف المحكمة بنجاح',
    'judge_created_success' => 'تم إنشاء القاضي بنجاح',
    'judge_updated_success' => 'تم تحديث القاضي بنجاح',
    'judge_deleted_success' => 'تم حذف القاضي بنجاح',
    'hearing_created_success' => 'تم إنشاء الجلسة بنجاح',
    'hearing_updated_success' => 'تم تحديث الجلسة بنجاح',
    'hearing_deleted_success' => 'تم حذف الجلسة بنجاح',
    'court_review_created_success' => 'تم إنشاء المراجعة القضائية بنجاح',
    'court_review_updated_success' => 'تم تحديث المراجعة القضائية بنجاح',
    'court_review_deleted_success' => 'تم حذف المراجعة القضائية بنجاح',
    'service_log_created_success' => 'تم إنشاء إدخال سجل التبليغات بنجاح',
    'service_log_updated_success' => 'تم تحديث إدخال سجل التبليغات بنجاح',
    'service_log_deleted_success' => 'تم حذف إدخال سجل التبليغات بنجاح',
];
