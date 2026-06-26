<?php require_once '../PHP/auth.php'; auth_require_rol('admin'); ?>
<!DOCTYPE html>
<!--
    ============================================================
    admin_facturas.php — Gestión de Facturas
    ============================================================
    Equivale al componente Invoices.tsx de React.

    ESTRUCTURA:
    ┌──────────────────────────────────────────────────────────┐
    │  HEADER                                                  │
    ├───────────┬──────────────────────────────────────────────┤
    │           │  Título + subtítulo                         │
    │ SIDEBAR   ├──────────────────────────────────────────────┤
    │ (fijo)    │  STATS: [Total Facturas] [Total Facturado]  │
    │           ├──────────────────────────────────────────────┤
    │           │  TABLA: Folio, Ticket, Cliente, Fecha...    │
    └───────────┴──────────────────────────────────────────────┘

    Archivos que necesita:
    - styles.css        → Layout general (sidebar fijo, header, etc.)
    - inventory.css     → Estilos de tabla, cards, notificaciones
    - admin_facturas.js → Datos y lógica de cálculo
    ============================================================
-->
<html lang="es">

<head>
    <!-- FAVICON (igual que en dashboard.php e inventory.php) -->
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="manifest" href="site.webmanifest">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Facturas — PanelAdmin</title>

    <!-- Estilos del proyecto (layout, sidebar, header, variables) -->
    <link rel="stylesheet" href="../estilos.css">

    <!--
        CHART.JS — Librería de gráficas desde CDN.
        Equivale a recharts (BarChart, Bar, XAxis, etc.) del código React original.
        CDN = Content Delivery Network: carga la librería desde internet,
        no necesitamos instalar nada.
    -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <!-- Estilos específicos de esta página (filtro de fechas) -->
    <style>
        /* ── Panel de filtro de fechas ── */
        .filtro-panel {
            background: #fff;
            border-radius: 0.625rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.07);
            padding: 18px 24px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .filtro-panel label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--color-muted);
        }

        .filtro-panel input[type="date"], .filtro-panel input[type="text"] {
            padding: 7px 12px;
            border: 1px solid var(--color-borde);
            border-radius: 6px;
            font-size: 0.875rem;
            color: var(--color-texto);
            background: #f9f9fb;
            outline: none;
        }

        .filtro-panel input[type="date"] { cursor: pointer; }

        .filtro-panel input[type="date"]:focus, .filtro-panel input[type="text"]:focus {
            border-color: var(--color-azul);
            box-shadow: 0 0 0 3px rgba(62, 140, 221, 0.15);
        }

        .filtro-panel .filtro-separador {
            color: var(--color-muted);
            font-weight: 600;
        }

        .btn-filtro {
            padding: 8px 18px;
            border: none;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        .btn-filtro:hover {
            opacity: 0.85;
        }

        .btn-filtro-aplicar {
            background-color: var(--color-azul);
            color: #fff;
        }

        .btn-filtro-reset {
            background-color: var(--color-muted-fondo);
            color: var(--color-texto);
        }

        .filtro-titulo {
            font-weight: 700;
            color: var(--color-azul);
            font-size: 0.95rem;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .filtro-titulo-seccion {
            width: 100%;
            border-bottom: 1px solid var(--color-borde);
            padding-bottom: 10px;
            margin-bottom: 4px;
        }
    </style>
</head>

<body>
    <!-- ============================================================
         LAYOUT PRINCIPAL
         El sidebar es FIJO (position: fixed en styles.css).
         El contenido usa margin-left para no quedar debajo.
         ============================================================ -->
    <div class="layout">

        <!-- SIDEBAR FIJO -->
        <div id="sidebar"></div>
        <!-- FIN SIDEBAR -->


        <!-- CONTENIDO PRINCIPAL (margen izquierdo = ancho del sidebar) -->
        <div class="contenido-principal">

            <header class="header">
                <span>Gestión de Facturas</span>
            </header>

            <main class="area-pagina">

                <!-- ============================================
                     ENCABEZADO DE LA PÁGINA
                     ============================================ -->
                <div class="pagina-header">
                    <div>
                        <h1 class="pagina-titulo">
                            Gestión de Facturas
                        </h1>
                        <p class="pagina-subtitulo">
                            Consulta y administración de facturas emitidas
                        </p>
                    </div>
                    <!-- No hay botón "Agregar": solo visualización -->
                </div>


                <!-- ============================================
                     TARJETAS DE ESTADÍSTICAS (solo 2)
                     ============================================
                     Se eliminó el recuadro "Facturas emitidas"
                     según las instrucciones. Solo quedan:
                     - Total de gf_facturas
                     - Total facturado
                     ============================================ -->
                <div class="stats-grid stats-grid-2">

                    <div class="stat-card stat-azul">
                        <div class="stat-label">Total de Facturas</div>
                        <!-- JS actualiza este valor con document.getElementById -->
                        <div id="stat-total-gf_facturas" class="stat-valor stat-valor-azul">0</div>
                    </div>

                    <div class="stat-card stat-verde">
                        <div class="stat-label">Total Facturado</div>
                        <div id="stat-total-facturado" class="stat-valor stat-valor-verde stat-valor-md">$0</div>
                    </div>

                </div>

                <!-- ============================================
                FILTRO DE FECHAS (nuevo)
                ============================================ -->
                <div class="filtro-panel">
                    <div class="filtro-titulo-seccion">
                        <span class="filtro-titulo">🔍 Filtros</span>
                    </div>
                    <div class="filtro-grupo" style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-size: 0.8rem; font-weight: 700; color: var(--color-muted); text-transform: uppercase;">📅 Fecha desde</label>
                        <input type="date" id="filtro-desde" onchange="aplicarFiltro()">
                    </div>
                    <div class="filtro-separador">—</div>
                    <div class="filtro-grupo" style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-size: 0.8rem; font-weight: 700; color: var(--color-muted); text-transform: uppercase;">📅 Fecha hasta</label>
                        <input type="date" id="filtro-hasta" onchange="aplicarFiltro()">
                    </div>

                    <div class="filtro-grupo" style="position: relative; flex: 1; min-width: 200px;">
                        <label style="font-size: 0.8rem; font-weight: 700; color: var(--color-azul); text-transform: uppercase; letter-spacing: 0.04em; display: block; margin-bottom: 6px;">👤 Cliente</label>
                        <input type="text" id="filtro-cliente-input" placeholder="Buscar cliente..." style="padding: 9px 12px; border: 1px solid var(--color-borde); border-radius: 6px; font-size: 0.9rem; outline: none; width: 100%;" oninput="renderizarDropdownBusqueda('cliente')" onfocus="renderizarDropdownBusqueda('cliente')" autocomplete="off">
                        <ul id="lista-busqueda-cliente" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ddd; border-radius: 4px; max-height: 200px; overflow-y: auto; z-index: 1000; margin: 4px 0 0 0; padding: 0; list-style: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"></ul>
                        <input type="hidden" id="filtro-cliente">
                    </div>

                    <button class="btn-filtro btn-filtro-aplicar" onclick="aplicarFiltro()">
                        Aplicar
                    </button>
                    <button id="btnLimpiarFiltros" class="btn-filtro btn-filtro-reset">
                        Limpiar filtros
                    </button>
                </div>
                <!-- ============================================
                     GRÁFICA DE FACTURACIÓN POR MES
                     ============================================
                     Equivale al componente <BarChart> de recharts.
                     Chart.js dibuja en el elemento <canvas>.

                     La función generarGraficaFacturas() en
                     admin_facturas.js agrupa las gf_facturas por mes,
                     calcula el total de cada uno y configura Chart.js
                     para mostrar las barras.
                     ============================================ -->
                <div class="tabla-card grafica-card">

                    <div class="tabla-header">
                        <!-- Ícono TrendingUp como SVG (equivale a <TrendingUp> de lucide-react) -->
                        <svg class="icono-titulo" viewBox="0 0 24 24">
                            <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" />
                            <polyline points="17 6 23 6 23 12" />
                        </svg>
                        <span>Facturación por Mes</span>
                    </div>

                    <div class="grafica-contenedor">
                        <!--
                            <canvas> = el lienzo donde Chart.js dibuja.
                            id="grafica-facturas" → JS lo busca con getElementById
                            para obtener el contexto de dibujo 2D.
                        -->
                        <canvas id="grafica-facturas"></canvas>
                    </div>

                </div>
                <!-- FIN grafica-card -->


                <!-- ============================================
                     TABLA DE FACTURAS
                     ============================================
                     El <tbody id="cuerpo-tabla-facturas"> empieza
                     vacío. admin_facturas.js lo llena con
                     renderizarTablaFacturas() al cargar la página.
                     ============================================ -->
                <div class="tabla-card">

                    <div class="tabla-header">
                        <!-- Ícono de documento (FileText de lucide) como SVG -->
                        <svg class="icono-titulo" viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                            <line x1="16" y1="13" x2="8" y2="13" />
                            <line x1="16" y1="17" x2="8" y2="17" />
                        </svg>
                        <span>Registro de Facturas</span>
                    </div>

                    <div class="tabla-contenedor">
                        <table class="tabla">
                            <thead>
                                <tr>
                                    <th>ID Factura</th>
                                    <th>ID Ticket</th>
                                    <th>Cliente</th>
                                    <th>Fecha</th>
                                    <th>Tipo (CFDI)</th>
                                    <th>Total Factura</th>
                                    <th>PDF</th>
                                </tr>
                            </thead>
                            <!--
                                CUERPO VACÍO — admin_facturas.js lo
                                llenará con renderizarTablaFacturas()
                            -->
                            <tbody id="cuerpo-tabla-facturas">
                                <!-- Filas generadas dinámicamente -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Contenedor de Paginación -->
                    <div id="paginacion-contenedor"></div>
                </div>

            </main>
        </div>
        <!-- FIN contenido-principal -->

    </div>
    <!-- FIN layout -->

    <!-- JS al final del body para que el DOM ya exista al ejecutarse -->
    <script src="paginacion.js"></script>
    <script src="admin_facturas.js?v=2"></script>
    <script src="sidebar.js"></script>

    <!-- ============================================
         MODAL PARA VER PDF (Iframe dinámico)
         ============================================ -->
    <div id="pdfModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.6); align-items:center; justify-content:center;">
        <div style="background:#fff; width:90%; max-width:900px; height:85vh; border-radius:8px; display:flex; flex-direction:column; overflow:hidden;">
            <div style="padding:15px; background:#3e8cdd; color:white; display:flex; justify-content:space-between; align-items:center;">
                <h3 style="margin:0; font-size:1.1rem;">Visor de Factura</h3>
                <button onclick="cerrarModalPdf()" style="background:transparent; border:none; color:white; font-size:1.5rem; cursor:pointer; line-height:1;">&times;</button>
            </div>
            <div style="flex:1; padding:0; background:#e5e7eb; display:flex; flex-direction:column;">
                <iframe id="pdfIframe" src="" style="width:100%; height:100%; border:none;" title="Visor de PDF"></iframe>
            </div>
        </div>
    </div>

    <script>
        function abrirModalPdf(url) {
            document.getElementById('pdfIframe').src = url;
            document.getElementById('pdfModal').style.display = 'flex';
        }
        function cerrarModalPdf() {
            document.getElementById('pdfModal').style.display = 'none';
            document.getElementById('pdfIframe').src = '';
        }
    </script>

</body>

</html>