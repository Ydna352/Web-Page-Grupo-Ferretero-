<?php require_once '../PHP/auth.php'; auth_require_rol('cliente'); ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal del Cliente</title>
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

        .modules-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        @media (max-width: 768px) {
            .modules-grid {
                grid-template-columns: 1fr;
            }
        }

        .mod-card {
            border: 2px solid;
            border-radius: 0.5rem;
            padding: 1.5rem;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            background: white;
        }

        .mod-card:hover {
            transform: scale(1.03);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, .1);
        }

        .mod-icon {
            padding: 0.75rem;
            border-radius: 0.5rem;
            width: fit-content;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .mod-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.375rem;
        }

        .mod-desc {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: auto;
        }

        .mod-hint {
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 1rem;
        }
    </style>
</head>

<body>
    <div class="root-layout">
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
                <div style="display:flex;align-items:center;gap:0.5rem;"><span style="font-size:1.25rem;">🏢</span>
                    <h1 style="font-weight:bold;color:#74cb3c;margin:0;font-size:1.125rem;">Portal Cliente</h1>
                </div>
                <div style="width:1.5rem;"></div>
            </header>
            <main>
                <div class="page-inner">
                    <h1 style="font-size:1.875rem;font-weight:bold;color:#74cb3c;">Portal del Cliente</h1>
                    <p style="color:#4b5563;margin-top:0.5rem;">Accede a tus servicios y consulta tu información</p>
                    <div class="modules-grid" id="modulesGrid"></div>
                </div>
            </main>
        </div>
    </div>
    <script>
        const NAV_ITEMS = [
            { path: 'Dashboard.php', label: 'Panel Principal', icon: '📊' },
            { path: 'company_info.php', label: 'Información Empresa', icon: '🏢' },
            { path: 'MyPurchases.php', label: 'Mis Compras', icon: '🛍️' },
            { path: 'InvoiceGeneration.php', label: 'Generar Factura', icon: '📄' },
            { path: 'Profile.php', label: 'Perfil', icon: '👤' },
            { path: 'catalogo.php', label: 'Catálogo', icon: '🛒' }
        ];
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

        const MODULES = [
            { title: 'Información de la Empresa', description: 'Consulta información general de la empresa', icon: '🏢', path: 'company_info.php', color: '#3e8cdd' },
            { title: 'Mis Compras', description: 'Acceso limitado a consulta de tus compras', icon: '🛒', path: 'MyPurchases.php', color: '#74cb3c' },
            { title: 'Generación de Facturas', description: 'Genera gf_facturas en línea y sube tu RFC', icon: '📄', path: 'InvoiceGeneration.php', color: '#3e8cdd' },
            { title: 'Catálogo', description: 'Explora herramientas y gf_materiales disponibles', icon: '🗂️', path: 'catalogo.php', color: '#74cb3c' }
        ];

        function renderModules() {
            const grid = document.getElementById('modulesGrid'); grid.innerHTML = '';
            MODULES.forEach(m => {
                const a = document.createElement('a'); a.href = m.path; a.className = 'mod-card'; a.style.borderColor = m.color;
                a.innerHTML = `<div class="mod-icon" style="background-color:${m.color};color:white;">${m.icon}</div>
        <div class="mod-title" style="color:${m.color};">${m.title}</div>
        <p class="mod-desc">${m.description}</p>
        <p class="mod-hint">Haz clic para acceder</p>`;
                grid.appendChild(a);
            });
        }

        document.addEventListener('DOMContentLoaded', () => { renderNav('desktopNav'); renderNav('mobileNav'); renderModules(); });
    </script>
  <script src="ClienteSidebar.js"></script>
</body>

</html>