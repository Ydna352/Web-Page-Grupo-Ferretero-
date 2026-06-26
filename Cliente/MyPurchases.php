<?php require_once '../PHP/auth.php'; auth_require_rol('cliente'); ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Compras – Portal Cliente</title>
    <link rel="apple-touch-icon" sizes="180x180" href="../Administrador/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../Administrador/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../Administrador/favicon-16x16.png">
    <link rel="manifest" href="../Administrador/site.webmanifest">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            width: 100%;
            height: 100%;
        }

        body {
            font-family: sans-serif;
            background-color: #f3f4f6;
        }

        .root-layout {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        #desktopSidebar {
            display: none;
            width: 16rem;
            min-width: 16rem;
            height: 100vh;
            position: sticky;
            top: 0;
            border-right: 1px solid #e5e7eb;
            background-color: white;
            flex-direction: column;
            flex-shrink: 0;
        }

        @media (min-width: 1024px) {
            #desktopSidebar {
                display: flex;
            }

            #mobileHeader {
                display: none !important;
            }
        }

        .nav-btn {
            width: 100%;
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            border: none;
            background: transparent;
            cursor: pointer;
            text-decoration: none;
            color: #374151;
            font-family: inherit;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .nav-btn:hover {
            background-color: #f3f4f6;
        }

        .nav-btn.active {
            background-color: #74cb3c !important;
            color: white !important;
        }

        .main-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            width: 100%;
        }

        #mobileHeader {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            background-color: white;
            border-bottom: 1px solid #e5e7eb;
            position: sticky;
            top: 0;
            z-index: 40;
        }

        main {
            flex: 1;
            padding: 1.5rem;
            overflow-x: hidden;
            width: 100%;
        }

        .page-inner {
            background-color: #fff;
            border-radius: 0.5rem;
            padding: 2rem;
            width: 100%;
        }

        /* Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem;
            margin-top: 1.5rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        .stat-card {
            border: 2px solid;
            border-radius: 0.75rem;
            padding: 1.25rem;
            background: white;
        }

        .stat-card .s-lbl {
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .stat-card .s-val {
            font-size: 1.75rem;
            font-weight: 700;
        }

        /* Filter card */
        .filter-card {
            border: 2px solid #3e8cdd;
            border-radius: 0.75rem;
            padding: 1.25rem 1.5rem;
            background: white;
            margin-bottom: 1.5rem;
        }

        .filter-card-hdr {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
            color: #3e8cdd;
            margin-bottom: 1rem;
            font-size: 1rem;
        }

        .filter-grid {
            display: flex;
            align-items: flex-end;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .filter-grid > div {
            flex: 1;
            min-width: 140px;
        }

        .filter-acciones {
            display: flex;
            align-items: flex-end;
            margin-left: auto;
        }

        @media (max-width: 640px) {
            .filter-grid {
                flex-direction: column;
                align-items: stretch;
            }
            .filter-acciones {
                margin-left: 0;
            }
        }

        .filter-label {
            font-size: 0.8rem;
            color: #6b7280;
            margin-bottom: 0.3rem;
            display: block;
        }

        .filter-input {
            width: 100%;
            padding: 0.45rem 0.75rem;
            border: 2px solid #3e8cdd;
            border-radius: 0.375rem;
            font-family: inherit;
            font-size: 0.875rem;
            color: #374151;
            outline: none;
        }

        .btn-filtro {
            padding: 8px 18px;
            border: none;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            background-color: #f1f5f9;
            color: #1e293b;
            transition: opacity 0.2s;
            font-family: inherit;
        }

        .btn-filtro:hover {
            opacity: 0.85;
        }

        /* Section header */
        .section-hdr {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
            font-size: 1.25rem;
            color: #3e8cdd;
            margin-bottom: 1rem;
        }

        /* Tickets grid */
        .tickets-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem;
        }

        @media (max-width: 1024px) {
            .tickets-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .tickets-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Ticket card */
        .ticket-card {
            border: 2px solid #e5e7eb;
            border-radius: 0.75rem;
            background: white;
            cursor: pointer;
            transition: box-shadow 0.2s, transform 0.2s;
            overflow: hidden;
        }

        .ticket-card:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            transform: scale(1.03);
        }

        .ticket-card-hdr {
            background: #f8fafc;
            padding: 1rem 1.25rem 0.75rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .ticket-card-hdr-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
        }

        .ticket-id {
            font-size: 1rem;
            font-weight: 700;
            color: #3e8cdd;
            margin-bottom: 0.35rem;
        }

        .ticket-date {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.75rem;
            color: #6b7280;
        }

        .badge-items {
            background: #74cb3c;
            color: white;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.2rem 0.55rem;
            border-radius: 9999px;
            white-space: nowrap;
        }

        .ticket-card-body {
            padding: 1rem 1.25rem 0.75rem;
        }

        .ticket-preview-item {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            margin-bottom: 0.6rem;
        }

        .ticket-preview-item svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .preview-item-name {
            font-size: 0.8rem;
            font-weight: 600;
            color: #1f2937;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .preview-item-sub {
            font-size: 0.72rem;
            color: #6b7280;
            margin-top: 1px;
        }

        .preview-item-sub span {
            font-weight: 600;
            color: #74cb3c;
        }

        .ticket-more {
            font-size: 0.72rem;
            color: #9ca3af;
            font-style: italic;
            margin-bottom: 0.75rem;
        }

        .ticket-card-footer {
            border-top: 1px solid #e5e7eb;
            padding: 0.75rem 1.25rem;
        }

        .ticket-footer-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.6rem;
        }

        .ticket-total-lbl {
            font-size: 0.85rem;
            font-weight: 600;
            color: #374151;
        }

        .ticket-total-val {
            font-size: 1.15rem;
            font-weight: 700;
            color: #74cb3c;
        }

        .btn-ver-det {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            width: 100%;
            padding: 0.4rem 0;
            background: #3e8cdd;
            color: white;
            border: none;
            border-radius: 0.375rem;
            font-size: 0.8rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
        }

        .btn-ver-det svg {
            width: 14px;
            height: 14px;
        }

        .btn-ver-det:hover {
            background: #2d7acc;
        }

        /* Empty state */
        .empty-state {
            border: 2px solid #3e8cdd;
            border-radius: 0.75rem;
            padding: 3rem 2rem;
            text-align: center;
            background: white;
        }

        .empty-icon {
            width: 4rem;
            height: 4rem;
            margin: 0 auto 1rem;
            color: #d1d5db;
        }

        .empty-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }

        .empty-sub {
            font-size: 0.875rem;
            color: #9ca3af;
        }

        /* Modal */
        .modal-bg {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 100;
            background: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .modal-bg.open {
            display: flex;
        }

        .modal {
            background: white;
            border-radius: 0.75rem;
            padding: 2rem;
            width: 100%;
            max-width: 680px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .2);
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #3e8cdd;
        }

        .modal-meta {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            margin-top: 0.4rem;
            font-size: 0.8rem;
            color: #6b7280;
        }

        .modal-meta-item {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .modal-meta-item svg {
            width: 14px;
            height: 14px;
            color: #3e8cdd;
        }

        .modal-products-title {
            font-weight: 700;
            font-size: 1rem;
            color: #3e8cdd;
            margin: 1.5rem 0 0.75rem;
        }

        .modal-product {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem;
            background: #f9fafb;
            border-radius: 0.5rem;
            margin-bottom: 0.75rem;
            transition: background .15s;
        }

        .modal-product:hover {
            background: #f3f4f6;
        }

        .modal-product svg {
            width: 32px;
            height: 32px;
            color: #3e8cdd;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .modal-product-name {
            font-size: 0.9rem;
            font-weight: 700;
            color: #3e8cdd;
            margin-bottom: 0.25rem;
        }

        .modal-product-badge {
            display: inline-block;
            padding: 0.1rem 0.5rem;
            border: 1px solid #74cb3c;
            color: #74cb3c;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-bottom: 0.4rem;
        }

        .modal-product-desc {
            font-size: 0.78rem;
            color: #6b7280;
            margin-bottom: 0.6rem;
        }

        .modal-product-nums {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
            font-size: 0.8rem;
        }

        .modal-product-nums .num-lbl {
            color: #9ca3af;
            margin-bottom: 0.1rem;
        }

        .modal-product-nums .num-val {
            font-weight: 600;
        }

        .modal-product-nums .num-val.green {
            color: #74cb3c;
        }

        .modal-total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 2px solid #3e8cdd;
            margin-top: 1.5rem;
            padding-top: 1.25rem;
        }

        .modal-total-lbl {
            font-size: 1.1rem;
            font-weight: 700;
        }

        .modal-total-val {
            font-size: 1.75rem;
            font-weight: 700;
            color: #74cb3c;
        }

        .modal-close {
            display: block;
            width: 100%;
            margin-top: 1.25rem;
            padding: 0.6rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            background: white;
            cursor: pointer;
            font-family: inherit;
            font-size: 0.9rem;
        }

        .modal-close:hover {
            background: #f9fafb;
        }
    </style>
