<?php

return [
    'document' => 'مستند',
    'documents' => 'المستندات',
    'title' => 'العنوان',
    'document_type' => 'نوع المستند',
    'language' => 'اللغة',
    'status' => 'الحالة',
    'version' => 'الإصدار',
    'versions' => 'الإصدارات',
    'current_version' => 'الإصدار الحالي',
    'change_summary' => 'ملخص التغييرات',
    'clause' => 'بند',
    'clauses' => 'البنود',
    'clause_type' => 'نوع البند',
    'clause_path' => 'مسار البند',
    'body' => 'المحتوى',

    // Document types [PROVISIONAL-FOUNDER-DECIDED]
    'type_contract' => 'عقد',
    'type_memo' => 'مذكرة',
    'type_letter' => 'خطاب',
    'type_amendment' => 'تعديل',
    'type_other' => 'أخرى',

    // Document statuses
    'status_draft' => 'مسودة',
    'status_under_review' => 'قيد المراجعة',
    'status_with_counterparty' => 'لدى الطرف المقابل',
    'status_signed' => 'موقّع',
    'status_archived' => 'مؤرشف',

    // Language options
    'language_ar' => 'عربي',
    'language_en' => 'إنجليزي',
    'language_bilingual' => 'ثنائي اللغة',
    'language_mixed' => 'مختلط',

    // Import
    'import_docx' => 'استيراد .docx',
    'import_file' => 'مستند Word',
    'import_file_required' => 'يرجى اختيار ملف .docx للاستيراد.',
    'import_file_mimes' => 'يُقبل فقط ملفات .docx.',
    'import_file_max' => 'يجب ألا يتجاوز حجم الملف 25 ميغابايت.',
    'import_title_placeholder' => 'اتركه فارغاً للكشف التلقائي من المستند',
    'import_success' => 'تم استيراد المستند بنجاح',
    'import_error' => 'فشل الاستيراد',
    'import_file_not_found' => 'لم يتم العثور على الملف المرفوع.',
    'imported_from_docx' => 'مستورد من .docx',
    'create_blank' => 'إنشاء مستند فارغ',
    'blank_document_created' => 'تم إنشاء مستند فارغ',

    // Empty state
    'empty_state_heading' => 'لا توجد مستندات بعد',
    'empty_state_description' => 'استورد مستند Word أو أنشئ مستنداً جديداً من الصفر.',

    // Success messages
    'created_success' => 'تم إنشاء المستند بنجاح',
    'updated_success' => 'تم تحديث المستند بنجاح',
    'deleted_success' => 'تم حذف المستند بنجاح',
    'version_created' => 'تم حفظ إصدار جديد',
    'version_skipped' => 'لم يتم اكتشاف تغييرات',
];
