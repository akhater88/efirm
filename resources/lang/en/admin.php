<?php

return [

    // Navigation
    'nav' => [
        'admin_users' => 'Admin Users',
        'activity_log' => 'Activity Log',
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

    // Errors
    'errors' => [
        'unauthorized' => 'You do not have permission to perform this action.',
        'not_found' => 'The requested resource was not found.',
    ],

];
