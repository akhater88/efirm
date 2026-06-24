<?php

return [

    // Navigation
    'nav' => [
        'admin_users' => 'Admin Users',
        'activity_log' => 'Activity Log',
        'plans' => 'Plans',
        'group_billing' => 'Billing',
        'workspaces' => 'Workspaces',
        'group_platform' => 'Platform',
    ],

    // Panel
    'panel' => [
        'brand' => 'Platform Admin',
    ],

    // Auth
    'auth' => [
        'email' => 'Email',
        'password' => 'Password',
        'login' => 'Sign In',
        'logout' => 'Sign Out',
        'account_disabled' => 'This account has been disabled. Contact a super administrator.',
        'session_expired' => 'Your session has expired. Please sign in again.',
        'session_idle' => 'Your session was closed due to inactivity.',
    ],

    // Roles
    'roles' => [
        'super_admin' => 'Super Admin',
        'support' => 'Support',
        'finance' => 'Finance',
        'read_only' => 'Read Only',
    ],

    // Locales
    'locales' => [
        'ar' => 'Arabic',
        'en' => 'English',
    ],

    // Admin Users CRUD
    'users' => [
        'singular' => 'Admin User',
        'plural' => 'Admin Users',
        'name' => 'Name',
        'email' => 'Email',
        'role' => 'Role',
        'locale' => 'Locale',
        'password' => 'Password',
        'password_confirmation' => 'Confirm Password',
        'last_login' => 'Last Login',
        'status' => 'Status',
        'status_active' => 'Active',
        'status_disabled' => 'Disabled',
        'created_at' => 'Created At',
        'created' => 'Admin user created successfully.',
        'updated' => 'Admin user updated successfully.',
        'reset_password' => 'Reset Password',
        'reset_password_heading' => 'Reset Password',
        'reset_password_description' => 'A new random password will be generated. You must communicate it securely to the user.',
        'password_was_reset' => 'Password has been reset successfully.',
        'disable' => 'Disable',
        'reenable' => 'Re-enable',
        'disabled_success' => 'Admin user has been disabled.',
        'reenabled_success' => 'Admin user has been re-enabled.',
        'cannot_demote_last_super_admin' => 'Cannot demote the last super admin.',
    ],

    // Activity Log
    'activity_log' => [
        'singular' => 'Activity Log Entry',
        'plural' => 'Activity Log',
        'occurred_at' => 'Time',
        'admin_user' => 'Admin User',
        'event_type' => 'Event',
        'ip_address' => 'IP Address',
        'user_agent' => 'User Agent',
        'payload' => 'Details',
        'attempted_email' => 'Attempted Email',
        'target_type' => 'Target Type',
        'target_id' => 'Target ID',
        'system' => 'System',
    ],

    // Activity event labels
    'activity' => [
        'login_success' => 'Login Success',
        'login_failed' => 'Login Failed',
        'login_blocked_disabled' => 'Login Blocked (Disabled)',
        'login_rate_limited' => 'Login Rate Limited',
        'logout' => 'Logout',
        'session_expired' => 'Session Expired',
        'user_created' => 'User Created',
        'user_updated' => 'User Updated',
        'user_password_reset' => 'Password Reset',
        'user_disabled' => 'User Disabled',
        'user_reenabled' => 'User Re-enabled',
        'locale_changed' => 'Locale Changed',
        'plan_created' => 'Plan Created',
        'plan_updated' => 'Plan Updated',
        'impersonation_started' => 'Impersonation Started',
        'impersonation_ended' => 'Impersonation Ended',
        'cancellation_initiated' => 'Cancellation Initiated',
    ],

    // PDPL Retention
    'pdpl' => [
        'retention_days' => '90',
        'retention_notice' => 'Workspace data will be retained for :days days after cancellation per PDPL Law No. 24/2023.',
        'purge_complete' => 'Workspace data has been purged.',
        'consent_version' => 'v1.0-draft',
    ],

    // Workspaces
    'workspaces' => [
        'singular' => 'Workspace',
        'plural' => 'Workspaces',
        'name' => 'Name',
        'slug' => 'Slug',
        'locale' => 'Locale',
        'members_count' => 'Members',
        'created_at' => 'Created At',
        'pdpl_consent' => 'PDPL Consent',
        'consent_yes' => 'Obtained',
        'consent_no' => 'Not obtained',
        'consent_date' => 'Consent Date',
        'consent_version' => 'Consent Version',
        'section_details' => 'Workspace Details',
        'section_pdpl' => 'PDPL Compliance',
    ],

    // Impersonation
    'impersonation' => [
        'start' => 'Impersonate User',
        'stop' => 'Stop Impersonating',
        'modal_heading' => 'Start Impersonation',
        'modal_description' => 'You will be logged in as the selected user. All actions during impersonation are recorded in the audit log.',
        'select_user' => 'Select User',
        'purpose' => 'Purpose (required for audit)',
        'already_active' => 'You already have an active impersonation session. Stop it first.',
        'banner' => 'Impersonating :name — all actions are being recorded.',
    ],

    // Subscriptions
    'subscriptions' => [
        'singular' => 'Subscription',
        'plural' => 'Subscriptions',
        'state_trial' => 'Trial',
        'state_active' => 'Active',
        'state_past_due' => 'Past Due',
        'state_suspended' => 'Suspended',
        'state_cancelled' => 'Cancelled',
    ],

    // Plans CRUD
    'plans' => [
        'singular' => 'Plan',
        'plural' => 'Plans',
        'slug' => 'Slug',
        'name_en' => 'Name (English)',
        'name_ar' => 'Name (Arabic)',
        'description_en' => 'Description (English)',
        'description_ar' => 'Description (Arabic)',
        'price_per_seat' => 'Price per Seat (USD)',
        'max_seats' => 'Max Seats',
        'max_matters' => 'Max Matters',
        'max_contacts' => 'Max Contacts',
        'max_storage_mb' => 'Max Storage (MB)',
        'features' => 'Feature Flags',
        'feature_key' => 'Feature',
        'feature_value' => 'Enabled',
        'is_active' => 'Active',
        'sort_order' => 'Sort Order',
        'null_unlimited' => 'Leave empty for unlimited',
        'section_details' => 'Plan Details',
        'section_pricing' => 'Pricing',
        'section_limits' => 'Usage Limits',
        'section_features' => 'Feature Flags',
    ],

    // Dashboard
    'dashboard' => [
        'total_workspaces' => 'Total Workspaces',
        'total_users' => 'Total Users',
        'active_subscriptions' => 'Active Subscriptions',
        'monthly_revenue' => 'Monthly Revenue',
        'updated_when_available' => 'Updated when subscription data is available',
    ],

    // Common actions
    'actions' => [
        'save' => 'Save',
        'cancel' => 'Cancel',
        'delete' => 'Delete',
        'confirm' => 'Confirm',
        'back' => 'Back',
    ],

    // Entitlements
    'entitlements' => [
        'suspended_read_only' => 'Your workspace is suspended. Read-only access.',
        'seat_limit_reached' => 'Seat limit reached for your plan.',
        'matter_limit_reached' => 'Matter limit reached for your plan.',
        'contact_limit_reached' => 'Contact limit reached for your plan.',
        'feature_not_available' => 'This feature is not available on your plan.',
    ],

    // Errors
    'errors' => [
        'unauthorized' => 'You do not have permission to perform this action.',
        'not_found' => 'The requested resource was not found.',
    ],

];
