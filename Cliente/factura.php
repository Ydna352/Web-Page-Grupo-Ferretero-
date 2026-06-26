<?php require_once '../PHP/auth.php'; auth_require_rol('cliente'); ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura – Portal Cliente</title>
    <link rel="apple-touch-icon" sizes="180x180" href="../Administrador/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../Administrador/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../Administrador/favicon-16x16.png">
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

        /* ── Layout ───────────────────────────────────────────────── */
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

        /* ── Cards ────────────────────────────────────────────────── */
        .page-inner {
            background: #fff;
            border-radius: 0.5rem;
            padding: 2rem;
            width: 100%;
        }

        .card {
            border: 2px solid;
            border-radius: 0.75rem;
            background: white;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .07);
            margin-bottom: 1.5rem;
        }

        .card--blue {
            border-color: #3e8cdd;
        }

        .card--green {
            border-color: #74cb3c;
        }

        .card-hdr {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
            font-size: 1.05rem;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .card-hdr.blue {
            color: #3e8cdd;
        }

        .card-hdr.green {
            color: #74cb3c;
        }

        .card-body {
            padding: 1.25rem 1.5rem;
        }

        /* ── Estado: cargando / error / ya facturado ─────────────── */
        .state-panel {
            text-align: center;
            padding: 3rem 2rem;
            display: none;
        }

        .state-panel.show {
            display: block;
        }

        .state-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .state-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .state-sub {
            color: #6b7280;
            margin-bottom: 1.5rem;
        }

        /* ── Vista previa de factura ──────────────────────────────── */
        #invoicePreview {
            display: none;
        }

        #invoicePreview.show {
            display: block;
        }

        /* ── Factura estilo carta ─────────────────────────────────── */
        .factura-wrap {
            width: 100%;
            max-width: 820px;
            margin: 0 auto;
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 0 24px rgba(0, 0, 0, .12);
            overflow: hidden;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Header */
        .fac-header {
            position: relative;
            height: 130px;
            overflow: hidden;
        }

        .fac-hero {
            position: absolute;
            inset: 0;
            background: linear-gradient(to right, #3e8cdd 68%, #74cb3c 32%);
            clip-path: polygon(0 0, 100% 0, 100% 45%, 0 100%);
        }

        .fac-header-content {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            padding: 20px 40px 0;
            gap: 12px;
        }

        .fac-header-content img {
            width: 54px;
            height: 54px;
        }

        .fac-header-content h2 {
            font-size: 2rem;
            color: white;
            line-height: 1;
        }

        .fac-slogan {
            font-size: 0.7rem;
            letter-spacing: 2px;
            color: white;
            margin-top: 4px;
            display: block;
        }

        /* Emisor */
        .fac-issuer {
            background: #fff;
            padding: 8px 40px 10px;
            border-bottom: 2px solid #3e8cdd;
            text-align: right;
        }

        .fac-issuer p {
            margin: 0;
            font-size: 0.82rem;
            line-height: 1.6;
            color: #333;
        }

        /* Cliente + meta */
        .fac-main {
            padding: 14px 40px 20px;
        }

        .fac-client-row {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #3e8cdd;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .fac-client-list {
            list-style: none;
            font-size: 0.88rem;
        }

        .fac-client-list li {
            margin-bottom: 2px;
        }

        .fac-meta {
            text-align: right;
            font-size: 0.88rem;
        }

        .fac-meta p {
            margin-bottom: 2px;
        }

        /* Tabla */
        .fac-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        .fac-table th {
            background: #3e8cdd;
            color: white;
            padding: 10px 12px;
            text-align: left;
            font-size: 0.85rem;
        }

        .fac-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #eee;
            font-size: 0.83rem;
        }

        .fac-table tbody tr:nth-child(even) td {
            background: #f8faff;
        }

        /* Totales */
        .fac-totals-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .fac-fiscal {
            max-width: 55%;
            font-size: 0.78rem;
            color: #555;
        }

        .fac-fiscal p {
            margin-bottom: 4px;
        }

        .fac-cfdi-aviso {
            color: red;
            font-weight: bold;
            font-size: 0.68rem;
            margin-top: 6px;
        }

        .fac-total-box {
            list-style: none;
            text-align: right;
            min-width: 200px;
        }

        .fac-total-box li {
            padding: 4px 0;
            border-bottom: 1px solid #eee;
            font-size: 0.88rem;
        }

        .fac-grand-total {
            font-size: 1.1rem;
            color: #3e8cdd;
            font-weight: bold;
            border-bottom: 2px solid #3e8cdd !important;
        }

        /* Firma */
        .fac-sig {
            text-align: center;
            padding: 16px 40px 8px;
            font-size: 0.78rem;
            color: #888;
        }

        .fac-sig hr {
            border: none;
            border-top: 1px solid #ccc;
            margin-bottom: 5px;
        }

        /* Footer */
        .fac-footer-bg {
            height: 90px;
            background: linear-gradient(to right, #3e8cdd 70%, #74cb3c 30%);
            clip-path: polygon(0 55%, 100% 0, 100% 100%, 0 100%);
        }

        /* ── Botones ──────────────────────────────────────────────── */
        .btn-row {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 1.5rem;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.65rem 1.4rem;
            border: none;
            border-radius: 0.375rem;
            background: #74cb3c;
            color: white;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            font-size: 0.95rem;
            transition: opacity .15s;
        }

        .btn-primary:hover {
            opacity: .88;
        }

        .btn-primary:disabled {
            opacity: .55;
            cursor: not-allowed;
        }

        .btn-outline {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.65rem 1.4rem;
            border: 1px solid #3e8cdd;
            border-radius: 0.375rem;
            color: #3e8cdd;
            background: white;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            font-size: 0.95rem;
            transition: background .15s;
        }

        .btn-outline:hover {
            background: #eff6ff;
        }

        /* ── Pantalla de éxito ────────────────────────────────────── */
        .success-screen {
            display: none;
            text-align: center;
            padding: 3rem 2rem;
        }

        .success-screen.show {
            display: block;
        }

        .success-icon {
            font-size: 5rem;
            margin-bottom: 1rem;
        }

        /* ── Banner ya facturado ──────────────────────────────────── */
        .alert-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            color: #1e40af;
            margin-bottom: 1rem;
        }

        /* ── CFDI select ──────────────────────────────────────────── */
        .field {
            display: flex;
            flex-direction: column;
            gap: 0.375rem;
            margin-top: 1rem;
        }

        .field label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
        }

        .field select {
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-family: inherit;
            font-size: 0.9rem;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
        }

        .field select:focus {
            border-color: #3e8cdd;
            box-shadow: 0 0 0 3px rgba(62, 140, 221, .12);
        }

        /* ── Spinner ─────────────────────────────────────────────── */
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, .3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <div class="root-layout">

        <!-- Sidebar Desktop -->
        <aside id="desktopSidebar">
            <div style="padding:1.5rem;border-bottom:1px solid #e5e7eb;background-color:#74cb3c;">
                <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.5rem;">
                    <span style="font-size:1.5rem;background:white;border-radius:0.5rem;padding:0.25rem;">🏢</span>
                    <h1 style="font-size:1.25rem;font-weight:bold;color:white;margin:0;">Portal Cliente</h1>
                </div>
                <p style="font-size:0.875rem;color:#dcfce7;margin:0;" id="sidebarClientName">Cliente</p>
            </div>
            <nav style="flex:1;padding:1rem;overflow-y:auto;" id="desktopNav"></nav>
            <div style="padding:1rem;border-top:1px solid #e5e7eb;margin-top:auto;">
                <button onclick="handleLogout()"
                    style="width:100%;display:flex;align-items:center;padding:0.5rem 1rem;color:#dc2626;border:1px solid #fca5a5;background:transparent;border-radius:0.375rem;cursor:pointer;">
                    <span style="margin-right:0.75rem;">🚪</span> Cerrar Sesión
                </button>
            </div>
        </aside>

        <!-- Mobile Menu Overlay -->
        <div id="mobileMenuOverlay"
            style="display:none;position:fixed;inset:0;z-index:50;background-color:rgba(0,0,0,0.5);">
            <aside style="width:16rem;height:100%;background-color:white;display:flex;flex-direction:column;">
                <div
                    style="padding:1.5rem;border-bottom:1px solid #e5e7eb;background-color:#74cb3c;display:flex;justify-content:space-between;align-items:center;">
                    <div style="display:flex;align-items:center;gap:0.5rem;">
                        <span style="font-size:1.25rem;background:white;border-radius:0.5rem;padding:0.25rem;">🏢</span>
                        <div>
                            <h1 style="font-size:1.125rem;font-weight:bold;color:white;margin:0;">Portal Cliente</h1>
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

        <!-- Main Area -->
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
                    <h1 style="font-size:1.875rem;font-weight:bold;color:#3e8cdd;">Vista de Factura</h1>
                    <p style="color:#4b5563;margin-top:0.375rem;">Previsualización y generación de tu factura
                        electrónica</p>

                    <!-- ── Estado: cargando ── -->
                    <div class="state-panel show" id="stateLoading">
                        <div class="state-icon">⏳</div>
                        <div class="state-title" style="color:#3e8cdd;">Cargando datos de la factura…</div>
                        <div class="state-sub">Por favor espera</div>
                    </div>

                    <!-- ── Estado: error ── -->
                    <div class="state-panel" id="stateError">
                        <div class="state-icon">❌</div>
                        <div class="state-title" style="color:#dc2626;" id="errorTitle">Error</div>
                        <div class="state-sub" id="errorMsg">No se pudieron cargar los datos.</div>
                        <div class="btn-row" style="justify-content:center;">
                            <button class="btn-outline" onclick="history.back()">← Regresar</button>
                            <button class="btn-outline" onclick="location.href='InvoiceGeneration.php'">Generar otra
                                factura</button>
                        </div>
                    </div>



                    <!-- ── Pantalla de éxito ── -->
                    <div class="success-screen" id="successScreen">
                        <div class="success-icon">✅</div>
                        <h2 style="font-size:2rem;font-weight:bold;color:#74cb3c;margin-bottom:0.75rem;">
                            Factura Generada Exitosamente
                        </h2>
                        <p style="color:#4b5563;margin-bottom:0.25rem;" id="successFolio"></p>
                        <p style="color:#4b5563;margin-bottom:0.25rem;" id="successTotal"></p>
                        <p style="color:#4b5563;margin-bottom:2rem;" id="successPdfInfo"></p>
                        <div class="btn-row" style="justify-content:center;">
                            <button class="btn-primary" id="btnAbrirPdf">📄 Abrir PDF</button>
                            <button class="btn-outline" onclick="location.href='InvoiceGeneration.php'">Generar otra
                                factura</button>
                            <button class="btn-outline" onclick="location.href='MyPurchases.php'">Mis Compras</button>
                        </div>
                        
                        <!-- Visualizador de Factura Automático -->
                        <div id="invoiceViewerContainer" style="display:none; text-align:left; margin-top: 2rem;">
                            <h3 style="font-size:1.1rem;font-weight:bold;color:#374151;margin-bottom:0.75rem;">Vista previa del PDF generado:</h3>
                            <iframe id="invoiceIframe" src="" width="100%" height="650px" style="border:1px solid #e5e7eb; border-radius:0.5rem;" title="Factura Generada"></iframe>
                        </div>
                    </div>

                    <!-- ── Vista previa de factura ── -->
                    <div id="invoicePreview">

                        <!-- Selector de uso CFDI -->
                        <div class="card card--green" style="margin-top:1.5rem;">
                            <div class="card-hdr green">
                                🧾 Configuración de Factura
                            </div>
                            <div class="card-body">
                                <div class="field">
                                    <label for="cfdiSelect">Uso CFDI *</label>
                                    <select id="cfdiSelect">
                                        <option value="G03">G03 — Gastos en general</option>
                                        <option value="G01">G01 — Adquisición de bienes</option>
                                        <option value="G02">G02 — Devoluciones / descuentos</option>
                                        <option value="I01">I01 — Construcciones</option>
                                        <option value="I02">I02 — Mobiliario de oficina</option>
                                        <option value="D04">D04 — Donativos</option>
                                        <option value="D07">D07 — Primas por seguros</option>
                                        <option value="D08">D08 — Gastos de transportación</option>
                                        <option value="P01">P01 — Por definir</option>
                                    </select>
                                </div>
                                <div class="btn-row">
                                    <button class="btn-primary" id="btnGenerar" onclick="generarFactura()">
                                        📄 Generar y Guardar PDF
                                    </button>
                                    <button class="btn-outline" onclick="history.back()">← Regresar</button>
                                </div>
                            </div>
                        </div>

                        <!-- Vista previa HTML de la factura -->
                        <div class="card card--blue" style="margin-top:1.5rem;">
                            <div class="card-hdr blue">
                                📋 Vista Previa de Factura
                            </div>
                            <div class="card-body" style="padding:1.5rem;">

                                <div class="factura-wrap" id="facturaHTML">
                                    <!-- Contenido inyectado por JS -->
                                </div>

                            </div>
                        </div>

                    </div><!-- /#invoicePreview -->

                </div>
            </main>
        </div>
    </div>

    <script>
        // ── Navegación ─────────────────────────────────────────────────────
        var NAV_ITEMS = [
            { path: 'Dashboard.php', label: 'Panel Principal', icon: '📊' },
            { path: 'company_info.php', label: 'Info Empresa', icon: '🏢' },
            { path: 'MyPurchases.php', label: 'Mis Compras', icon: '🛍️' },
            { path: 'InvoiceGeneration.php', label: 'Generar Factura', icon: '📄' },
            { path: 'Profile.php', label: 'Perfil', icon: '👤' },
            { path: 'catalogo.php', label: 'Catálogo', icon: '🛒' }
        ];

        var currentPage = location.pathname.split('/').pop() || 'Dashboard.php';

        function renderNav(id) {
            var c = document.getElementById(id);
            if (!c) return;
            c.innerHTML = '';
            NAV_ITEMS.forEach(function (item) {
                var a = document.createElement('a');
                a.href = item.path;
                a.className = 'nav-btn' + (currentPage === item.path ? ' active' : '');
                a.innerHTML = '<span style="margin-right:0.75rem;">' + item.icon + '</span>' + item.label;
                if (id === 'mobileNav') a.onclick = function () { toggleMobileMenu(false); };
                c.appendChild(a);
            });
        }

        function toggleMobileMenu(show) {
            document.getElementById('mobileMenuOverlay').style.display = show ? 'block' : 'none';
        }
        function handleLogout() { location.href = '../Home/Home.html'; }

        // ── Parámetros de URL ──────────────────────────────────────────────
        var params = new URLSearchParams(location.search);
        var idTicket = parseInt(params.get('id_ticket') || '0');
        var idCliente = parseInt(params.get('id_cliente') || localStorage.getItem('id_cliente') || '6021');

        // ── Estado global ──────────────────────────────────────────────────
        var facturaData = null;   // datos cargados de generar_factura.php

        // ── Formateo ───────────────────────────────────────────────────────
        function fmtMXN(n) {
            return '$' + parseFloat(n).toLocaleString('es-MX', {
                minimumFractionDigits: 2, maximumFractionDigits: 2
            });
        }
        function padFolio(n) { return String(n).padStart(6, '0'); }
        function fmtDate(iso) {
            var d = new Date(iso + 'T12:00:00');
            var meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
                'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
            return d.getDate() + ' de ' + meses[d.getMonth()] + ' de ' + d.getFullYear();
        }

        // ── Mostrar/ocultar paneles ────────────────────────────────────────
        function showPanel(id) {
            ['stateLoading', 'stateError', 'stateAlready', 'successScreen', 'invoicePreview']
                .forEach(function (p) {
                    var el = document.getElementById(p);
                    if (el) { el.classList.remove('show'); el.style.display = ''; }
                });
            var target = document.getElementById(id);
            if (!target) return;
            if (target.classList.contains('state-panel') || target.classList.contains('success-screen')) {
                target.classList.add('show');
            } else {
                target.style.display = 'block';
            }
        }

        // ── Renderizar factura HTML ────────────────────────────────────────
        function renderFacturaHTML(data) {
            var c = data.cliente;
            var t = data.ticket;
            var d = [c.calle_num, c.colonia, c.ciudad, c.estado]
                .filter(function (x) { return x && x.trim(); }).join(', ');
            if (c.cp) d += ' C.P. ' + c.cp;

            var filasHTML = '';
            data.items.forEach(function (it) {
                filasHTML += '<tr>' +
                    '<td>' + it.cantidad + '.00</td>' +
                    '<td>' + it.articulo + '</td>' +
                    '<td>' + fmtMXN(it.precio_unitario) + '</td>' +
                    '<td>' + fmtMXN(it.subtotal_item) + '</td>' +
                    '</tr>';
            });

            // UUID simulado
            var uuid = 'XXXX-' + Math.random().toString(36).substring(2, 6).toUpperCase() + '-' +
                Math.random().toString(36).substring(2, 6).toUpperCase() + '-' +
                Math.random().toString(36).substring(2, 10).toUpperCase();

            var html = '' +
                '<div class="fac-header">' +
                '<div class="fac-hero"></div>' +
                '<div class="fac-header-content">' +
                '<img src="../formato_facturas/Logo_blanco_GF.PNG" alt="Logo GF Piedras" onerror="this.style.display=\'none\'">' +
                '<div>' +
                '<h2>Piedras</h2>' +
                '<span class="fac-slogan">UNA SOLUCIÓN A TUS NECESIDADES</span>' +
                '</div>' +
                '</div>' +
                '</div>' +

                '<div class="fac-issuer">' +
                '<p><strong>GUADALUPE MONSERRAT MORA RAMÍREZ</strong></p>' +
                '<p>C. VENUSTIANO CARRANZA NTE. No. 6 SAN BARTOLOMÉ</p>' +
                '<p>SAN PABLO DEL MONTE TLAXCALA C.P. 90970 &nbsp;|&nbsp; RFC: MORG-821212-VD2</p>' +
                '</div>' +

                '<div class="fac-main">' +

                '<div class="fac-client-row">' +
                '<ul class="fac-client-list">' +
                '<li><strong>CLIENTE:</strong> ' + (c.nombre || '—') + '</li>' +
                '<li><strong>RFC:</strong> ' + (c.rfc || 'Sin RFC') + '</li>' +
                '<li><strong>CURP:</strong> ' + (c.curp || 'Sin CURP') + '</li>' +
                '<li><strong>RÉGIMEN:</strong> ' + (c.regimen || '—') + '</li>' +
                '<li><strong>DIRECCIÓN:</strong> ' + (d || '—') + '</li>' +
                '<li><strong>ATENDIÓ:</strong> ' + (t.trabajador || '—') + '</li>' +
                '</ul>' +
                '<div class="fac-meta">' +
                '<p><strong>FOLIO:</strong> (pendiente)</p>' +
                '<p><strong>FECHA:</strong> ' + fmtDate(t.fecha_iso) + '</p>' +
                '<p><strong>MÉTODO PAGO:</strong> PUE</p>' +
                '</div>' +
                '</div>' +

                '<table class="fac-table">' +
                '<thead><tr>' +
                '<th>CANT.</th><th>ARTÍCULO</th><th>P.U.</th><th>IMP.</th>' +
                '</tr></thead>' +
                '<tbody>' + filasHTML + '</tbody>' +
                '</table>' +

                '<div class="fac-totals-row">' +
                '<div class="fac-fiscal">' +
                '<p><strong>Folio Fiscal UUID:</strong> ' + uuid + '</p>' +
                '<p class="fac-cfdi-aviso">ESTE DOCUMENTO ES UNA REPRESENTACIÓN IMPRESA DE UN CFDI (SIMULACIÓN)</p>' +
                '</div>' +
                '<ul class="fac-total-box">' +
                '<li><span>SUBTOTAL:</span> <strong>' + fmtMXN(data.subtotal) + '</strong></li>' +
                '<li><span>IVA (16%):</span> <strong>' + fmtMXN(data.iva) + '</strong></li>' +
                '<li class="fac-grand-total"><span>TOTAL:</span> <strong>' + fmtMXN(data.total) + '</strong></li>' +
                '</ul>' +
                '</div>' +

                '</div>' +

                '<div class="fac-sig">' +
                '<hr>' +
                '<p>SELLO DIGITAL DEL CFDI (SIMULADO)</p>' +
                '</div>' +
                '<div class="fac-footer-bg"></div>';

            document.getElementById('facturaHTML').innerHTML = html;
        }

        // ── Cargar datos del ticket desde la API ──────────────────────────
        function cargarDatos() {
            if (!idTicket) {
                showPanel('stateError');
                document.getElementById('errorTitle').textContent = 'Ticket no especificado';
                document.getElementById('errorMsg').textContent = 'No se recibió el ID del ticket. Regresa a Mis Compras y selecciona un ticket.';
                return;
            }

            var url = '../PHP/generar_factura.php?id_ticket=' + idTicket + '&id_cliente=' + idCliente;

            fetch(url)
                .then(function (r) { return r.json(); })
                .then(function (data) {

                    if (data.error) {
                        showPanel('stateError');
                        document.getElementById('errorTitle').textContent = 'Error al cargar factura';
                        document.getElementById('errorMsg').textContent = data.error;
                        return;
                    }

                    if (data.ya_facturado) {
                        if (data.pdf) {
                            window.location.replace('../facturas_pdf/' + data.pdf);
                        } else {
                            showPanel('stateError');
                            document.getElementById('errorTitle').textContent = 'Factura sin PDF';
                            document.getElementById('errorMsg').textContent = 'El ticket está facturado pero no tiene archivo PDF asociado.';
                        }
                        return;
                    }

                    // Guardar datos y renderizar vista previa
                    facturaData = data;
                    renderFacturaHTML(data);
                    showPanel('invoicePreview');
                })
                .catch(function (err) {
                    showPanel('stateError');
                    document.getElementById('errorTitle').textContent = 'Error de conexión';
                    document.getElementById('errorMsg').textContent = 'No se pudo conectar con el servidor. Verifica que EasyPHP esté activo.';
                    console.error(err);
                });
        }

        // ── Generar y guardar PDF ──────────────────────────────────────────
        function generarFactura() {
            if (!facturaData) return;

            var btnGenerar = document.getElementById('btnGenerar');
            var usoCfdi = document.getElementById('cfdiSelect').value;

            btnGenerar.disabled = true;
            btnGenerar.innerHTML = '<span class="spinner"></span>&nbsp; Generando PDF…';

            fetch('../PHP/guardar_factura.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id_ticket: idTicket,
                    id_cliente: idCliente,
                    uso_cfdi: usoCfdi
                })
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    btnGenerar.disabled = false;
                    btnGenerar.innerHTML = '📄 Generar y Guardar PDF';

                    if (data.error) {
                        alert('❌ Error: ' + data.error);
                        return;
                    }

                    // Mostrar pantalla de éxito
                    var folio = 'FAC-' + padFolio(data.folio);
                    document.getElementById('successFolio').textContent = 'Folio: ' + folio;
                    document.getElementById('successTotal').textContent = 'Total: ' + fmtMXN(data.total);
                    document.getElementById('successPdfInfo').textContent =
                        data.pdf ? 'Archivo: ' + data.pdf : '';

                    var btnPdf = document.getElementById('btnAbrirPdf');
                    if (data.pdf) {
                        btnPdf.style.display = 'inline-flex';
                        var pdfUrl = '../facturas_pdf/' + data.pdf;
                        btnPdf.onclick = function () {
                            window.open(pdfUrl, '_blank');
                        };
                        
                        // Embeber PDF automáticamente
                        document.getElementById('invoiceIframe').src = pdfUrl;
                        document.getElementById('invoiceViewerContainer').style.display = 'block';
                    } else {
                        btnPdf.style.display = 'none';
                    }

                    showPanel('successScreen');
                    document.getElementById('successScreen').classList.add('show');
                })
                .catch(function (err) {
                    btnGenerar.disabled = false;
                    btnGenerar.innerHTML = '📄 Generar y Guardar PDF';
                    alert('❌ Error de red. Verifica tu conexión e intenta nuevamente.');
                    console.error(err);
                });
        }


        // ── Init ──────────────────────────────────────────────────────────
        document.addEventListener('DOMContentLoaded', function () {
            renderNav('desktopNav');
            renderNav('mobileNav');

            // Cargar nombre del cliente en sidebar
            fetch('../PHP/api_cliente.php?accion=perfil')
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    if (d.nombre) {
                        var el = document.getElementById('sidebarClientName');
                        if (el) el.textContent = d.nombre;
                    }
                })
                .catch(function () { });

            cargarDatos();
        });
    </script>

    <script src="ClienteSidebar.js"></script>
</body>

</html>