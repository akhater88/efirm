<style>
    /* eFirm brand overrides for Filament panels */

    /* Sidebar: brand-700 background with white text */
    .fi-sidebar {
        --sidebar-width: 16rem;
        background-color: #072E17 !important;
    }

    .fi-sidebar-header {
        background-color: #072E17 !important;
        border-bottom-color: rgba(255, 255, 255, 0.1) !important;
    }

    .fi-sidebar-nav-groups {
        background-color: #072E17 !important;
    }

    /* Sidebar text: white/light */
    .fi-sidebar-group-label,
    .fi-sidebar-item-label {
        color: #D6D3D1 !important;
    }

    .fi-sidebar-item-button {
        color: #D6D3D1 !important;
    }

    .fi-sidebar-item-button:hover {
        background-color: #052015 !important;
        color: #FAFAF9 !important;
    }

    /* Active sidebar item */
    .fi-sidebar-item-active .fi-sidebar-item-button {
        background-color: #094B26 !important;
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
        color: #A8A29E !important;
    }

    .fi-sidebar-item-button:hover .fi-sidebar-item-icon {
        color: #FAFAF9 !important;
    }

    /* Sidebar group labels */
    .fi-sidebar-group-label {
        color: #78716C !important;
    }

    /* Sidebar dividers */
    .fi-sidebar-group + .fi-sidebar-group {
        border-top-color: rgba(255, 255, 255, 0.08) !important;
    }

    /* Sidebar collapse button */
    .fi-sidebar-close-btn,
    .fi-sidebar-open-btn {
        color: #D6D3D1 !important;
    }

    .fi-sidebar-close-btn:hover,
    .fi-sidebar-open-btn:hover {
        background-color: #052015 !important;
    }

    /* Topbar: white background with brand accent */
    .fi-topbar {
        border-bottom-color: #E7E5E4 !important;
    }

    /* Brand logo in sidebar — ensure reversed logo is visible */
    .fi-logo img,
    .fi-sidebar-header img {
        filter: brightness(0) invert(1);
    }

    /* Tenant menu text in sidebar */
    .fi-tenant-menu-trigger {
        color: #D6D3D1 !important;
    }

    .fi-tenant-menu-trigger:hover {
        background-color: #052015 !important;
        color: #FAFAF9 !important;
    }

    /* Footer in sidebar */
    .fi-sidebar-footer {
        border-top-color: rgba(255, 255, 255, 0.1) !important;
    }
</style>
