<?php require_once '../PHP/auth.php';
auth_require_rol('admin'); ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Panel de administración para editar el contenido del HOME y la información corporativa de Grupo Ferretero Piedras.">
    <title>Editar HOME — PanelAdmin</title>
    <link rel="stylesheet" href="../estilos.css">
    <style>
        /* ── Paneles del formulario ────────────────────────────────────── */
        .form-panel {
            background: #fff;
            border-radius: 0.625rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.07);
            padding: 24px;
            margin-bottom: 24px;
        }

        .form-panel h3 {
            color: var(--color-azul);
            margin-bottom: 16px;
            font-weight: 700;
            border-bottom: 1px solid var(--color-borde);
            padding-bottom: 8px;
        }

        /* ── Grid de campos ─────────────────────────────────────────────── */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--color-muted);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            padding: 10px 12px;
            border: 1px solid var(--color-borde);
            border-radius: 6px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: border-color 0.2s, box-shadow 0.2s;
            background: #fafafa;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--color-azul);
            box-shadow: 0 0 0 3px rgba(62, 140, 221, 0.15);
            background: #fff;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 90px;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        /* ── Upload de logo ──────────────────────────────────────────────── */
        .logo-upload-area {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 16px;
            border: 2px dashed var(--color-borde);
            border-radius: 8px;
            background: #fafafa;
            transition: border-color 0.2s;
            cursor: pointer;
        }

        .logo-upload-area:hover {
            border-color: var(--color-azul);
        }

        #preview-logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
            border-radius: 8px;
            border: 1px solid var(--color-borde);
            background: #fff;
            padding: 4px;
        }

        .logo-info p {
            font-size: 0.85rem;
            color: var(--color-muted);
            margin: 2px 0;
        }

        .logo-info strong {
            font-size: 0.92rem;
            color: var(--color-texto);
        }

        #logo_imagen {
            display: none;
        }

        /* ocultamos el input, activamos con click */
        .btn-elegir-logo {
            margin-top: 8px;
            padding: 6px 14px;
            background: var(--color-azul);
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        .btn-elegir-logo:hover {
            opacity: 0.85;
        }

        /* ── Botón guardar ───────────────────────────────────────────────── */
        .btn-save {
            background-color: var(--color-verde);
            color: #fff;
            padding: 12px 28px;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.15s, box-shadow 0.2s;
        }

        .btn-save:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(116, 203, 60, 0.3);
        }

        .btn-save:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* ── Mensaje de resultado ────────────────────────────────────────── */
        #mensaje {
            margin-top: 16px;
            padding: 12px 16px;
            border-radius: 6px;
            display: none;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .msg-success {
            background: #e0f2d9;
            color: #387019;
            border-left: 4px solid #74cb3c;
        }

        .msg-error {
            background: #fceceb;
            color: #c0392b;
            border-left: 4px solid #e74c3c;
        }

        /* ── Sección fecha de actualización ─────────────────────────────── */
        .ultimo-update {
            font-size: 0.8rem;
            color: var(--color-muted);
            text-align: right;
            margin-bottom: 8px;
        }
    </style>
</head>

