<style>
    /* ═══ SGFE Government Styling ═══ */
    .sgfe-sidebar {
        background: linear-gradient(180deg, #0c1a2e 0%, #162d50 100%);
        width: 260px;
        min-height: 100vh;
        transition: transform 0.3s ease;
    }
    .sgfe-sidebar a, .sgfe-sidebar button { transition: all 0.15s ease; }
    .sgfe-sidebar .nav-item {
        display: flex; align-items: center; gap: 0.75rem;
        padding: 0.6rem 1.25rem; color: #94a3b8;
        font-size: 0.875rem; font-weight: 500;
        border-radius: 0.375rem; margin: 0 0.5rem;
        text-decoration: none;
    }
    .sgfe-sidebar .nav-item:hover { background: rgba(255,255,255,0.08); color: #e2e8f0; }
    .sgfe-sidebar .nav-item.active { background: rgba(212,160,23,0.15); color: #d4a017; font-weight: 600; }
    .sgfe-sidebar .nav-section {
        font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em;
        color: #475569; padding: 1.25rem 1.25rem 0.4rem; font-weight: 700;
    }
    .sgfe-topbar { background: #fff; border-bottom: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }

    /* KPI Cards */
    .kpi-card {
        background: #fff; border: 1px solid #e2e8f0;
        border-radius: 0.75rem; padding: 1.25rem 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    }
    .kpi-card .kpi-label { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: #64748b; letter-spacing: 0.05em; }
    .kpi-card .kpi-value { font-size: 1.5rem; font-weight: 700; margin-top: 0.25rem; }

    /* Mobile sidebar */
    .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 40; }
    @media (max-width: 1023px) {
        .sgfe-sidebar { position: fixed; z-index: 50; transform: translateX(-100%); }
        .sgfe-sidebar.open { transform: translateX(0); }
        .sidebar-overlay.open { display: block; }
    }

    /* Notifications badge pulse */
    .badge-pulse { animation: pulse-badge 2s ease-in-out infinite; }
    @keyframes pulse-badge { 0%,100% { opacity: 1; } 50% { opacity: 0.6; } }

    /* Responsive table scroll */
    @media (max-width: 640px) {
        .responsive-table { display: block; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .responsive-table table { min-width: 600px; }
    }

    /* Mobile-first tweaks */
    @media (max-width: 480px) {
        .kpi-card { padding: 1rem 1.25rem; }
        .kpi-card .kpi-value { font-size: 1.25rem; }
    }
    @media (max-width: 1023px) {
        .sgfe-topbar .page-title { font-size: 0.875rem; }
    }
</style>
