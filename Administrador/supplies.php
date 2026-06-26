<?php require_once '../PHP/auth.php'; auth_require_rol('admin'); ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="manifest" href="site.webmanifest">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Suministros — PanelAdmin</title>

    <link rel="stylesheet" href="../estilos.css">

    <style>
        /* ── Stats de 2 columnas ── */
        .stats-grid-2 {
            grid-template-columns: repeat(2, 1fr);
        }

        @media (max-width: 700px) {
            .stats-grid-2 {
                grid-template-columns: 1fr;
            }
        }

        /* ── Panel de filtros ── */
        .filtro-panel {
            background: #fff;
            border-radius: 0.625rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.07);
            padding: 18px 24px;
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 20px;
        }

        .filtro-grupo {
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-width: 160px;
        }

        .filtro-grupo label {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--color-azul);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .filtro-grupo input[type="date"],
        .filtro-grupo select {
            padding: 7px 12px;
            border: 1px solid var(--color-borde);
            border-radius: 6px;
            font-size: 0.875rem;
            color: var(--color-texto);
            background: #f9f9fb;
            cursor: pointer;
            outline: none;
            min-width: 140px;
        }

        .filtro-grupo input[type="date"]:focus,
        .filtro-grupo select:focus {
            border-color: var(--color-azul);
            box-shadow: 0 0 0 3px rgba(62, 140, 221, 0.15);
        }

        .filtro-separador {
            color: var(--color-muted);
            font-weight: 600;
            align-self: flex-end;
            padding-bottom: 8px;
        }

        .filtro-acciones {
            display: flex;
            align-items: flex-end;
            gap: 8px;
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

        /* ── Tabla de tickets ── */
        .celda-total {
            color: var(--color-verde);
            font-weight: 700;
        }

        /* ── Ticket cards ── */
        .tickets-lista {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 16px 0;
        }

        .ticket-card {
            background: #fff;
            border: 1px solid var(--color-borde);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
            transition: box-shadow 0.2s;
        }

        .ticket-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.10);
        }

        .ticket-list-grid-header {
            display: grid;
            grid-template-columns: 2fr 1fr 1.5fr 150px;
            gap: 15px;
            padding: 10px 20px;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--color-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid var(--color-borde);
            margin: 0 16px 10px 16px;
        }

        .ticket-card-header {
            display: grid;
            grid-template-columns: 2fr 1fr 1.5fr 150px;
            gap: 15px;
            align-items: center;
            padding: 14px 20px;
            background: linear-gradient(90deg, #f8faff 0%, #fff 100%);
        }

        @media (max-width: 800px) {
            .ticket-list-grid-header {
                display: none;
            }
            .ticket-card-header {
                grid-template-columns: 1fr;
                gap: 10px;
            }
        }

        .ticket-card-header:hover {
            background: #f0f7ff;
        }

        .ticket-id {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--color-azul);
            min-width: 56px;
        }

        .ticket-proveedor {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--color-texto);
        }

        .ticket-fecha {
            font-size: 0.82rem;
            color: var(--color-muted);
        }

        .ticket-total {
            font-size: 1rem;
            font-weight: 700;
            color: var(--color-verde);
        }

        .ticket-header-derecha {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn-ver-mas {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--color-azul);
            background: rgba(62, 140, 221, 0.1);
            border: 1px solid rgba(62, 140, 221, 0.25);
            border-radius: 20px;
            padding: 4px 14px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-ver-mas:hover {
            background: rgba(62, 140, 221, 0.2);
        }

        /* ── Detalle expandible ── */
        .ticket-detalle {
            display: none;
            border-top: 1px solid var(--color-borde);
            padding: 16px 20px;
            background: #fafbff;
            animation: slideDown 0.2s ease;
        }

        .ticket-detalle.abierto {
            display: block;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .detalle-tabla {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .detalle-tabla th {
            text-align: left;
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--color-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 6px 10px;
            border-bottom: 1px solid var(--color-borde);
        }

        .detalle-tabla td {
            padding: 8px 10px;
            font-size: 0.88rem;
            color: var(--color-texto);
            border-bottom: 1px solid #f0f2f7;
        }

        .detalle-tabla tr:last-child td {
            border-bottom: none;
        }

        .detalle-total-row {
            font-weight: 700;
            color: var(--color-verde);
        }

        .detalle-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 10px;
            border-top: 1px solid var(--color-borde);
            flex-wrap: wrap;
            gap: 8px;
        }

        /* ── Modal ── */
        .modal-contenido {
            max-width: 680px;
        }

        /* Input de proveedor (texto + datalist) — idéntico al diseño del select */
        .form-grupo input[type="text"] {
            padding: 9px 12px;
            border: 1px solid var(--color-borde);
            border-radius: 6px;
            font-size: 0.9rem;
            color: var(--color-texto);
            background-color: #f9f9fb;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
            width: 100%;
        }

        .form-grupo input[type="text"]:focus {
            border-color: var(--color-azul);
            box-shadow: 0 0 0 3px rgba(62, 140, 221, 0.15);
            background: #fff;
        }

        .form-grupo input[type="text"]::placeholder {
            color: #9ca3af;
            font-weight: 300;
        }

        /* Mantener estilos del select para otros contextos */
        .form-grupo select {
            padding: 9px 12px;
            border: 1px solid var(--color-borde);
            border-radius: 6px;
            font-size: 0.9rem;
            color: var(--color-texto);
            background-color: #f9f9fb;
            transition: border-color 0.2s;
            outline: none;
            width: 100%;
        }

        .form-grupo select:focus {
            border-color: var(--color-azul);
            box-shadow: 0 0 0 3px rgba(62, 140, 221, 0.15);
            background: #fff;
        }

        /* Hint de validación bajo el input de proveedor */
        .hint-proveedor {
            font-size: 0.75rem;
            color: var(--color-muted);
            margin-top: 3px;
        }

        .hint-proveedor.valido {
            color: #16a34a;
        }

        .hint-proveedor.invalido {
            color: #dc2626;
        }

        /* ── Líneas de gf_materiales ── */
        .lineas-materiales {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin: 16px 0;
        }

        .linea-material {
            display: grid;
            grid-template-columns: 2fr 80px 110px 36px;
            gap: 8px;
            align-items: center;
            background: #f4f6fb;
            border: 1px solid var(--color-borde);
            border-radius: 8px;
            padding: 10px 12px;
        }

        /* Input de material (texto + datalist) */
        .linea-input-material {
            width: 100%;
            padding: 7px 10px;
            border: 1px solid var(--color-borde);
            border-radius: 6px;
            font-size: 0.85rem;
            background: #fff;
            outline: none;
            color: var(--color-texto);
            transition: border-color 0.2s;
        }

        .linea-input-material:focus {
            border-color: var(--color-azul);
            box-shadow: 0 0 0 3px rgba(62, 140, 221, 0.12);
        }

        .linea-input-material::placeholder {
            color: #9ca3af;
        }

        .linea-material input[type="number"] {
            width: 100%;
            padding: 7px 10px;
            border: 1px solid var(--color-borde);
            border-radius: 6px;
            font-size: 0.85rem;
            background: #fff;
            outline: none;
        }

        .linea-material input[type="number"]:focus {
            border-color: var(--color-azul);
        }

        .btn-quitar-linea {
            width: 32px;
            height: 32px;
            border: none;
            border-radius: 6px;
            background: #fee2e2;
            color: #ef4444;
            font-size: 1.1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
            flex-shrink: 0;
        }

        .btn-quitar-linea:hover {
            background: #fca5a5;
        }

        .btn-agregar-linea {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 16px;
            border: 2px dashed var(--color-azul);
            border-radius: 8px;
            background: transparent;
            color: var(--color-azul);
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            justify-content: center;
            transition: background 0.2s;
        }

        .btn-agregar-linea:hover {
            background: rgba(62, 140, 221, 0.08);
        }

        .lineas-header {
            display: grid;
            grid-template-columns: 2fr 80px 110px 36px;
            gap: 8px;
            padding: 0 12px;
            margin-bottom: 4px;
        }

        .lineas-header span {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--color-muted);
            text-transform: uppercase;
        }

        .modal-total-preview {
            text-align: right;
            font-size: 1rem;
            font-weight: 700;
            color: var(--color-verde);
            padding: 8px 12px;
            background: #f0fdf4;
            border-radius: 8px;
            margin-top: 4px;
        }

        /* Hint debajo del input de material */
        .hint-material {
            font-size: 0.72rem;
            color: var(--color-muted);
            grid-column: 1;
            margin-top: -4px;
            padding-left: 2px;
        }
    </style>
</head>

<body>
    <!-- datalists globales — los llena supplies.js con datos de la BD -->
    <datalist id="datalist-materiales"></datalist>
    <datalist id="datalist-proveedores"></datalist>

    <!-- Notificación flotante -->
    <div id="notificacion" class="notificacion" style="display:none;"></div>

    <!-- ============================================================
         MODAL DETALLE
    ============================================================ -->
    <div id="modal-detalle" class="modal-overlay" style="display:none;">
        <div class="modal-contenido">
            <div class="modal-header">
                <h2 id="detalle-titulo" class="modal-titulo">Detalle del Ticket</h2>
                <button class="btn-cerrar-modal" onclick="cerrarModalDetalle()">×</button>
            </div>
            <div id="detalle-contenido" style="padding: 16px 0;"></div>
            <div class="form-botones">
                <button type="button" class="btn btn-cancelar" onclick="cerrarModalDetalle()">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- ============================================================
         MODAL NUEVO / EDITAR TICKET
    ============================================================ -->
    <div id="modal-suministro" class="modal-overlay" style="display:none;">
        <div class="modal-contenido">
            <div class="modal-header">
                <h2 id="modal-titulo" class="modal-titulo">Nuevo Pedido de Suministro</h2>
                <button class="btn-cerrar-modal" onclick="cerrarModal()">×</button>
            </div>
            <form id="form-suministro" onsubmit="guardarTicket(event)">
                <input type="hidden" id="campo-id">

                <div class="form-grid">
                    <!-- Proveedor — input + datalist (escribe o selecciona) -->
                    <div class="form-grupo form-grupo-completo">
                        <label for="campo-proveedor-busqueda">Proveedor *</label>
                        <!--
                          El empleado escribe el nombre y el browser filtra sugerencias.
                          El ID numérico real se guarda en #campo-proveedor-id (hidden).
                          supplies.js lee ese hidden al guardar el ticket.
                        -->
                        <input
                            type="text"
                            id="campo-proveedor-busqueda"
                            list="datalist-proveedores"
                            placeholder="Escribe para buscar proveedor…"
                            autocomplete="off"
                            required
                            oninput="onEscrituraProveedor(this)"
                        />
                        <input type="hidden" id="campo-proveedor-id" value="" />
                        <span class="hint-proveedor" id="hint-proveedor"></span>
                    </div>

                    <!-- Fecha -->
                    <div class="form-grupo form-grupo-completo">
                        <label for="campo-fecha">Fecha del Pedido *</label>
                        <input type="date" id="campo-fecha" required>
                    </div>
                </div>

                <!-- Líneas de gf_materiales -->
                <div style="margin-top:16px;">
                    <div style="font-weight:700; font-size:0.9rem; color:var(--color-azul); margin-bottom:8px;">
                        📦 Materiales del Pedido
                    </div>
                    <table class="tabla" style="width: 100%; margin-bottom: 10px;">
                        <thead>
                            <tr>
                                <th style="text-align: left; padding: 10px; border-bottom: 2px solid var(--color-borde); color: var(--color-azul);">Nombre (Material)</th>
                                <th style="text-align: left; padding: 10px; border-bottom: 2px solid var(--color-borde); color: var(--color-azul);">Cantidad</th>
                                <th style="text-align: left; padding: 10px; border-bottom: 2px solid var(--color-borde); color: var(--color-azul);">Costo Unit.</th>
                                <th style="text-align: center; padding: 10px; border-bottom: 2px solid var(--color-borde); color: var(--color-azul);">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="lineas-materiales-container">
                            <!-- Líneas se agregan dinámicamente por supplies.js -->
                        </tbody>
                    </table>
                    <button type="button" class="btn-agregar-linea" onclick="agregarLinea()">
                        + Agregar Material
                    </button>
                </div>

                <!-- Total preview -->
                <div class="modal-total-preview" id="modal-total-preview">
                    Total estimado: $0.00
                </div>

                <div class="form-botones">
                    <button type="button" class="btn btn-cancelar" onclick="cerrarModal()">Cancelar</button>
                    <button type="submit" class="btn btn-guardar">Guardar Pedido</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================
         LAYOUT PRINCIPAL
    ============================================================ -->
    <div class="layout">
        <div id="sidebar"></div>

        <div class="contenido-principal">
            <header class="header"><span>Gestión de Suministros</span></header>

            <main class="area-pagina">
                <div class="pagina-header">
                    <div>
                        <h1 class="pagina-titulo">Gestión de Suministros</h1>
                        <p class="pagina-subtitulo">Pedidos y detalle de suministros por ticket</p>
                    </div>
                    <button class="btn btn-agregar-principal" onclick="abrirModalNuevo()">
                        <svg class="icono-btn" viewBox="0 0 24 24">
                            <line x1="12" y1="5" x2="12" y2="19" />
                            <line x1="5" y1="12" x2="19" y2="12" />
                        </svg>
                        Nuevo Pedido
                    </button>
                </div>

                <!-- ESTADÍSTICAS -->
                <div class="stats-grid stats-grid-2">
                    <div class="stat-card stat-azul">
                        <div class="stat-label">Total Tickets</div>
                        <div id="stat-total" class="stat-valor stat-valor-azul">0</div>
                    </div>
                    <div class="stat-card stat-verde">
                        <div class="stat-label">Inversión Total</div>
                        <div id="stat-inversion" class="stat-valor stat-valor-verde" style="font-size:1.6rem;">$0</div>
                    </div>
                </div>

                <!-- FILTROS -->
                <div class="filtro-panel">
                    <div class="filtro-titulo-seccion">
                        <span class="filtro-titulo">🔍 Filtros</span>
                    </div>
                    <div class="filtro-grupo">
                        <label>📅 Fecha desde</label>
                        <input type="date" id="filtro-desde" onchange="aplicarFiltros()">
                    </div>
                    <div class="filtro-separador">—</div>
                    <div class="filtro-grupo">
                        <label>📅 Fecha hasta</label>
                        <input type="date" id="filtro-hasta" onchange="aplicarFiltros()">
                    </div>
                    <div class="filtro-grupo" style="position: relative; flex: 1; min-width: 200px;">
                        <label style="font-size: 0.8rem; font-weight: 700; color: var(--color-azul); text-transform: uppercase; letter-spacing: 0.04em;">🚚 Proveedor</label>
                        <input type="text" id="filtro-proveedor-input" placeholder="Buscar proveedor..." style="padding: 9px 12px; border: 1px solid var(--color-borde); border-radius: 6px; font-size: 0.9rem; outline: none; width: 100%;" oninput="renderizarDropdownBusqueda('proveedor')" onfocus="renderizarDropdownBusqueda('proveedor')" autocomplete="off">
                        <ul id="lista-busqueda-proveedor" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ddd; border-radius: 4px; max-height: 200px; overflow-y: auto; z-index: 1000; margin: 4px 0 0 0; padding: 0; list-style: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"></ul>
                        <input type="hidden" id="filtro-proveedor">
                    </div>
                    <div class="filtro-grupo" style="position: relative; flex: 1; min-width: 200px;">
                        <label style="font-size: 0.8rem; font-weight: 700; color: var(--color-azul); text-transform: uppercase; letter-spacing: 0.04em;">📦 Material</label>
                        <input type="text" id="filtro-material-input" placeholder="Buscar material..." style="padding: 9px 12px; border: 1px solid var(--color-borde); border-radius: 6px; font-size: 0.9rem; outline: none; width: 100%;" oninput="renderizarDropdownBusqueda('material')" onfocus="renderizarDropdownBusqueda('material')" autocomplete="off">
                        <ul id="lista-busqueda-material" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ddd; border-radius: 4px; max-height: 200px; overflow-y: auto; z-index: 1000; margin: 4px 0 0 0; padding: 0; list-style: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"></ul>
                        <input type="hidden" id="filtro-material">
                    </div>
                    <div class="filtro-acciones" style="align-self:flex-end;">
                        <button class="btn-filtro btn-filtro-reset" onclick="resetearFiltros()">Limpiar filtros</button>
                    </div>
                </div>

                <!-- LISTA DE TICKETS -->
                <div class="tabla-card">
                    <div class="tabla-header">
                        <svg class="icono-titulo" viewBox="0 0 24 24">
                            <line x1="16.5" y1="9.4" x2="7.5" y2="4.21" />
                            <path
                                d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96" />
                            <line x1="12" y1="22.08" x2="12" y2="12" />
                        </svg>
                        <span>Tickets de Suministros</span>
                        <span id="contador-resultados"
                            style="margin-left:auto; font-size:0.8rem; font-weight:400; color:var(--color-muted);"></span>
                    </div>
                    <div class="ticket-list-grid-header">
                        <span>Proveedor</span>
                        <span>Fecha</span>
                        <span>Info. Ticket</span>
                        <span style="text-align: right;">Acciones</span>
                    </div>
                    
                    <div id="lista-tickets" class="tickets-lista" style="padding: 0 16px 16px;"></div>
                    
                    <!-- Contenedor para controles de paginación -->
                    <div id="paginacion-contenedor" style="padding: 0 16px 16px;"></div>
                </div>
            </main>
        </div>
    </div>

    <!-- Script Principal -->
    <script src="paginacion.js"></script>
    <script src="supplies.js"></script>
    <script src="sidebar.js"></script>
</body>

</html>