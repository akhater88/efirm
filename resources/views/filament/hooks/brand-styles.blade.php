<style>
    /* eFirm brand overrides for Filament panels */

    /* Sidebar: brand-700 background with white text */
    .fi-sidebar {
        --sidebar-width: 16rem;
        background-color: #330000 !important;
    }

    .fi-sidebar-header {
        background-color: #330000 !important;
        border-bottom-color: rgba(255, 255, 255, 0.1) !important;
    }

    .fi-sidebar-nav-groups {
        background-color: #330000 !important;
    }

    /* Sidebar text: white/light */
    .fi-sidebar-group-label,
    .fi-sidebar-item-label {
        color: #D6CDCD !important;
    }

    .fi-sidebar-item-button {
        color: #D6CDCD !important;
    }

    .fi-sidebar-item-button:hover {
        background-color: #260000 !important;
        color: #FAFAF9 !important;
    }

    /* Active sidebar item */
    .fi-sidebar-item-active .fi-sidebar-item-button {
        background-color: #440000 !important;
        color: #FFFFFF !important;
    }

    .fi-sidebar-item-active .fi-sidebar-item-label {
        color: #FFFFFF !important;
    }

    .fi-sidebar-item-active .fi-sidebar-item-icon {
        color: #FFFFFF !important;
    }

    /* Sidebar icons */
    .fi-sidebar-item-icon {
        color: #A89090 !important;
    }

    .fi-sidebar-item-button:hover .fi-sidebar-item-icon {
        color: #FAFAF9 !important;
    }

    /* Sidebar group labels */
    .fi-sidebar-group-label {
        color: #7A5050 !important;
    }

    /* Sidebar dividers */
    .fi-sidebar-group + .fi-sidebar-group {
        border-top-color: rgba(255, 255, 255, 0.08) !important;
    }

    /* Sidebar collapse button */
    .fi-sidebar-close-btn,
    .fi-sidebar-open-btn {
        color: #D6CDCD !important;
    }

    .fi-sidebar-close-btn:hover,
    .fi-sidebar-open-btn:hover {
        background-color: #260000 !important;
    }

    /* Topbar: brand-700 background with white text */
    .fi-topbar {
        background-color: #330000 !important;
        border-bottom-color: rgba(255, 255, 255, 0.1) !important;
    }

    .fi-topbar nav {
        background-color: #330000 !important;
    }

    /* Topbar text and icons: white */
    .fi-topbar-item-label,
    .fi-topbar button,
    .fi-topbar a {
        color: #D6CDCD !important;
    }

    .fi-topbar button:hover,
    .fi-topbar a:hover {
        color: #FFFFFF !important;
    }

    /* Topbar user menu, breadcrumbs, search */
    .fi-topbar .fi-breadcrumbs ol li,
    .fi-topbar .fi-breadcrumbs a,
    .fi-topbar .fi-breadcrumbs span {
        color: #D6CDCD !important;
    }

    /* Topbar icons */
    .fi-topbar svg {
        color: #D6CDCD !important;
    }

    .fi-topbar button:hover svg,
    .fi-topbar a:hover svg {
        color: #FFFFFF !important;
    }

    /* Global search in topbar */
    .fi-global-search-field input {
        background-color: rgba(255, 255, 255, 0.1) !important;
        border-color: rgba(255, 255, 255, 0.2) !important;
        color: #FFFFFF !important;
    }

    .fi-global-search-field input::placeholder {
        color: #A89090 !important;
    }

    /* Brand logo — reversed (white) SVG used directly via panel brandLogo config */

    /* Tenant menu in sidebar */
    .fi-tenant-menu-trigger {
        color: #D6CDCD !important;
    }

    .fi-tenant-menu-trigger:hover {
        background-color: #260000 !important;
        color: #FAFAF9 !important;
    }

    /* Tenant name text */
    .fi-tenant-menu-trigger-tenant-name {
        color: #FAFAF9 !important;
        font-weight: 600;
    }

    .fi-tenant-menu-trigger svg {
        color: #D6CDCD !important;
    }

    .fi-tenant-menu-trigger:hover svg {
        color: #FAFAF9 !important;
    }

    /* User avatar in sidebar — light ring on dark background */
    .fi-sidebar .fi-avatar,
    .fi-sidebar .fi-user-avatar {
        border-color: rgba(255, 255, 255, 0.2) !important;
    }

    /* User avatar in topbar — light ring on dark background */
    .fi-topbar .fi-avatar,
    .fi-topbar .fi-user-avatar {
        border-color: rgba(255, 255, 255, 0.2) !important;
    }

    /* Avatar initials text — white on brand background */
    .fi-avatar span,
    .fi-user-avatar span {
        color: #FFFFFF !important;
    }

    /* Avatar background — slightly lighter green for contrast */
    .fi-sidebar .fi-avatar,
    .fi-topbar .fi-avatar {
        background-color: #440000 !important;
    }

    /* User menu button text in topbar */
    .fi-topbar .fi-user-menu button span {
        color: #D6CDCD !important;
    }

    .fi-topbar .fi-user-menu button:hover span {
        color: #FFFFFF !important;
    }

    /* Footer in sidebar */
    .fi-sidebar-footer {
        border-top-color: rgba(255, 255, 255, 0.1) !important;
        color: #D6CDCD !important;
    }

    .fi-sidebar-footer button,
    .fi-sidebar-footer a {
        color: #D6CDCD !important;
    }

    .fi-sidebar-footer button:hover,
    .fi-sidebar-footer a:hover {
        color: #FAFAF9 !important;
    }
</style>
