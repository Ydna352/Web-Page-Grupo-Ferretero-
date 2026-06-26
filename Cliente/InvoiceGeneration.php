<?php require_once '../PHP/auth.php'; auth_require_rol('cliente'); ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generación de Facturas – Portal Cliente</title>
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

        .form-sections {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .card {
            border: 2px solid;
            border-radius: 0.75rem;
            background: white;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .07);
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
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .card-hdr svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
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

        .fields2 {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 0.375rem;
        }

        .field.full {
            grid-column: 1 / -1;
        }

        .field label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
        }

        .field input,
        .field textarea,
        .field select {
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-family: inherit;
            font-size: 0.9rem;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
            width: 100%;
        }

        .field input:focus,
        .field textarea:focus,
        .field select:focus {
            border-color: #3e8cdd;
            box-shadow: 0 0 0 3px rgba(62, 140, 221, .12);
        }

        .field textarea {
            resize: vertical;
            min-height: 80px;
        }

        .field input[type="file"] {
            padding: 0.4rem;
        }

        .field input.invalid {
            border-color: #dc2626 !important;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, .12) !important;
        }

        .field-error {
            font-size: 0.78rem;
            color: #dc2626;
            margin-top: 0.1rem;
            display: none;
            line-height: 1.4;
        }

        .field-error.show {
            display: block;
        }

        .file-ok {
            font-size: 0.85rem;
            color: #74cb3c;
            margin-top: 0.375rem;
        }

        .btn-row {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.25rem;
            border: none;
            border-radius: 0.375rem;
            background: #74cb3c;
            color: white;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: opacity .15s;
        }

        .btn-primary:hover {
            opacity: .88;
        }

        .btn-outline {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.25rem;
            border: 1px solid #3e8cdd;
            border-radius: 0.375rem;
            color: #3e8cdd;
            background: white;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: background .15s;
        }

        .btn-outline:hover {
            background: #eff6ff;
        }

        .btn-primary svg,
        .btn-outline svg {
            width: 18px;
            height: 18px;
        }

        .success-screen {
            display: none;
            text-align: center;
            padding: 3rem 2rem;
        }

        .success-screen.show {
            display: block;
        }

        .form-area.hide {
            display: none;
        }

        .success-icon {
            font-size: 5rem;
            margin-bottom: 1rem;
        }

        .profile-banner {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.65rem 1rem;
            background: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
            font-size: 0.85rem;
            color: #166534;
        }

        .profile-banner svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        .profile-banner .banner-actions {
            margin-left: auto;
            display: flex;
            gap: 0.5rem;
        }

        .btn-restore {
            padding: 0.3rem 0.7rem;
            border: 1px solid #86efac;
            background: white;
            border-radius: 0.25rem;
            font-size: 0.78rem;
            color: #166534;
            cursor: pointer;
            font-family: inherit;
            font-weight: 500;
            white-space: nowrap;
            transition: background .15s;
        }

        .btn-restore:hover {
            background: #dcfce7;
        }

        .ticket-select {
            width: 100%;
            padding: 0.6rem 0.75rem;
            border: 2px solid #74cb3c;
            border-radius: 0.375rem;
            font-family: inherit;
            font-size: 0.9rem;
            outline: none;
            background: white;
            cursor: pointer;
            transition: border-color .15s, box-shadow .15s;
            color: #374151;
        }

        .ticket-select:focus {
            border-color: #74cb3c;
            box-shadow: 0 0 0 3px rgba(116, 203, 60, .15);
        }

        .ticket-select option {
            padding: 0.5rem;
        }

        .ticket-preview {
            display: none;
            margin-top: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            overflow: hidden;
            animation: fadeSlideIn .3s ease;
        }

        @keyframes fadeSlideIn {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .ticket-preview.show {
            display: block;
        }

        .ticket-preview-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
        }

        .ticket-preview-header .tp-id {
            font-weight: 700;
            color: #3e8cdd;
            font-size: 1rem;
        }

        .ticket-preview-header .tp-date {
            font-size: 0.8rem;
            color: #6b7280;
        }

        .ticket-preview-body {
            padding: 1rem 1.25rem;
        }

        .tp-product {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .tp-product:last-child {
            border-bottom: none;
        }

        .tp-product-icon {
            width: 32px;
            height: 32px;
            background: #eff6ff;
            border-radius: 0.375rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #3e8cdd;
        }

        .tp-product-icon svg {
            width: 18px;
            height: 18px;
        }

        .tp-product-info {
            flex: 1;
            min-width: 0;
        }

        .tp-product-name {
            font-size: 0.85rem;
            font-weight: 600;
            color: #1f2937;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .tp-product-qty {
            font-size: 0.75rem;
            color: #6b7280;
        }

        .tp-product-subtotal {
            font-size: 0.85rem;
            font-weight: 600;
            color: #74cb3c;
            white-space: nowrap;
        }

        .ticket-preview-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            background: #f0fdf4;
            border-top: 2px solid #74cb3c;
        }

        .ticket-preview-footer .tp-total-label {
            font-weight: 700;
            color: #374151;
            font-size: 0.95rem;
        }

        .ticket-preview-footer .tp-total-val {
            font-weight: 700;
            color: #74cb3c;
            font-size: 1.25rem;
        }

        .no-profile-banner {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.65rem 1rem;
            background: #fef3c7;
            border: 1px solid #fcd34d;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
            font-size: 0.85rem;
            color: #92400e;
        }

        .no-profile-banner svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        .no-profile-banner a {
            color: #92400e;
            font-weight: 600;
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

        <!-- Mobile Menu Overlay -->
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

                    <!-- Success screen -->
                    <div class="success-screen" id="successScreen">
                        <div class="success-icon">✅</div>
                        <h2 style="font-size:2rem;font-weight:bold;color:#74cb3c;margin-bottom:0.75rem;">
                            Factura Generada Exitosamente
                        </h2>
                        <p style="color:#4b5563;margin-bottom:0.25rem;" id="successFolio"></p>
                        <p style="color:#4b5563;margin-bottom:0.25rem;" id="successTicket"></p>
                        <p style="color:#4b5563;margin-bottom:0.25rem;" id="successTotal"></p>
                        <p style="color:#4b5563;margin-bottom:2rem;" id="successEmail"></p>
                        <div class="btn-row" style="justify-content:center; margin-bottom: 2rem;">
                            <button class="btn-primary" onclick="resetForm()">Generar Nueva Factura</button>
                            <button class="btn-outline" id="btnDescargarPdf" onclick="descargarPdf()"
                                style="display:none;">📄 Descargar PDF</button>
                        </div>

                        <!-- Visualizador de Factura Automático -->
                        <div id="invoiceViewerContainer" style="display:none; text-align:left;">
                            <h3 style="font-size:1.1rem;font-weight:bold;color:#374151;margin-bottom:0.75rem;">Vista
                                previa de la factura:</h3>
                            <iframe id="invoiceIframe" src="" width="100%" height="600px"
                                style="border:1px solid #e5e7eb; border-radius:0.5rem;"
                                title="Factura Generada"></iframe>
                        </div>
                    </div>

                    <!-- Form area -->
                    <div class="form-area" id="formArea">
                        <h1 style="font-size:1.875rem;font-weight:bold;color:#3e8cdd;">Generación de Facturas</h1>
                        <p style="color:#4b5563;margin-top:0.375rem;">
                            Completa los campos para generar tu factura electrónica
                        </p>

                        <form id="invoiceForm" class="form-sections" onsubmit="handleSubmit(event)">

                            <div class="card card--blue">
                                <div class="card-hdr blue">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                    Datos Fiscales
                                </div>
                                <div class="card-body">
                                    <div class="profile-banner" id="profileBanner" style="display:none;">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>Datos cargados automáticamente desde tu perfil</span>
                                        <div class="banner-actions">
                                            <button type="button" class="btn-restore" onclick="restoreProfileData()">
                                                ↺ Restaurar datos del perfil
                                            </button>
                                        </div>
                                    </div>

                                    <div class="no-profile-banner" id="noProfileBanner" style="display:none;">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                        </svg>
                                        <span>No se encontraron datos en tu perfil. <a href="Profile.php">Ir a Mi
                                                Perfil</a> para completarlos.</span>
                                    </div>

                                    <div class="fields2">
                                        <div class="field">
                                            <label>RFC *</label>
                                            <input type="text" name="rfc" id="rfcInput" placeholder="XAXX010101000"
                                                required maxlength="13" oninput="validateRFC(this)" />
                                            <span class="field-error" id="rfcError"></span>
                                        </div>

                                        <div class="field">
                                            <label>Razón Social *</label>
                                            <input type="text" name="razonSocial" id="razonSocialInput"
                                                placeholder="Nombre o Razón Social" required />
                                        </div>

                                        <div class="field full">
                                            <label>Correo Electrónico *</label>
                                            <input type="email" name="email" id="emailInput"
                                                placeholder="correo@ejemplo.com" required />
                                        </div>

                                        <div class="field full">
                                            <label>Dirección Fiscal *</label>
                                            <input type="text" name="direccion" id="direccionInput"
                                                placeholder="Calle, Número, Colonia, Ciudad, Estado" required />
                                        </div>

                                        <div class="field">
                                            <label>Código Postal *</label>
                                            <input type="text" name="codigoPostal" id="cpInput" placeholder="00000"
                                                required maxlength="5" oninput="validateCP(this)" />
                                            <span class="field-error" id="cpError"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card card--green">
                                <div class="card-hdr green">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                                    </svg>
                                    Seleccionar Ticket de Compra
                                </div>
                                <div class="card-body">
                                    <div class="fields2" style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px dashed #e5e7eb;">
                                        <div class="field">
                                            <label>Desde (opcional)</label>
                                            <input type="date" id="filtroFechaInicio" onchange="cargarTickets()">
                                        </div>
                                        <div class="field">
                                            <label>Hasta (opcional)</label>
                                            <input type="date" id="filtroFechaFin" onchange="cargarTickets()">
                                        </div>
                                        <div class="field" style="justify-content: flex-end;">
                                            <button type="button" class="btn-outline" id="btnLimpiarTicket" onclick="limpiarFiltrosTicket()" style="padding: 0.5rem 1rem; font-size: 0.85rem;">Limpiar filtros</button>
                                        </div>
                                    </div>

                                    <div class="field">
                                        <label>Ticket de compra a facturar *</label>
                                        <select class="ticket-select" id="ticketSelect"
                                            onchange="handleTicketSelect(this.value)" required>
                                            <option value="">— Selecciona un ticket de compra —</option>
                                        </select>
                                    </div>

                                    <div class="ticket-preview" id="ticketPreview">
                                        <div class="ticket-preview-header">
                                            <span class="tp-id" id="tpId"></span>
                                            <span class="tp-date" id="tpDate"></span>
                                        </div>
                                        <div class="ticket-preview-body" id="tpProducts"></div>
                                        <div class="ticket-preview-footer">
                                            <span class="tp-total-label">Total del Ticket:</span>
                                            <span class="tp-total-val" id="tpTotal"></span>
                                        </div>
                                    </div>

                                    <div class="field" style="margin-top:1rem;">
                                        <label>Uso CFDI *</label>
                                        <select id="cfdiSelect" name="uso_cfdi" required>
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
                                </div>
                            </div>

                            <div style="display:flex;align-items:center;gap:0.6rem;padding:0.75rem 1rem;
                                        background:#eff6ff;border:1px solid #bfdbfe;border-radius:0.5rem;
                                        font-size:0.875rem;color:#1e40af;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" style="width:18px;height:18px;flex-shrink:0;">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                </svg>
                                <span>Para adjuntar tu <strong>Constancia de Situación Fiscal</strong>,
                                    visita <a href="Profile.php" style="color:#1e40af;font-weight:600;">Mi Perfil</a>.
                                </span>
                            </div>

                            <div class="btn-row">
                                <button type="submit" class="btn-primary" id="btnSubmit">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                    Generar Factura
                                </button>
                                <button type="button" class="btn-outline" onclick="clearForm()">Limpiar
                                    Formulario</button>
                            </div>

                        </form>
                    </div><!-- /form-area -->

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
            const c = document.getElementById(id);
            if (!c) return;
            c.innerHTML = '';
            NAV_ITEMS.forEach(item => {
                const a = document.createElement('a');
                a.href = item.path;
                a.className = 'nav-btn' + (currentPage === item.path ? ' active' : '');
                a.innerHTML = '<span style="margin-right:0.75rem;">' + item.icon + '</span>' + item.label;
                if (id === 'mobileNav') a.onclick = () => toggleMobileMenu(false);
                c.appendChild(a);
            });
        }

        function toggleMobileMenu(show) {
            document.getElementById('mobileMenuOverlay').style.display = show ? 'block' : 'none';
        }

        function handleLogout() { location.href = '../Home/Home.html'; }

        let apiTickets = [];
        let apiMaterials = {};

        function getMaterial(id) { return apiMaterials[id] || null; }

        function fmtDate(d) {
            return new Date(d + 'T12:00:00').toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' });
        }

        function fmtDateLong(d) {
            return new Date(d + 'T12:00:00').toLocaleDateString('es-MX', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        }

        function fmtMXN(n) {
            return '$' + n.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function padId(id) { return '#' + id.toString().padStart(4, '0'); }

        let profileData = null;

        function fillFiscalFields(data) {
            if (!data) return;
            document.getElementById('rfcInput').value = data.rfc || '';
            document.getElementById('razonSocialInput').value = data.nombre || '';
            document.getElementById('emailInput').value = data.correo_electronico || '';
            const partes = [data.calle_num, data.colonia, data.ciudad, data.estado].filter(Boolean);
            document.getElementById('direccionInput').value = partes.join(', ');
            document.getElementById('cpInput').value = data.cp || '';
        }

        function restoreProfileData() {
            if (profileData) fillFiscalFields(profileData);
        }

        function loadProfileData() {
            return fetch('../PHP/api_cliente.php?accion=perfil')
                .then(r => r.json())
                .then(data => {
                    if (data.error) return false;
                    profileData = data;
                    return true;
                })
                .catch(() => false);
        }

        function populateTicketSelector(tickets) {
            const select = document.getElementById('ticketSelect');
            tickets.forEach(ticket => {
                const detalles = ticket._detalles || [];
                const total = detalles.reduce((s, d) => s + parseInt(d.cantidad) * parseFloat(d.precio_venta), 0);
                const totalItems = detalles.reduce((s, d) => s + parseInt(d.cantidad), 0);
                const numArticulos = detalles.length;
                const opt = document.createElement('option');
                opt.value = ticket.id;
                const preview = detalles.slice(0, 2).map(d => {
                    const mat = getMaterial(parseInt(d.id_materiales));
                    return mat ? mat.nombre : 'Material';
                });
                const previewText = preview.join(', ') + (numArticulos > 2 ? '...' : '');
                opt.textContent = `Ticket ${padId(parseInt(ticket.id))} | ${fmtDate(ticket.fecha)} | ${totalItems} art. | ${fmtMXN(total)} — ${previewText}`;
                select.appendChild(opt);
            });
        }

        const iconPackageSm = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>`;

        function handleTicketSelect(ticketId) {
            const preview = document.getElementById('ticketPreview');
            if (!ticketId) { preview.classList.remove('show'); return; }
            const ticket = apiTickets.find(t => parseInt(t.id) === parseInt(ticketId));
            if (!ticket) return;
            const detalles = ticket._detalles || [];
            const total = detalles.reduce((s, d) => s + parseInt(d.cantidad) * parseFloat(d.precio_venta), 0);
            document.getElementById('tpId').textContent = 'Ticket ' + padId(parseInt(ticket.id));
            document.getElementById('tpDate').textContent = fmtDateLong(ticket.fecha);
            const productsHTML = detalles.map(d => {
                const mat = getMaterial(parseInt(d.id_materiales));
                const name = mat ? mat.nombre : 'Material no encontrado';
                const subtotal = parseInt(d.cantidad) * parseFloat(d.precio_venta);
                return `<div class="tp-product">
                    <div class="tp-product-icon">${iconPackageSm}</div>
                    <div class="tp-product-info">
                        <p class="tp-product-name">${name}</p>
                        <p class="tp-product-qty">${d.cantidad} × ${fmtMXN(parseFloat(d.precio_venta))}</p>
                    </div>
                    <span class="tp-product-subtotal">${fmtMXN(subtotal)}</span>
                </div>`;
            }).join('');
            document.getElementById('tpProducts').innerHTML = productsHTML;
            document.getElementById('tpTotal').textContent = fmtMXN(total);
            preview.classList.add('show');
        }

        function validateRFC(input) {
            const rfcFisica = /^[A-ZÑ&]{4}\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])[A-Z0-9]{3}$/i;
            const rfcMoral = /^[A-ZÑ&]{3}\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])[A-Z0-9]{3}$/i;
            input.value = input.value.toUpperCase().replace(/[^A-ZÑ&0-9]/g, '');
            const val = input.value;
            const errorEl = document.getElementById('rfcError');
            if (val === '') { input.classList.remove('invalid'); errorEl.classList.remove('show'); return true; }
            const valid = rfcFisica.test(val) || rfcMoral.test(val);
            if (!valid) {
                input.classList.add('invalid'); errorEl.classList.add('show');
                if (val.length < 12) {
                    errorEl.textContent = 'RFC incompleto. Persona Física: 13 caracteres · Persona Moral: 12 caracteres.';
                } else {
                    const letras = val.length === 12 ? 3 : 4;
                    const fecha = val.slice(letras, letras + 6);
                    const homo = val.slice(letras + 6);
                    const fechaOk = /^\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])$/.test(fecha);
                    const homoOk = /^[A-Z0-9]{3}$/.test(homo);
                    if (!fechaOk) errorEl.textContent = 'La fecha debe ser válida en formato AAMMDD.';
                    else if (!homoOk) errorEl.textContent = 'Homoclave inválida. Deben ser 3 caracteres alfanuméricos.';
                    else errorEl.textContent = 'RFC inválido.';
                }
            } else {
                input.classList.remove('invalid'); errorEl.classList.remove('show');
            }
            return valid;
        }

        function validateCP(input) {
            input.value = input.value.replace(/\D/g, '');
            const val = input.value;
            const errorEl = document.getElementById('cpError');
            if (val === '') { input.classList.remove('invalid'); errorEl.classList.remove('show'); return true; }
            const valid = /^\d{5}$/.test(val) && val !== '00000' && parseInt(val) >= 1000;
            if (!valid) {
                input.classList.add('invalid'); errorEl.classList.add('show');
                if (val.length < 5) errorEl.textContent = 'El código postal debe tener exactamente 5 dígitos.';
                else if (val === '00000' || parseInt(val) < 1000) errorEl.textContent = 'Código postal inválido. Rango válido: 01000 – 99999.';
                else errorEl.textContent = 'Código postal inválido.';
            } else {
                input.classList.remove('invalid'); errorEl.classList.remove('show');
            }
            return valid;
        }

        var ultimoPdfUrl = null;

        function descargarPdf() {
            if (ultimoPdfUrl) window.open(ultimoPdfUrl, '_blank');
        }

        // SVG del botón (reutilizable)
        const btnSvg = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>`;

        function handleSubmit(e) {
            e.preventDefault();

            const rfcInput = document.getElementById('rfcInput');
            const cpInput = document.getElementById('cpInput');
            const ticketSelect = document.getElementById('ticketSelect');
            const btnSubmit = document.getElementById('btnSubmit');

            if (!validateRFC(rfcInput)) { rfcInput.focus(); return; }
            if (!validateCP(cpInput)) { cpInput.focus(); return; }

            if (!ticketSelect.value) {
                ticketSelect.focus();
                alert('Por favor selecciona un ticket de compra para facturar.');
                return;
            }

            const capturedEmail = document.getElementById('emailInput').value;
            const selectedTicketId = parseInt(ticketSelect.value);
            const usoCfdi = document.getElementById('cfdiSelect').value;

            const idCliente = parseInt(localStorage.getItem('id_cliente') || '0');
            if (!idCliente) {
                alert('No se pudo identificar al cliente. Inicia sesión nuevamente.');
                return;
            }

            btnSubmit.disabled = true;
            btnSubmit.textContent = 'Generando factura…';

            // ── POST a guardar_factura.php ────────────────────────────────────
            fetch('../PHP/guardar_factura.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id_ticket: selectedTicketId,
                    id_cliente: idCliente,
                    uso_cfdi: usoCfdi
                })
            })
                // ── CORRECCIÓN: detectar respuesta no-JSON antes de parsear ──────
                .then(r => {
                    if (!r.ok) {
                        return r.text().then(text => {
                            throw new Error('Servidor respondió ' + r.status + ': ' + text.substring(0, 300));
                        });
                    }
                    return r.json();
                })
                .then(data => {
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = btnSvg + ' Generar Factura';

                    if (data.error) {
                        if (data.estado === 'ya_facturado') {
                            alert('Este ticket ya ha sido facturado.');
                            btnSubmit.style.display = 'none';
                            
                            const btnVerFactura = document.createElement('a');
                            btnVerFactura.href = '../facturas_pdf/' + (data.pdf || 'factura_' + data.folio + '.pdf');
                            btnVerFactura.target = '_blank';
                            btnVerFactura.className = 'btn-outline';
                            btnVerFactura.innerHTML = '📄 Ver Factura Generada';
                            btnVerFactura.id = 'btnVerFacturaGenerada';
                            
                            // Remover anterior si existía
                            const oldBtn = document.getElementById('btnVerFacturaGenerada');
                            if (oldBtn) oldBtn.remove();
                            
                            btnSubmit.parentNode.insertBefore(btnVerFactura, btnSubmit.nextSibling);
                        } else {
                            alert('Error: ' + data.error);
                        }
                        return;
                    }

                    // ── Pantalla de éxito ──────────────────────────────────────
                    const folio = 'FAC-' + data.folio.toString().padStart(6, '0');
                    document.getElementById('successFolio').textContent = 'Folio: ' + folio;
                    document.getElementById('successTicket').textContent = 'Ticket facturado: #' + selectedTicketId;
                    document.getElementById('successEmail').textContent = 'Factura registrada para: ' + capturedEmail;

                    if (data.total) {
                        document.getElementById('successTotal').textContent =
                            'Total: $' + parseFloat(data.total).toLocaleString('es-MX', { minimumFractionDigits: 2 });
                    }

                    if (data.pdf) {
                        ultimoPdfUrl = '../facturas_pdf/' + data.pdf;
                        const btnPdf = document.getElementById('btnDescargarPdf');
                        btnPdf.style.display = 'inline-flex';
                        btnPdf.textContent = data.aviso ? '📄 Ver Documento (HTML)' : '📄 Descargar PDF';

                        // Mostrar vista previa en iframe
                        document.getElementById('invoiceIframe').src = ultimoPdfUrl;
                        document.getElementById('invoiceViewerContainer').style.display = 'block';
                    }

                    document.getElementById('formArea').classList.add('hide');
                    document.getElementById('successScreen').classList.add('show');
                })
                // ── CORRECCIÓN: mostrar error real en lugar de mensaje genérico ──
                .catch(err => {
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = btnSvg + ' Generar Factura';
                    console.error('Error detallado:', err);
                    alert('Error: ' + err.message);
                });
        }

        function clearForm() {
            document.getElementById('invoiceForm').reset();
            document.getElementById('ticketPreview').classList.remove('show');
            ['rfcInput', 'cpInput'].forEach(id => document.getElementById(id).classList.remove('invalid'));
            ['rfcError', 'cpError'].forEach(id => document.getElementById(id).classList.remove('show'));
            
            const btnSubmit = document.getElementById('btnSubmit');
            if (btnSubmit) btnSubmit.style.display = 'inline-flex';
            const btnVerFactura = document.getElementById('btnVerFacturaGenerada');
            if (btnVerFactura) btnVerFactura.remove();
        }

        function resetForm() {
            clearForm();
            document.getElementById('successScreen').classList.remove('show');
            document.getElementById('formArea').classList.remove('hide');
            document.getElementById('invoiceViewerContainer').style.display = 'none';
            document.getElementById('invoiceIframe').src = '';
            loadProfileData().then(ok => { if (ok) fillFiscalFields(profileData); });
        }

        function cargarTickets() {
            const fi = document.getElementById('filtroFechaInicio').value;
            const ff = document.getElementById('filtroFechaFin').value;
            let url = '../PHP/api_cliente.php?accion=compras&sin_facturar=1';
            if (fi && ff) {
                url += '&fecha_inicio=' + fi + '&fecha_fin=' + ff;
            }
            
            return fetch(url)
                .then(r => r.json())
                .then(comprasData => {
                    if (!comprasData.error) {
                        (comprasData.materiales || []).forEach(m => {
                            apiMaterials[parseInt(m.id)] = m;
                        });

                        const detallesMap = {};
                        (comprasData.detalles || []).forEach(d => {
                            const vid = parseInt(d.id_ventas);
                            if (!detallesMap[vid]) detallesMap[vid] = [];
                            detallesMap[vid].push(d);
                        });

                        apiTickets = (comprasData.tickets || []).map(t => ({
                            ...t,
                            _detalles: detallesMap[parseInt(t.id)] || []
                        }));

                        const select = document.getElementById('ticketSelect');
                        select.innerHTML = '<option value="">— Selecciona un ticket de compra —</option>';
                        document.getElementById('ticketPreview').classList.remove('show');
                        
                        populateTicketSelector(apiTickets);
                    }
                })
                .catch(err => console.error('Error al inicializar facturación:', err));
        }

        function limpiarFiltrosTicket() {
            document.getElementById('filtroFechaInicio').value = '';
            document.getElementById('filtroFechaFin').value = '';
            document.getElementById('ticketSelect').value = '';
            document.getElementById('ticketPreview').classList.remove('show');
            cargarTickets();
        }

        document.addEventListener('DOMContentLoaded', () => {
            renderNav('desktopNav');
            renderNav('mobileNav');

            Promise.all([
                loadProfileData(),
                cargarTickets()
            ]).then(([hasProfile, _]) => {
                if (hasProfile) {
                    fillFiscalFields(profileData);
                    document.getElementById('profileBanner').style.display = 'flex';
                    document.getElementById('noProfileBanner').style.display = 'none';
                } else {
                    document.getElementById('profileBanner').style.display = 'none';
                    document.getElementById('noProfileBanner').style.display = 'flex';
                }
            }).catch(err => console.error('Error al inicializar:', err));
        });
    </script>
    <script src="ClienteSidebar.js"></script>
</body>

</html>