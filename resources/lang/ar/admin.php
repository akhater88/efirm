<?php

return [

    // Navigation
    'nav' => [
        'admin_users' => 'مستخدمو الإدارة',
        'activity_log' => 'سجل النشاط',
        'plans' => 'الخطط',
        'group_billing' => 'الفوترة',
        'workspaces' => 'مساحات العمل',
        'group_platform' => 'المنصة',
    ],

    // Panel
    'panel' => [
        'brand' => 'لوحة إدارة المنصة',
    ],

    // Auth
    'auth' => [
        'email' => 'البريد الإلكتروني',
        'password' => 'كلمة المرور',
        'login' => 'تسجيل الدخول',
        'logout' => 'تسجيل الخروج',
        'account_disabled' => 'تم تعطيل هذا الحساب. تواصل مع المسؤول الأعلى.',
        'session_expired' => 'انتهت جلستك. يرجى تسجيل الدخول مرة أخرى.',
        'session_idle' => 'تم إغلاق جلستك بسبب عدم النشاط.',
    ],

    // Roles
    'roles' => [
        'super_admin' => 'مسؤول أعلى',
        'support' => 'دعم',
        'finance' => 'مالية',
        'read_only' => 'قراءة فقط',
    ],

    // Locales
    'locales' => [
        'ar' => 'العربية',
        'en' => 'الإنجليزية',
    ],

    // Admin Users CRUD
    'users' => [
        'singular' => 'مستخدم إدارة',
        'plural' => 'مستخدمو الإدارة',
        'name' => 'الاسم',
        'email' => 'البريد الإلكتروني',
        'role' => 'الدور',
        'locale' => 'اللغة',
        'password' => 'كلمة المرور',
        'password_confirmation' => 'تأكيد كلمة المرور',
        'last_login' => 'آخر تسجيل دخول',
        'status' => 'الحالة',
        'status_active' => 'نشط',
        'status_disabled' => 'معطّل',
        'created_at' => 'تاريخ الإنشاء',
        'created' => 'تم إنشاء مستخدم الإدارة بنجاح.',
        'updated' => 'تم تحديث مستخدم الإدارة بنجاح.',
        'reset_password' => 'إعادة تعيين كلمة المرور',
        'reset_password_heading' => 'إعادة تعيين كلمة المرور',
        'reset_password_description' => 'سيتم إنشاء كلمة مرور عشوائية جديدة. يجب عليك إبلاغ المستخدم بها بطريقة آمنة.',
        'password_was_reset' => 'تم إعادة تعيين كلمة المرور بنجاح.',
        'disable' => 'تعطيل',
        'reenable' => 'إعادة تفعيل',
        'disabled_success' => 'تم تعطيل مستخدم الإدارة.',
        'reenabled_success' => 'تم إعادة تفعيل مستخدم الإدارة.',
        'cannot_demote_last_super_admin' => 'لا يمكن تخفيض رتبة آخر مسؤول أعلى.',
    ],

    // Activity Log
    'activity_log' => [
        'singular' => 'سجل نشاط',
        'plural' => 'سجل النشاط',
        'occurred_at' => 'الوقت',
        'admin_user' => 'المستخدم الإداري',
        'event_type' => 'الحدث',
        'ip_address' => 'عنوان IP',
        'user_agent' => 'وكيل المستخدم',
        'payload' => 'التفاصيل',
        'attempted_email' => 'البريد المُستخدم',
        'target_type' => 'نوع الهدف',
        'target_id' => 'معرّف الهدف',
        'system' => 'النظام',
    ],

    // Activity event labels
    'activity' => [
        'login_success' => 'تسجيل دخول ناجح',
        'login_failed' => 'فشل تسجيل الدخول',
        'login_blocked_disabled' => 'تسجيل دخول محظور (معطّل)',
        'login_rate_limited' => 'تسجيل دخول محدود السرعة',
        'logout' => 'تسجيل خروج',
        'session_expired' => 'انتهاء الجلسة',
        'user_created' => 'إنشاء مستخدم',
        'user_updated' => 'تحديث مستخدم',
        'user_password_reset' => 'إعادة تعيين كلمة المرور',
        'user_disabled' => 'تعطيل مستخدم',
        'user_reenabled' => 'إعادة تفعيل مستخدم',
        'locale_changed' => 'تغيير اللغة',
        'plan_created' => 'إنشاء خطة',
        'plan_updated' => 'تحديث خطة',
        'impersonation_started' => 'بدء انتحال الهوية',
        'impersonation_ended' => 'إنهاء انتحال الهوية',
    ],

    // Workspaces
    'workspaces' => [
        'singular' => 'مساحة عمل',
        'plural' => 'مساحات العمل',
        'name' => 'الاسم',
        'slug' => 'المعرّف',
        'locale' => 'اللغة',
        'members_count' => 'الأعضاء',
        'created_at' => 'تاريخ الإنشاء',
        'pdpl_consent' => 'موافقة PDPL',
        'consent_yes' => 'تم الحصول عليها',
        'consent_no' => 'لم يتم الحصول عليها',
        'consent_date' => 'تاريخ الموافقة',
        'consent_version' => 'إصدار الموافقة',
        'section_details' => 'تفاصيل مساحة العمل',
        'section_pdpl' => 'الامتثال لقانون حماية البيانات',
    ],

    // Impersonation
    'impersonation' => [
        'start' => 'انتحال هوية المستخدم',
        'stop' => 'إيقاف الانتحال',
        'modal_heading' => 'بدء انتحال الهوية',
        'modal_description' => 'سيتم تسجيل دخولك كالمستخدم المحدد. جميع الإجراءات أثناء الانتحال مسجلة في سجل التدقيق.',
        'select_user' => 'اختر المستخدم',
        'purpose' => 'الغرض (مطلوب للتدقيق)',
        'already_active' => 'لديك جلسة انتحال نشطة بالفعل. أوقفها أولا.',
        'banner' => 'تنتحل هوية :name — جميع الإجراءات قيد التسجيل.',
    ],

    // Subscriptions
    'subscriptions' => [
        'singular' => 'اشتراك',
        'plural' => 'الاشتراكات',
        'state_trial' => 'تجريبي',
        'state_active' => 'نشط',
        'state_past_due' => 'متأخر الدفع',
        'state_suspended' => 'معلّق',
        'state_cancelled' => 'ملغى',
    ],

    // Plans CRUD
    'plans' => [
        'singular' => 'خطة',
        'plural' => 'الخطط',
        'slug' => 'المعرّف',
        'name_en' => 'الاسم (إنجليزي)',
        'name_ar' => 'الاسم (عربي)',
        'description_en' => 'الوصف (إنجليزي)',
        'description_ar' => 'الوصف (عربي)',
        'price_per_seat' => 'السعر لكل مقعد (دولار)',
        'max_seats' => 'الحد الأقصى للمقاعد',
        'max_matters' => 'الحد الأقصى للقضايا',
        'max_contacts' => 'الحد الأقصى لجهات الاتصال',
        'max_storage_mb' => 'الحد الأقصى للتخزين (ميجابايت)',
        'features' => 'ميزات الخطة',
        'feature_key' => 'الميزة',
        'feature_value' => 'مفعّلة',
        'is_active' => 'نشطة',
        'sort_order' => 'الترتيب',
        'null_unlimited' => 'اتركه فارغا للحد غير المحدود',
        'section_details' => 'تفاصيل الخطة',
        'section_pricing' => 'التسعير',
        'section_limits' => 'حدود الاستخدام',
        'section_features' => 'ميزات الخطة',
    ],

    // Dashboard
    'dashboard' => [
        'total_workspaces' => 'إجمالي مساحات العمل',
        'total_users' => 'إجمالي المستخدمين',
        'active_subscriptions' => 'الاشتراكات النشطة',
        'monthly_revenue' => 'الإيرادات الشهرية',
        'updated_when_available' => 'يتم التحديث عند توفر بيانات الاشتراكات',
    ],

    // Common actions
    'actions' => [
        'save' => 'حفظ',
        'cancel' => 'إلغاء',
        'delete' => 'حذف',
        'confirm' => 'تأكيد',
        'back' => 'رجوع',
    ],

    // Entitlements
    'entitlements' => [
        'suspended_read_only' => 'مساحة عملك معلّقة. الوصول للقراءة فقط.',
        'seat_limit_reached' => 'تم الوصول للحد الأقصى للمقاعد في خطتك.',
        'matter_limit_reached' => 'تم الوصول للحد الأقصى للقضايا في خطتك.',
        'contact_limit_reached' => 'تم الوصول للحد الأقصى لجهات الاتصال في خطتك.',
        'feature_not_available' => 'هذه الميزة غير متوفرة في خطتك.',
    ],

    // Errors
    'errors' => [
        'unauthorized' => 'ليس لديك صلاحية لتنفيذ هذا الإجراء.',
        'not_found' => 'المورد المطلوب غير موجود.',
    ],

];
