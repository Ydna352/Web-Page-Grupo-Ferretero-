<?php require_once '../PHP/auth.php'; auth_require_rol('cliente'); ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil – Portal del Cliente</title>
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

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-header h1 {
            font-size: 1.875rem;
            font-weight: bold;
            color: #3e8cdd;
        }

        .page-header p {
            color: #4b5563;
            margin-top: 0.375rem;
        }

        .btn-edit {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background-color: #74cb3c;
            color: white;
            padding: 0.5rem 1.125rem;
            border: none;
            border-radius: 0.375rem;
            font-weight: 500;
            cursor: pointer;
            font-family: inherit;
            font-size: 0.9rem;
            transition: opacity .15s;
        }

        .btn-edit:hover {
            opacity: .88;
        }

        .card {
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            background-color: white;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .08);
            overflow: hidden;
        }

        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #3e8cdd;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .card-header svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        .card-body {
            padding: 1.5rem;
        }

        .fields-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1.25rem;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .field label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
        }

        .field input {
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-family: inherit;
            font-size: 0.9rem;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
        }

        .field input:focus {
            border-color: #3e8cdd;
            box-shadow: 0 0 0 3px rgba(62, 140, 221, .12);
        }

        .field input:disabled {
            background-color: #f3f4f6;
            color: #6b7280;
            cursor: default;
        }

        /* ── Validación ── */
        .field input.invalid {
            border-color: #dc2626 !important;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, .12) !important;
        }

        .field input.valid {
            border-color: #74cb3c !important;
        }

        .field-error {
            font-size: 0.75rem;
            color: #dc2626;
            display: none;
            line-height: 1.4;
        }

        .field-error.show {
            display: block;
        }

        .field .hint {
            font-size: 0.75rem;
            color: #6b7280;
        }

        .edit-actions {
            display: none;
            justify-content: flex-end;
            gap: 0.75rem;
            padding-top: 1.25rem;
            margin-top: 1.25rem;
            border-top: 1px solid #e5e7eb;
        }

        .btn-cancel {
            padding: 0.5rem 1rem;
            border: 1px solid #d1d5db;
            background-color: white;
            border-radius: 0.375rem;
            font-weight: 500;
            cursor: pointer;
            font-family: inherit;
            transition: background .15s;
        }

        .btn-cancel:hover {
            background-color: #f9fafb;
        }

        .btn-save {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1.125rem;
            border: none;
            background-color: #3e8cdd;
            color: white;
            border-radius: 0.375rem;
            font-weight: 500;
            cursor: pointer;
            font-family: inherit;
            transition: opacity .15s;
        }

        .btn-save:hover {
            opacity: .88;
        }

        .btn-save svg {
            width: 16px;
            height: 16px;
        }

        .info-note {
            margin-top: 1.5rem;
            padding: 1rem;
            background-color: #eff6ff;
            border-radius: 0.5rem;
            border: 1px solid #bfdbfe;
            font-size: 0.875rem;
            color: #1e40af;
        }

        /* ── Card Constancia Fiscal ── */
        .constancia-card {
            margin-top: 1.5rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            background-color: white;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .08);
            overflow: hidden;
        }

        .constancia-status {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }

        .constancia-status.ok {
            background: #f0fdf4;
            border: 1px solid #86efac;
            color: #166534;
        }

        .constancia-status.none {
            background: #fefce8;
            border: 1px solid #fde047;
            color: #713f12;
        }

        .constancia-upload-area {
            display: flex;
            align-items: flex-end;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .constancia-file-field {
            flex: 1;
            min-width: 220px;
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }

        .constancia-file-field label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
        }

        .constancia-file-field input[type="file"] {
            padding: 0.4rem 0.6rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-family: inherit;
            font-size: 0.85rem;
            cursor: pointer;
            background: white;
        }

        .btn-subir {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.55rem 1.1rem;
            border: none;
            background-color: #74cb3c;
            color: white;
            border-radius: 0.375rem;
            font-weight: 600;
            font-family: inherit;
            font-size: 0.875rem;
            cursor: pointer;
            transition: opacity .15s;
            white-space: nowrap;
        }

        .btn-subir:hover {
            opacity: .88;
        }

        .btn-subir:disabled {
            opacity: .55;
            cursor: not-allowed;
        }

        .constancia-msg {
            margin-top: 0.75rem;
            padding: 0.6rem 0.9rem;
            border-radius: 0.375rem;
            font-size: 0.85rem;
            display: none;
        }

        .constancia-msg.success {
            background: #f0fdf4;
            border: 1px solid #86efac;
            color: #166534;
            display: block;
        }

        .constancia-msg.error {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            color: #991b1b;
            display: block;
        }

        .constancia-hint {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }

        .btn-ver-pdf {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 0.85rem;
            border: 1px solid #3e8cdd;
            border-radius: 0.375rem;
            color: #3e8cdd;
            background: white;
            font-size: 0.8rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            text-decoration: none;
            transition: background .15s;
            margin-left: auto;
        }

        .btn-ver-pdf:hover {
            background: #eff6ff;
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

                    <div class="page-header">
                        <div>
                            <h1>Mi Perfil</h1>
                            <p>Consulta y edita tu información personal</p>
                        </div>
                        <button class="btn-edit" id="btnEdit">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" style="width:16px;height:16px;">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                            Editar Perfil
                        </button>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                            Información del Cliente
                        </div>

                        <div class="card-body">
                            <form id="profileForm">
                                <div class="fields-grid">

                                    <div class="field">
                                        <label for="f_id">ID</label>
                                        <input type="text" id="f_id" name="id" disabled />
                                        <span class="hint">Campo autogenerado, no editable</span>
                                    </div>

                                    <div class="field">
                                        <label for="f_nombre">Nombre *</label>
                                        <input type="text" id="f_nombre" name="nombre" required />
                                    </div>

                                    <!-- RFC con validación -->
                                    <div class="field">
                                        <label for="f_rfc">RFC</label>
                                        <input type="text" id="f_rfc" name="rfc" maxlength="13"
                                            oninput="validateRFC(this)" />
                                        <span class="field-error" id="rfcError"></span>
                                    </div>

                                    <!-- CURP con validación -->
                                    <div class="field">
                                        <label for="f_curp">CURP</label>
                                        <input type="text" id="f_curp" name="curp" maxlength="18"
                                            oninput="validateCURP(this)" />
                                        <span class="field-error" id="curpError"></span>
                                    </div>

                                    <div class="field">
                                        <label for="f_email">Correo Electrónico *</label>
                                        <input type="email" id="f_email" name="email" required />
                                    </div>

                                    <div class="field">
                                        <label for="f_regimen">Régimen</label>
                                        <input type="text" id="f_regimen" name="regimen" />
                                    </div>

                                    <div class="field">
                                        <label for="f_calle_num">Calle y Número</label>
                                        <input type="text" id="f_calle_num" name="calle_num" />
                                    </div>

                                    <div class="field">
                                        <label for="f_colonia">Colonia</label>
                                        <input type="text" id="f_colonia" name="colonia" />
                                    </div>

                                    <div class="field">
                                        <label for="f_ciudad">Ciudad</label>
                                        <input type="text" id="f_ciudad" name="ciudad" />
                                    </div>

                                    <div class="field">
                                        <label for="f_estado">Estado</label>
                                        <input type="text" id="f_estado" name="estado" />
                                    </div>

                                    <!-- Código Postal con validación -->
                                    <div class="field">
                                        <label for="f_cp">Código Postal (CP)</label>
                                        <input type="text" id="f_cp" name="cp" maxlength="5"
                                            oninput="validateCP(this)" />
                                        <span class="field-error" id="cpError"></span>
                                    </div>

                                    <div class="field">
                                        <label for="f_telefono">Teléfono</label>
                                        <input type="text" id="f_telefono" name="telefono" />
                                    </div>

                                </div><!-- /fields-grid -->

                                <div class="edit-actions" id="editActions">
                                    <button type="button" class="btn-cancel" id="btnCancel">Cancelar</button>
                                    <button type="submit" class="btn-save">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                        </svg>
                                        Guardar Cambios
                                    </button>
                                </div>

                            </form>
                        </div>
                    </div>

                    <div class="info-note">
                        <strong>Nota:</strong> Los campos marcados con * son obligatorios. El ID es autogenerado y no
                        puede ser modificado.
                    </div>

                    <!-- ── Card: Constancia de Situación Fiscal ── -->
                    <div class="constancia-card">
                        <div class="card-header">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                            </svg>
                            Constancia de Situación Fiscal
                        </div>

                        <div class="card-body">

                            <!-- Estado actual del PDF -->
                            <div class="constancia-status none" id="constanciaStatus">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" style="width:16px;height:16px;flex-shrink:0;">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                </svg>
                                <span id="constanciaStatusText">Verificando...</span>
                                <a id="btnVerPdf" href="#" target="_blank" class="btn-ver-pdf" style="display:none;">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor" style="width:14px;height:14px;">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Abrir PDF en pestaña
                                </a>
                            </div>

                            <!-- Visualizador de PDF (Iframe) -->
                            <div id="pdfViewerContainer" style="display:none; margin-bottom:1.5rem;">
                                <iframe id="pdfIframe" src="" width="100%" height="600px"
                                    style="border:1px solid #e5e7eb; border-radius:0.5rem;"
                                    title="Constancia de Situación Fiscal"></iframe>
                            </div>

                            <!-- Área de carga -->
                            <div class="constancia-upload-area">
                                <div class="constancia-file-field">
                                    <label for="inputConstancia">Subir nuevo PDF (máx. 5 MB)</label>
                                    <input type="file" id="inputConstancia" name="constancia" accept=".pdf"
                                        onchange="onConstanciaFileChange(this)" />
                                    <span class="constancia-hint">Solo archivos PDF con RFC y datos oficiales.</span>
                                </div>
                                <button type="button" id="btnSubirConstancia" class="btn-subir"
                                    onclick="subirConstancia()" disabled>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor" style="width:15px;height:15px;">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                    </svg>
                                    Subir PDF
                                </button>
                            </div>

                            <!-- Mensaje de feedback -->
                            <div class="constancia-msg" id="constanciaMsg"></div>

                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <!-- Layout Script -->
    <script>
        const layout_menuItems = [
            { path: 'Dashboard.php', label: 'Panel Principal', icon: '📊' },
            { path: 'company_info.php', label: 'Información Empresa', icon: '🏢' },
            { path: 'MyPurchases.php', label: 'Mis Compras', icon: '🛍️' },
            { path: 'InvoiceGeneration.php', label: 'Generar Factura', icon: '📄' },
            { path: 'Profile.php', label: 'Perfil', icon: '👤' }
        ];

        const currentPath = window.location.pathname.split('/').pop() || 'Dashboard.php';

        function renderNav(containerId) {
            const container = document.getElementById(containerId);
            if (!container) return;
            container.innerHTML = '';
            layout_menuItems.forEach(item => {
                const a = document.createElement('a');
                a.href = item.path;
                a.className = 'nav-btn' + (currentPath === item.path ? ' active' : '');
                a.innerHTML = '<span style="margin-right:0.75rem;">' + item.icon + '</span>' + item.label;
                if (containerId === 'mobileNav') a.onclick = () => toggleMobileMenu(false);
                container.appendChild(a);
            });
        }

        function toggleMobileMenu(show) {
            document.getElementById('mobileMenuOverlay').style.display = show ? 'block' : 'none';
        }

        function handleLogout() { location.href = '../Home/Home.html'; }

        document.addEventListener('DOMContentLoaded', () => {
            renderNav('desktopNav');
            renderNav('mobileNav');
        });
    </script>

    <!-- Page Script -->
    <script>
        // ── Validación RFC ─────────────────────────────────────────────────────────
        // Persona Física:  4 letras + AAMMDD + 3 alfanuméricos = 13 chars
        // Persona Moral:   3 letras + AAMMDD + 3 alfanuméricos = 12 chars
        function validateRFC(input) {
            const rfcFisica = /^[A-ZÑ&]{4}\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])[A-Z0-9]{3}$/i;
            const rfcMoral = /^[A-ZÑ&]{3}\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])[A-Z0-9]{3}$/i;

            input.value = input.value.toUpperCase().replace(/[^A-ZÑ&0-9]/g, '');

            const val = input.value;
            const errorEl = document.getElementById('rfcError');

            // Campo opcional: si está vacío, sin error
            if (val === '') {
                input.classList.remove('invalid', 'valid');
                errorEl.classList.remove('show');
                return true;
            }

            const valid = rfcFisica.test(val) || rfcMoral.test(val);

            if (!valid) {
                input.classList.add('invalid');
                input.classList.remove('valid');
                errorEl.classList.add('show');

                if (val.length < 12) {
                    errorEl.textContent = 'RFC incompleto. Física: 13 chars · Moral: 12 chars.';
                } else {
                    const letras = val.length === 12 ? 3 : 4;
                    const fecha = val.slice(letras, letras + 6);
                    const homo = val.slice(letras + 6);
                    const fechaOk = /^\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])$/.test(fecha);
                    const homoOk = /^[A-Z0-9]{3}$/.test(homo);

                    if (!fechaOk) {
                        errorEl.textContent = 'Fecha inválida. Formato AAMMDD (ej. 850101 = 1 enero 1985).';
                    } else if (!homoOk) {
                        errorEl.textContent = 'Homoclave inválida. Deben ser 3 caracteres alfanuméricos.';
                    } else {
                        errorEl.textContent = 'RFC inválido. Ej. Física: RODR850101ABC · Moral: GFP850101AB3.';
                    }
                }
            } else {
                input.classList.remove('invalid');
                input.classList.add('valid');
                errorEl.classList.remove('show');
            }

            return valid;
        }

        // ── Validación CURP ────────────────────────────────────────────────────────
        // 18 caracteres: 4 letras + AAMMDD + H/M + 2 letras estado + 3 consonantes + 1 letra/num + 1 dígito
        function validateCURP(input) {
            // Patrón oficial del RENAPO
            const curpRegex = /^[A-Z]{4}\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])[HM](AS|BC|BS|CC|CL|CM|CS|CH|DF|DG|GT|GR|HG|JC|MC|MN|MS|NT|NL|OC|PL|QT|QR|SP|SL|SR|TC|TS|TL|VZ|YN|ZS|NE)[B-DF-HJ-NP-TV-Z]{3}[A-Z0-9]\d$/i;

            input.value = input.value.toUpperCase().replace(/[^A-Z0-9]/g, '');

            const val = input.value;
            const errorEl = document.getElementById('curpError');

            // Campo opcional: si está vacío, sin error
            if (val === '') {
                input.classList.remove('invalid', 'valid');
                errorEl.classList.remove('show');
                return true;
            }

            const valid = curpRegex.test(val);

            if (!valid) {
                input.classList.add('invalid');
                input.classList.remove('valid');
                errorEl.classList.add('show');

                if (val.length < 18) {
                    errorEl.textContent = 'CURP incompleta. Debe tener exactamente 18 caracteres.';
                } else if (!/^[A-Z]{4}/.test(val)) {
                    errorEl.textContent = 'Los primeros 4 caracteres deben ser letras (apellidos y nombre).';
                } else if (!/^[A-Z]{4}\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])/.test(val)) {
                    errorEl.textContent = 'La fecha de nacimiento (posiciones 5–10) es inválida. Formato AAMMDD.';
                } else if (!/^[A-Z]{4}\d{6}[HM]/.test(val)) {
                    errorEl.textContent = 'La posición 11 debe ser H (hombre) o M (mujer).';
                } else {
                    errorEl.textContent = 'CURP inválida. Ej: PEGJ850101HDFRNN09.';
                }
            } else {
                input.classList.remove('invalid');
                input.classList.add('valid');
                errorEl.classList.remove('show');
            }

            return valid;
        }

        // ── Validación Código Postal ───────────────────────────────────────────────
        // México: exactamente 5 dígitos, rango 01000–99999
        function validateCP(input) {
            input.value = input.value.replace(/\D/g, '');

            const val = input.value;
            const errorEl = document.getElementById('cpError');

            // Campo opcional: si está vacío, sin error
            if (val === '') {
                input.classList.remove('invalid', 'valid');
                errorEl.classList.remove('show');
                return true;
            }

            const valid = /^\d{5}$/.test(val) && val !== '00000' && parseInt(val) >= 1000;

            if (!valid) {
                input.classList.add('invalid');
                input.classList.remove('valid');
                errorEl.classList.add('show');

                if (val.length < 5) {
                    errorEl.textContent = 'El código postal debe tener exactamente 5 dígitos.';
                } else if (val === '00000' || parseInt(val) < 1000) {
                    errorEl.textContent = 'CP inválido. El rango válido en México es 01000 – 99999.';
                } else {
                    errorEl.textContent = 'Código postal inválido.';
                }
            } else {
                input.classList.remove('invalid');
                input.classList.add('valid');
                errorEl.classList.remove('show');
            }

            return valid;
        }

        // ── Estado del formulario ──────────────────────────────────────────────────
        let isEditing = false;

        // formData se carga desde la BD vía API
        let formData = {};

        const form = document.getElementById('profileForm');
        const btnEdit = document.getElementById('btnEdit');
        const btnCancel = document.getElementById('btnCancel');
        const editActions = document.getElementById('editActions');
        const editableInputs = Array.from(form.querySelectorAll('input')).filter(i => i.name !== 'id');
        const idInput = document.getElementById('f_id');

        function clearValidationStates() {
            ['f_rfc', 'f_curp', 'f_cp'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.classList.remove('invalid', 'valid');
            });
            ['rfcError', 'curpError', 'cpError'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.classList.remove('show');
            });
        }

        function updateUI() {
            // Campo ID (no editable, mapeado desde la BD como "id")
            if (idInput) idInput.value = formData.id || '';
            editableInputs.forEach(input => {
                // La BD devuelve correo_electronico; el form usa name="email"
                if (input.name === 'email') {
                    input.value = formData.correo_electronico || '';
                } else {
                    input.value = formData[input.name] || '';
                }
                input.disabled = !isEditing;
            });
            btnEdit.style.display = isEditing ? 'none' : 'flex';
            editActions.style.display = isEditing ? 'flex' : 'none';
        }

        // Sincronizar cambios al formData en tiempo real
        editableInputs.forEach(input => {
            input.addEventListener('input', e => {
                const key = e.target.name === 'email' ? 'correo_electronico' : e.target.name;
                formData[key] = e.target.value;
            });
        });

        btnEdit.addEventListener('click', () => {
            isEditing = true;
            updateUI();
        });

        btnCancel.addEventListener('click', () => {
            isEditing = false;
            clearValidationStates();
            // Recargar datos desde la BD para descartar cambios no guardados
            cargarPerfil();
        });

        form.addEventListener('submit', e => {
            e.preventDefault();

            if (!formData.nombre || !formData.correo_electronico) {
                alert('Error: Los campos Nombre y Correo Electrónico son obligatorios.');
                return;
            }

            const rfcInput = document.getElementById('f_rfc');
            const curpInput = document.getElementById('f_curp');
            const cpInput = document.getElementById('f_cp');

            if (rfcInput.value && !validateRFC(rfcInput)) { rfcInput.focus(); return; }
            if (curpInput.value && !validateCURP(curpInput)) { curpInput.focus(); return; }
            if (cpInput.value && !validateCP(cpInput)) { cpInput.focus(); return; }

            // ── Guardar en la BD vía API ──────────────────────────────────────
            fetch('../PHP/api_cliente.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    accion: 'actualizar_perfil',
                    nombre: formData.nombre,
                    rfc: formData.rfc || '',
                    curp: formData.curp || '',
                    email: formData.correo_electronico,
                    regimen: formData.regimen || '',
                    calle_num: formData.calle_num || '',
                    colonia: formData.colonia || '',
                    ciudad: formData.ciudad || '',
                    estado: formData.estado || '',
                    cp: formData.cp || '',
                    telefono: formData.telefono || ''
                })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.error) {
                        alert('Error al guardar: ' + data.error);
                        return;
                    }
                    alert('✅ Perfil actualizado correctamente');
                    isEditing = false;
                    clearValidationStates();
                    updateUI();
                })
                .catch(() => alert('Error de conexión al guardar el perfil.'));
        });

        // ── Cargar perfil desde la BD al iniciar ─────────────────────────────
        function cargarPerfil() {
            fetch('../PHP/api_cliente.php?accion=perfil')
                .then(r => r.json())
                .then(data => {
                    if (data.error) {
                        console.error('Error al cargar perfil:', data.error);
                        return;
                    }
                    formData = data;
                    isEditing = false;
                    updateUI();
                })
                .catch(err => console.error('Error de conexión al cargar perfil:', err));
        }

        document.addEventListener('DOMContentLoaded', cargarPerfil);

        // ══════════════════════════════════════════════════════════════════════
        // CONSTANCIA DE SITUACIÓN FISCAL
        // ══════════════════════════════════════════════════════════════════════

        /** Habilita el botón de subir solo cuando el usuario seleccionó un archivo */
        function onConstanciaFileChange(input) {
            const btn = document.getElementById('btnSubirConstancia');
            btn.disabled = !(input.files && input.files.length > 0);
            mostrarMsgConstancia('', '');
        }

        /** Muestra/oculta el mensaje de feedback bajo el campo de archivo */
        function mostrarMsgConstancia(tipo, texto) {
            const el = document.getElementById('constanciaMsg');
            el.className = 'constancia-msg' + (tipo ? ' ' + tipo : '');
            el.textContent = texto;
        }

        /** Actualiza el banner de estado (si ya hay PDF registrado o no) */
        function actualizarStatusConstancia(tienePdf, ruta) {
            const box = document.getElementById('constanciaStatus');
            const text = document.getElementById('constanciaStatusText');
            const link = document.getElementById('btnVerPdf');
            const pdfViewerContainer = document.getElementById('pdfViewerContainer');
            const pdfIframe = document.getElementById('pdfIframe');

            if (tienePdf && ruta) {
                box.className = 'constancia-status ok';
                text.textContent = '✔ Constancia registrada correctamente.';
                link.href = ruta;  // ruta relativa desde donde estamos
                link.style.display = 'inline-flex';
                pdfIframe.src = ruta;
                pdfViewerContainer.style.display = 'block';
            } else {
                box.className = 'constancia-status none';
                text.textContent = 'Sin constancia registrada. Sube tu PDF para completar tu perfil fiscal.';
                link.style.display = 'none';
                pdfViewerContainer.style.display = 'none';
                pdfIframe.src = '';
            }
        }

        /** Consulta la BD y actualiza el banner de estado */
        function cargarEstadoConstancia() {
            fetch('../PHP/api_cliente.php?accion=constancia_fiscal')
                .then(r => r.json())
                .then(data => {
                    if (data.error) {
                        document.getElementById('constanciaStatusText').textContent =
                            'No se pudo verificar el estado de la constancia.';
                        return;
                    }
                    actualizarStatusConstancia(data.tiene_constancia, data.ruta);
                })
                .catch(() => {
                    document.getElementById('constanciaStatusText').textContent =
                        'Error de conexión al verificar la constancia.';
                });
        }

        /** Envía el PDF seleccionado al endpoint subirConstancia.php */
        function subirConstancia() {
            const inputFile = document.getElementById('inputConstancia');
            const btnSubir = document.getElementById('btnSubirConstancia');

            if (!inputFile.files || inputFile.files.length === 0) {
                mostrarMsgConstancia('error', 'Por favor selecciona un archivo PDF primero.');
                return;
            }

            const archivo = inputFile.files[0];

            // Validación rápida de extensión en el cliente (complemento al servidor)
            if (!archivo.name.toLowerCase().endsWith('.pdf')) {
                mostrarMsgConstancia('error', 'Solo se permiten archivos PDF.');
                return;
            }

            if (archivo.size > 5 * 1024 * 1024) {
                mostrarMsgConstancia('error', 'El archivo supera el límite de 5 MB.');
                return;
            }

            // Estado de carga
            btnSubir.disabled = true;
            btnSubir.textContent = 'Subiendo...';
            mostrarMsgConstancia('', '');

            const formData = new FormData();
            formData.append('constancia', archivo);

            fetch('../PHP/subirConstancia.php', {
                method: 'POST',
                body: formData   // NO poner Content-Type manual; el browser lo pone con boundary
            })
                .then(r => r.json())
                .then(data => {
                    btnSubir.disabled = false;
                    btnSubir.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke-width="2" stroke="currentColor" style="width:15px;height:15px;">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                    </svg> Subir PDF`;

                    if (data.error) {
                        mostrarMsgConstancia('error', '❌ ' + data.error);
                        return;
                    }

                    mostrarMsgConstancia('success', '✅ ' + data.message);
                    inputFile.value = ''; // limpiar input
                    btnSubir.disabled = true;
                    cargarEstadoConstancia(); // refrescar banner de estado
                })
                .catch(() => {
                    btnSubir.disabled = false;
                    btnSubir.textContent = 'Subir PDF';
                    mostrarMsgConstancia('error', '❌ Error de conexión. Intenta de nuevo.');
                });
        }

        // Cargar estado de constancia junto con el perfil al iniciar
        document.addEventListener('DOMContentLoaded', cargarEstadoConstancia);
    </script>

    <script src="ClienteSidebar.js"></script>
</body>

</html>