<?php

return [

    // Navigation
    'nav' => [
        'admin_users' => 'مستخدمو الإدارة',
        'activity_log' => 'سجل النشاط',
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

    // Errors
    'errors' => [
        'unauthorized' => 'ليس لديك صلاحية لتنفيذ هذا الإجراء.',
        'not_found' => 'المورد المطلوب غير موجود.',
    ],

];