<body>
    <div class="layout">
        <!-- Sidebar inyectado por sidebar.js -->
        <div id="sidebar"></div>

        <div class="contenido-principal">
            <header class="header">
                <h1 style="font-size:1.25rem;color:var(--color-azul);font-weight:700;margin:0;">
                    📝 Editar Contenido del HOME
                </h1>
            </header>

            <main class="area-pagina">
                <p style="color:var(--color-muted);margin-bottom:8px;">
                    Gestiona dinámicamente la información corporativa y de contacto que se muestra en las vistas
                    públicas
                    (Inicio y Portal Cliente). Los cambios se sincronizan automáticamente.
                </p>
                <!-- Último update -->
                <p class="ultimo-update" id="txt-ultimo-update"></p>

                <!-- ══════════════════════════════════════════════════════
                     FORMULARIO PRINCIPAL — multipart/form-data para logo
                     ══════════════════════════════════════════════════════ -->
                <form id="homeForm" enctype="multipart/form-data" class="content">

                    <!-- ── 1. Logo ─────────────────────────────────────── -->
                    <div class="form-panel">
                        <h3>🖼️ Logotipo de la Empresa</h3>
                        <div class="logo-upload-area" onclick="document.getElementById('logo_imagen').click()">
                            <!-- Preview actual -->
                            <img id="preview-logo" src="../imagenes_empresa/logo.png" alt="Logo actual"
                                onerror="this.src='../Administrador/Logo_GF_piedra .PNG'">
                            <div class="logo-info">
                                <strong id="nombre-logo-actual">logo.png (actual)</strong>
                                <p>Clic para cambiar el logo.</p>
                                <p>Formatos: JPG, PNG, GIF, WebP · Máximo 5 MB.</p>
                                <button type="button" class="btn-elegir-logo"
                                    onclick="event.stopPropagation();document.getElementById('logo_imagen').click()">
                                    📁 Elegir imagen
                                </button>
                            </div>
                        </div>
                        <!-- Input real del archivo -->
                        <input type="file" id="logo_imagen" name="logo_imagen"
                            accept="image/jpeg,image/png,image/webp,image/gif">
                        <!--
                            PERSISTENCIA DEL LOGO:
                            Este campo oculto guarda el nombre del logo actual (poblado por JS).
                            Si el usuario NO sube una imagen nueva, el servidor usa este valor
                            para conservar logo_imagen en la BD sin sobrescribirlo con vacío.
                        -->
                        <input type="hidden" id="logo_actual" name="logo_actual" value="">
                    </div>

                    <!-- ── 2. Identidad corporativa ───────────────────── -->
                    <div class="form-panel">
                        <h3>🏢 Identidad Corporativa</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nombre_empresa">Nombre / Razón Social</label>
                                <input type="text" id="nombre_empresa" name="nombre_empresa"
                                    placeholder="Grupo Ferretero Piedras S.A. de C.V." required>
                            </div>
                            <div class="form-group">
                                <label for="ano_fundacion">Año de Fundación</label>
                                <input type="text" id="ano_fundacion" name="ano_fundacion" placeholder="2011"
                                    maxlength="10">
                            </div>
                            <div class="form-group full-width">
                                <label for="giro_comercial">Giro Comercial</label>
                                <input type="text" id="giro_comercial" name="giro_comercial"
                                    placeholder="Venta de gf_materiales de construcción">
                            </div>
                            <div class="form-group full-width">
                                <label for="titulo_bienvenida">Título Hero (sección de bienvenida en el Inicio)</label>
                                <input type="text" id="titulo_bienvenida" name="titulo_bienvenida"
                                    placeholder="Bienvenido a Grupo Ferretero Piedras" required>
                            </div>
                            <div class="form-group full-width">
                                <label for="subtitulo">Subtítulo / Bajada de Bienvenida</label>
                                <input type="text" id="subtitulo" name="subtitulo"
                                    placeholder="Venta de gf_materiales para la construcción">
                            </div>
                        </div>
                    </div>

                    <!-- ── 3. Misión y Visión ─────────────────────────── -->
                    <div class="form-panel">
                        <h3>🎯 Misión y Visión</h3>
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="mision">Misión</label>
                                <textarea id="mision" name="mision" rows="5"
                                    placeholder="Describe la misión de la empresa…" required></textarea>
                            </div>
                            <div class="form-group full-width">
                                <label for="vision">Visión</label>
                                <textarea id="vision" name="vision" rows="5"
                                    placeholder="Describe la visión de la empresa…" required></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- ── 4. Contacto ────────────────────────────────── -->
                    <div class="form-panel">
                        <h3>📞 Información de Contacto</h3>
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="email">Correo Electrónico</label>
                                <input type="email" id="email" name="email" placeholder="empresa@ejemplo.com" required>
                            </div>
                            <div class="form-group">
                                <label for="telefono1">Teléfono Principal</label>
                                <input type="text" id="telefono1" name="telefono1" placeholder="+52 (55) 1234-5678"
                                    required>
                            </div>
                            <div class="form-group">
                                <label for="telefono2">Teléfono Secundario (opcional)</label>
                                <input type="text" id="telefono2" name="telefono2" placeholder="+52 (55) 8765-4321">
                            </div>
                        </div>
                    </div>

                    <!-- ── 5. Dirección ───────────────────────────────── -->
                    <div class="form-panel">
                        <h3>📍 Dirección Física</h3>
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="calle_num">Calle y Número</label>
                                <input type="text" id="calle_num" name="calle_num"
                                    placeholder="Calle Venustiano Carranza 6" required>
                            </div>
                            <div class="form-group">
                                <label for="colonia">Colonia / Barrio</label>
                                <input type="text" id="colonia" name="colonia" placeholder="San Bartolomé" required>
                            </div>
                            <div class="form-group">
                                <label for="ciudad">Municipio / Ciudad</label>
                                <input type="text" id="ciudad" name="ciudad" placeholder="San Pablo del Monte" required>
                            </div>
                            <div class="form-group">
                                <label for="estado">Estado</label>
                                <input type="text" id="estado" name="estado" placeholder="Tlaxcala" required>
                            </div>
                            <div class="form-group">
                                <label for="cp">Código Postal</label>
                                <input type="text" id="cp" name="cp" placeholder="00000" maxlength="5" pattern="\d{5}"
                                    required>
                            </div>
                        </div>
                    </div>

                    <!-- ── Acción guardar ─────────────────────────────── -->
                    <div style="text-align:right;margin-bottom:40px;">
                        <button type="submit" id="btn-guardar" class="btn-save">
                            💾 Guardar Cambios
                        </button>
                        <div id="mensaje"></div>
                    </div>

                </form><!-- /homeForm -->
            </main>
        </div>
    </div>

    <!-- Dependencias de layout -->
    <script src="sidebar.js"></script>
    <!-- Lógica específica del formulario -->
    <script src="admin_home.js"></script>
</body>

</html>