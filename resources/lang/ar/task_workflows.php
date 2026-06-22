<?php

return [
    // Entity names
    'workflow' => 'سير العمل',
    'workflows' => 'مسارات العمل',
    'stage' => 'مرحلة',
    'stages' => 'المراحل',
    'transition' => 'انتقال',
    'transitions' => 'الانتقالات',
    'approval' => 'موافقة',
    'approvals' => 'الموافقات',

    // Default workflow names
    'generic_workflow' => 'سير عمل عام',
    'contract_review_workflow' => 'سير مراجعة العقود',
    'litigation_task_workflow' => 'سير مهام التقاضي',

    // Stage names
    'stage_todo' => 'للتنفيذ',
    'stage_in_progress' => 'قيد التنفيذ',
    'stage_done' => 'مكتملة',
    'stage_blocked' => 'معلّقة',
    'stage_drafting' => 'الصياغة',
    'stage_internal_review' => 'المراجعة الداخلية',
    'stage_counterparty_review' => 'مراجعة الطرف الآخر',
    'stage_approval' => 'الموافقة',
    'stage_closed' => 'مغلق',

    // Approval statuses
    'approval_pending' => 'قيد الانتظار',
    'approval_approved' => 'تمت الموافقة',
    'approval_rejected' => 'مرفوض',
    'approval_cancelled' => 'ملغى',

    // Success messages
    'created_success' => 'تم إنشاء سير العمل',
    'updated_success' => 'تم تحديث سير العمل',
    'deleted_success' => 'تم حذف سير العمل',
    'transition_success' => 'تم نقل المهمة بنجاح',
    'approval_requested' => 'تم طلب الموافقة',

    // Error messages
    'error_no_workflow' => 'لا يوجد سير عمل مُعيّن لهذه المهمة',
    'error_invalid_transition' => 'هذا الانتقال غير مسموح',
    'error_role_required' => 'ليس لديك الدور المطلوب لهذا الانتقال',
    'error_not_designated_approver' => 'لست الشخص المعيّن للموافقة على هذا الطلب',
    'error_approval_not_pending' => 'تم الرد على هذه الموافقة مسبقاً',
    'error_has_active_tasks' => 'لا يمكن حذف سير العمل مع وجود مهام نشطة',
];