</head>

<body>
    <div class="root-layout">
        <!-- Desktop Sidebar -->
        <aside id="desktopSidebar">
            <div style="padding:1.5rem;border-bottom:1px solid #e5e7eb;background-color:#74cb3c;">
                <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.5rem;">
                    <span style="font-size:1.5rem;background:white;border-radius:0.5rem;padding:0.25rem;">🏢</span>
                    <h1 style="font-size:1.25rem;font-weight:bold;color:white;margin:0;">Portal Cliente</h1>
                </div>
                <p style="font-size:0.875rem;color:#dcfce7;margin:0;">Cliente Demo</p>
            </div>
            <nav style="flex:1;padding:1rem;overflow-y:auto;" id="desktopNav"></nav>
            <div style="padding:1rem;border-top:1px solid #e5e7eb;margin-top:auto;">
                <button onclick="handleLogout()"
                    style="width:100%;display:flex;align-items:center;padding:0.5rem 1rem;color:#dc2626;border:1px solid #fca5a5;background:transparent;border-radius:0.375rem;cursor:pointer;">
                    <span style="margin-right:0.75rem;">🚪</span> Cerrar Sesión
                </button>
            </div>
        </aside>

        <!-- Mobile overlay -->
        <div id="mobileMenuOverlay"
            style="display:none;position:fixed;inset:0;z-index:50;background-color:rgba(0,0,0,0.5);">
            <aside style="width:16rem;height:100%;background-color:white;display:flex;flex-direction:column;">
                <div
                    style="padding:1.5rem;border-bottom:1px solid #e5e7eb;background-color:#74cb3c;display:flex;justify-content:space-between;align-items:center;">
                    <div style="display:flex;align-items:center;gap:0.5rem;">
                        <span style="font-size:1.25rem;background:white;border-radius:0.5rem;padding:0.25rem;">🏢</span>
                        <div>
                            <h1 style="font-size:1.125rem;font-weight:bold;color:white;margin:0;">Cliente</h1>
                            <p style="font-size:0.75rem;color:#dcfce7;margin:0;">Cliente Demo</p>
                        </div>
                    </div>
                    <button onclick="toggleMobileMenu(false)"
                        style="color:white;background:none;border:none;font-size:1.5rem;cursor:pointer;">&times;</button>
                </div>
                <nav style="flex:1;padding:1rem;overflow-y:auto;" id="mobileNav"></nav>
                <div style="padding:1rem;border-top:1px solid #e5e7eb;margin-top:auto;">
                    <button onclick="handleLogout()"
                        style="width:100%;display:flex;align-items:center;padding:0.5rem 1rem;color:#dc2626;border:1px solid #fca5a5;background:transparent;border-radius:0.375rem;cursor:pointer;">
                        <span style="margin-right:0.75rem;">🚪</span> Cerrar Sesión
                    </button>
                </div>
            </aside>
        </div>

        <div class="main-area">
            <header id="mobileHeader">
                <button onclick="toggleMobileMenu(true)"
                    style="color:#74cb3c;background:none;border:none;font-size:1.5rem;cursor:pointer;">☰</button>
                <div style="display:flex;align-items:center;gap:0.5rem;">
                    <span style="font-size:1.25rem;">🏢</span>
                    <h1 style="font-weight:bold;color:#74cb3c;margin:0;font-size:1.125rem;">Portal Cliente</h1>
                </div>
                <div style="width:1.5rem;"></div>
            </header>

            <main>
                <div class="page-inner">
                    <h1 style="font-size:1.875rem;font-weight:bold;color:#3e8cdd;">Mis Compras</h1>
                    <p style="color:#4b5563;margin-top:0.375rem;">Consulta el historial de tus compras organizadas por
                        ticket</p>

                    <!-- Stats -->
                    <div class="stats-grid" id="statsGrid"></div>

                    <!-- Filter -->
                    <div class="filter-card">
                        <div class="filter-card-hdr">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" style="width:20px;height:20px;">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                            </svg>
                            Filtrar por Fecha
                        </div>
                        <div class="filter-grid">
                            <div>
                                <label class="filter-label" for="dateFrom">Desde</label>
                                <input class="filter-input" type="date" id="dateFrom" oninput="applyFilter()">
                            </div>
                            <div>
                                <label class="filter-label" for="dateTo">Hasta</label>
                                <input class="filter-input" type="date" id="dateTo" oninput="applyFilter()">
                            </div>
                            <div class="filter-acciones">
                                <button class="btn-filtro" id="btnClear" onclick="clearFilter()">Limpiar filtros</button>
                            </div>
                        </div>
                    </div>

                    <!-- Tickets section -->
                    <div class="section-hdr">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" style="width:24px;height:24px;">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                        </svg>
                        Historial de Tickets
                    </div>

                    <div id="ticketsGrid" class="tickets-grid"></div>
                    <div id="emptyState" class="empty-state" style="display:none;">
                        <svg class="empty-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                        </svg>
                        <p class="empty-title" id="emptyTitle">No hay compras registradas</p>
                        <p class="empty-sub" id="emptySub">Tus tickets de compra aparecerán aquí una vez realices una
                            compra</p>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal-bg" id="modalBg">
        <div class="modal">
            <div class="modal-title" id="mTitle"></div>
            <div class="modal-meta">
                <div class="modal-meta-item">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5" />
                    </svg>
                    <span id="mDate"></span>
                </div>
                <div class="modal-meta-item">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                    <span id="mWorker"></span>
                </div>
            </div>

            <p class="modal-products-title" id="mProductsTitle"></p>
            <div id="mProducts"></div>

            <div class="modal-total-row">
                <span class="modal-total-lbl">Total del Ticket:</span>
                <span class="modal-total-val" id="mTotal"></span>
            </div>

            <a id="btnGenerarFacturaModal"
               style="display:block;width:100%;margin-top:1rem;padding:0.65rem 0;
                      background:#74cb3c;color:white;border:none;border-radius:0.375rem;
                      cursor:pointer;font-family:inherit;font-size:0.9rem;font-weight:600;
                      text-align:center;text-decoration:none;"
               href="#">📄 Generar Factura</a>
            <button class="modal-close" onclick="closeModal()">Cerrar</button>
        </div>
    </div>

    <script>
        /* ── NAV ── */
        const NAV_ITEMS = [
            { path: 'Dashboard.php', label: 'Panel Principal', icon: '📊' },
            { path: 'company_info.php', label: 'Información Empresa', icon: '🏢' },
            { path: 'MyPurchases.php', label: 'Mis Compras', icon: '🛍️' },
            { path: 'InvoiceGeneration.php', label: 'Generar Factura', icon: '📄' },
            { path: 'Profile.php', label: 'Perfil', icon: '👤' },
            { path: 'catalogo.php', label: 'Catálogo', icon: '🛒' }
        ];
        var idClienteLocal = parseInt(localStorage.getItem('id_cliente') || '6021');
        const currentPage = location.pathname.split('/').pop() || 'Dashboard.php';
        function renderNav(id) {
            const c = document.getElementById(id); if (!c) return; c.innerHTML = '';
            NAV_ITEMS.forEach(item => {
                const a = document.createElement('a'); a.href = item.path;
                a.className = 'nav-btn' + (currentPage === item.path ? ' active' : '');
                a.innerHTML = '<span style="margin-right:0.75rem;">' + item.icon + '</span>' + item.label;
                if (id === 'mobileNav') a.onclick = () => toggleMobileMenu(false);
                c.appendChild(a);
            });
        }
        function toggleMobileMenu(show) { document.getElementById('mobileMenuOverlay').style.display = show ? 'block' : 'none'; }
        function handleLogout() { location.href = '../Home/Home.html'; }

        /* ── DATOS DESDE BASE DE DATOS ── */
        // Estas variables se llenan via fetch al cargar la página
        let allTickets = [];
        let filtered   = [];

        // Mapa id_material → objeto material (para lookup rápido)
        let materialMap = {};
        let trabajadorMap = {};

        function getMaterial(id) {
            return materialMap[id] || null;
        }


        /* ── HELPERS ── */
        function fmtDate(d, long) {
            const opts = long
                ? { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }
                : { day: '2-digit', month: 'short', year: 'numeric' };
            return new Date(d + 'T12:00:00').toLocaleDateString('es-MX', opts);
        }

        function fmtMXN(n) { return '$' + n.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }

        function padId(id) { return '#' + id.toString().padStart(4, '0'); }


        /* ── ICONS (inline SVG strings) ── */
        const iconPackage  = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>`;
        const iconCalendar = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5"/></svg>`;
        const iconEye      = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>`;

        /* ── RENDER STATS ── */

        function renderStats(tickets) {
            const total = tickets.reduce((s, t) => s + t.total, 0);
            const last = tickets.length > 0 ? tickets[0].date : null;
            document.getElementById('statsGrid').innerHTML = `
                <div class="stat-card" style="border-color:#3e8cdd;">
                    <p class="s-lbl" style="color:#3e8cdd;">Total de Tickets</p>
                    <p class="s-val" style="color:#3e8cdd;">${tickets.length}</p>
                </div>
                <div class="stat-card" style="border-color:#74cb3c;">
                    <p class="s-lbl" style="color:#74cb3c;">Monto Total</p>
                    <p class="s-val" style="color:#74cb3c;">${fmtMXN(total)}</p>
                </div>
                <div class="stat-card" style="border-color:#3e8cdd;">
                    <p class="s-lbl" style="color:#3e8cdd;">Última Compra</p>
                    <p class="s-val" style="color:#3e8cdd;font-size:1.2rem;">${last ? fmtDate(last, false) : 'N/A'}</p>
                </div>`;
        }

        /* ── RENDER TICKET CARDS ── */
        function renderTickets(tickets) {
            const grid = document.getElementById('ticketsGrid');
            const empty = document.getElementById('emptyState');
            grid.innerHTML = '';

            if (tickets.length === 0) {
                grid.style.display = 'none';
                empty.style.display = 'block';
                const hasFilter = document.getElementById('dateFrom').value || document.getElementById('dateTo').value;
                document.getElementById('emptyTitle').textContent = hasFilter ? 'No hay compras en este rango de fechas' : 'No hay compras registradas';
                document.getElementById('emptySub').textContent = hasFilter ? 'Intenta ajustar el filtro de fechas para ver más resultados' : 'Tus tickets de compra aparecerán aquí una vez realices una compra';
                return;
            }

            grid.style.display = 'grid';
            empty.style.display = 'none';

            tickets.forEach(ticket => {
                const preview = ticket.items.slice(0, 2);
                const remaining = ticket.items.length - preview.length;

                const previewHTML = preview.map(item => `
                    <div class="ticket-preview-item">
                        <span style="color:#3e8cdd;">${iconPackage}</span>
                        <div style="flex:1;min-width:0;">
                            <p class="preview-item-name">${item.name}</p>
                            <p class="preview-item-sub">${item.quantity} × ${fmtMXN(item.price)} <span>${fmtMXN(item.subtotal)}</span></p>
                        </div>
                    </div>`).join('');

                const moreHTML = remaining > 0
                    ? `<p class="ticket-more">+ ${remaining} producto${remaining !== 1 ? 's' : ''} más...</p>`
                    : '';

                const card = document.createElement('div');
                card.className = 'ticket-card';
                card.onclick = () => openModal(ticket.id);
                card.innerHTML = `
                    <div class="ticket-card-hdr">
                        <div class="ticket-card-hdr-top">
                            <div>
                                <p class="ticket-id">Ticket ${padId(ticket.id)}</p>
                                <div class="ticket-date">
                                    <span style="color:#3e8cdd;">${iconCalendar}</span>
                                    <span>${fmtDate(ticket.date, false)}</span>
                                </div>
                            </div>
                            <span class="badge-items">${ticket.items.length} items</span>
                        </div>
                    </div>
                    <div class="ticket-card-body">
                        ${previewHTML}
                        ${moreHTML}
                    </div>
                    <div class="ticket-card-footer">
                        <div class="ticket-footer-row">
                            <span class="ticket-total-lbl">Total:</span>
                            <span class="ticket-total-val">${fmtMXN(ticket.total)}</span>
                        </div>
                        <button class="btn-ver-det">
                            ${iconEye} Ver detalles
                        </button>
                    </div>`;
                grid.appendChild(card);
            });
        }

        /* ── FETCH DATA ── */
        function loadTickets(from = '', to = '') {
            document.getElementById('ticketsGrid').innerHTML =
                '<p style="color:#6b7280;padding:2rem;">Cargando compras...</p>';
            
            let url = '../PHP/api_cliente.php?accion=compras';
            if (from && to) {
                url += `&fecha_inicio=${from}&fecha_fin=${to}`;
            }

            fetch(url)
                .then(r => r.json())
                .then(data => {
                    if (data.error) {
                        console.error('Error API:', data.error);
                        document.getElementById('ticketsGrid').innerHTML =
                            '<p style="color:#dc2626;padding:2rem;">Error: ' + data.error + '</p>';
                        return;
                    }
                    allTickets = transformarCompras(data);
                    filtered   = [...allTickets];
                    renderStats(allTickets);
                    renderTickets(allTickets);
                })
                .catch(err => {
                    console.error('Error de conexión:', err);
                    document.getElementById('ticketsGrid').innerHTML =
                        '<p style="color:#dc2626;padding:2rem;">Error al cargar compras. Verifica la conexión.</p>';
                });
        }

        /* ── FILTER ── */
        function applyFilter() {
            const from = document.getElementById('dateFrom').value;
            const to = document.getElementById('dateTo').value;

            if (from && to) {
                loadTickets(from, to);
            } else if (!from && !to) {
                loadTickets();
            }
        }

        function clearFilter() {
            document.getElementById('dateFrom').value = '';
            document.getElementById('dateTo').value = '';
            loadTickets();
        }

        /* ── MODAL ── */
        function openModal(id) {
            const ticket = allTickets.find(t => t.id === id);
            if (!ticket) return;

            document.getElementById('mTitle').textContent = 'Ticket ' + padId(ticket.id);
            document.getElementById('mDate').textContent = fmtDate(ticket.date, true);
            document.getElementById('mWorker').textContent = trabajadorMap[ticket.employeeId] || 'Trabajador #' + ticket.employeeId;

            // Botón ver factura
            var btnFac = document.getElementById('btnGenerarFacturaModal');
            if (btnFac) {
                if (ticket.facturado) {
                    btnFac.style.display = 'block';
                    btnFac.textContent = '📄 Ver Factura';
                    btnFac.href = '#';
                    btnFac.onclick = function(e) {
                        e.preventDefault();
                        onClickVerFactura(ticket.id);
                    };
                } else {
                    btnFac.style.display = 'none';
                    btnFac.onclick = null;
                }
            }
            
            document.getElementById('mProductsTitle').textContent = 'Productos (' + ticket.items.length + ')';
            document.getElementById('mTotal').textContent = fmtMXN(ticket.total);

            document.getElementById('mProducts').innerHTML = ticket.items.map(item => `
                <div class="modal-product">
                    ${iconPackage}
                    <div style="flex:1;min-width:0;">
                        <p class="modal-product-name">${item.name}</p>
                        <span class="modal-product-badge">${item.area}</span>
                        <p class="modal-product-desc">${item.description}</p>
                        <div class="modal-product-nums">
                            <div><p class="num-lbl">Cantidad</p><p class="num-val">${item.quantity}</p></div>
                            <div><p class="num-lbl">Precio Unitario</p><p class="num-val">${fmtMXN(item.price)}</p></div>
                            <div><p class="num-lbl">Subtotal</p><p class="num-val green">${fmtMXN(item.subtotal)}</p></div>
                        </div>
                    </div>
                </div>`).join('');

            document.getElementById('modalBg').classList.add('open');
        }

        function closeModal() { document.getElementById('modalBg').classList.remove('open'); }

        document.getElementById('modalBg').addEventListener('click', e => {
            if (e.target === document.getElementById('modalBg')) closeModal();
        });

        function onClickVerFactura(ticketId) {
            // Mostrar estado de carga visual en el botón
            var btnFac = document.getElementById('btnGenerarFacturaModal');
            var originalText = btnFac.textContent;
            btnFac.textContent = '⏳ Cargando...';
            btnFac.style.pointerEvents = 'none';

            fetch('../PHP/generar_factura.php?id_ticket=' + ticketId + '&id_cliente=' + idClienteLocal)
                .then(r => r.json())
                .then(data => {
                    btnFac.textContent = originalText;
                    btnFac.style.pointerEvents = 'auto';

                    if (data.ya_facturado && data.pdf) {
                        window.open('../facturas_pdf/' + data.pdf, '_blank');
                    } else if (data.ya_facturado && !data.pdf) {
                        alert('El ticket está facturado pero no tiene un archivo PDF asociado.');
                    } else if (data.error) {
                        alert('Error: ' + data.error);
                    } else {
                        alert('El ticket aún no ha sido facturado.');
                    }
                })
                .catch(err => {
                    btnFac.textContent = originalText;
                    btnFac.style.pointerEvents = 'auto';
                    console.error('Error al obtener factura:', err);
                    alert('Ocurrió un error al intentar abrir la factura.');
                });
        }

        /* ── TRANSFORM: API response → formato interno ── */
        function transformarCompras(data) {
            // Construir mapa id → material para lookup O(1)
            materialMap = {};
            (data.materiales || []).forEach(m => {
                materialMap[parseInt(m.id)] = m;
            });

            // Mapa id → nombre trabajador
            trabajadorMap = data.trabajadores || {};

            // Agrupar detalles por id_ventas
            const detallesMap = {};
            (data.detalles || []).forEach(d => {
                const vid = parseInt(d.id_ventas);
                if (!detallesMap[vid]) detallesMap[vid] = [];
                detallesMap[vid].push(d);
            });

            // Construir tickets en el formato esperado por renderTickets()
            return (data.tickets || []).map(t => {
                const detalles = detallesMap[parseInt(t.id)] || [];
                const items = detalles.map(d => {
                    const mat = getMaterial(parseInt(d.id_materiales));
                    const precio = parseFloat(d.precio_venta) || 0;
                    const cantidad = parseInt(d.cantidad);
                    return {
                        name:        mat ? mat.nombre    : 'Material #' + d.id_materiales,
                        area:        mat ? mat.area      : 'Sin área',
                        description: mat ? mat.descripcion : '',
                        price:       precio,
                        quantity:    cantidad,
                        subtotal:    cantidad * precio
                    };
                });
                return {
                    id:         parseInt(t.id),
                    date:       t.fecha,
                    employeeId: t.id_trabajadores,
                    facturado:  t.facturado,
                    folio_factura: t.folio_factura,
                    items:      items,
                    total:      items.reduce((s, i) => s + i.subtotal, 0)
                };
            });
        }

        /* ── INIT ── */
        document.addEventListener('DOMContentLoaded', () => {
            renderNav('desktopNav');
            renderNav('mobileNav');
            loadTickets();
        });
    </script>
    <script src="ClienteSidebar.js"></script>
</body>

</html>