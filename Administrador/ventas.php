<?php require_once '../PHP/auth.php'; auth_require_rol('admin'); ?>
<!DOCTYPE html>
<!--
    ventas.php — Gestión de Ventas (Administrador)
    Usa el mismo sistema de ticket-cards expandibles que supplies.php
-->
<html lang="es">

<head>
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="manifest" href="site.webmanifest">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Ventas — PanelAdmin</title>

    <link rel="stylesheet" href="../estilos.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        /* ── KPIs: 3 columnas ── */
        .stats-grid-3 {
            grid-template-columns: repeat(3, 1fr);
        }

        @media (max-width: 900px) {
            .stats-grid-3 {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 600px) {
            .stats-grid-3 {
                grid-template-columns: 1fr;
            }
        }

        /* ── Gráficas: 2 columnas ── */
        .graficas-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
            margin-bottom: 28px;
        }

        @media (max-width: 900px) {
            .graficas-grid {
                grid-template-columns: 1fr;
            }
        }

        .grafica-card {
            background: #fff;
            border-radius: 0.625rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.07);
            overflow: hidden;
        }

        .grafica-contenedor {
            padding: 20px 24px;
            height: 320px;
            position: relative;
        }

        .grafica-contenedor canvas {
            width: 100% !important;
            height: 100% !important;
        }

        /* ── Filtros ── */
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

        /* ── Leyenda pie chart ── */
        #leyenda-area {
            padding: 0 24px 16px 24px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .leyenda-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
            color: var(--color-muted);
        }

        .leyenda-color {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        /* ── Ticket Cards (idéntico a supplies) ── */
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

        .ticket-cliente {
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

        .btn-ver-mas,
        .ticket-card-header {
            user-select: none;
            -webkit-user-select: none;
        }

        /* ── stat responsive ── */
        .stat-valor-md {
            font-size: 1.6rem;
        }
    </style>
</head>

<body>

    <div class="layout">

        <!-- SIDEBAR FIJO -->
        <div id="sidebar"></div>

        <!-- CONTENIDO PRINCIPAL -->
        <div class="contenido-principal">

            <header class="header"><span>Gestión de Ventas</span></header>

            <main class="area-pagina">

                <!-- Encabezado -->
                <div class="pagina-header">
                    <div>
                        <h1 class="pagina-titulo">Gestión de Ventas</h1>
                        <p class="pagina-subtitulo">Reportes y análisis de ventas por ticket</p>
                    </div>
                </div>

                <!-- KPIs -->
                <div class="stats-grid stats-grid-3" style="margin-bottom:28px;">
                    <div class="stat-card stat-azul">
                        <div class="stat-label">💰 Ventas Totales</div>
                        <div id="kpi-total" class="stat-valor stat-valor-azul stat-valor-md">$0</div>
                    </div>
                    <div class="stat-card stat-azul">
                        <div class="stat-label">📈 Ticket Promedio</div>
                        <div id="kpi-promedio" class="stat-valor stat-valor-azul stat-valor-md">$0</div>
                    </div>
                    <div class="stat-card stat-verde">
                        <div class="stat-label">🗓️ Ventas del Periodo</div>
                        <div id="kpi-cantidad" class="stat-valor stat-valor-verde">0</div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="filtro-panel">
                    <div class="filtro-titulo-seccion">
                        <span class="filtro-titulo">🔍 Filtros</span>
                    </div>
                    <div class="filtro-grupo">
                        <label>📅 Fecha desde</label>
                        <input type="date" id="filtro-desde" onchange="aplicarFiltro()">
                    </div>
                    <div class="filtro-separador">—</div>
                    <div class="filtro-grupo">
                        <label>📅 Fecha hasta</label>
                        <input type="date" id="filtro-hasta" onchange="aplicarFiltro()">
                    </div>
                    <div class="filtro-grupo" style="position: relative; flex: 1; min-width: 200px;">
                        <label style="font-size: 0.8rem; font-weight: 700; color: var(--color-azul); text-transform: uppercase; letter-spacing: 0.04em;">👤 Cliente</label>
                        <input type="text" id="filtro-cliente-input" placeholder="Buscar cliente..." style="padding: 9px 12px; border: 1px solid var(--color-borde); border-radius: 6px; font-size: 0.9rem; outline: none; width: 100%;" oninput="renderizarDropdownBusqueda('cliente')" onfocus="renderizarDropdownBusqueda('cliente')" autocomplete="off">
                        <ul id="lista-busqueda-cliente" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ddd; border-radius: 4px; max-height: 200px; overflow-y: auto; z-index: 1000; margin: 4px 0 0 0; padding: 0; list-style: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"></ul>
                        <input type="hidden" id="filtro-cliente">
                    </div>
                    <div class="filtro-grupo" style="position: relative; flex: 1; min-width: 200px;">
                        <label style="font-size: 0.8rem; font-weight: 700; color: var(--color-azul); text-transform: uppercase; letter-spacing: 0.04em;">📦 Material</label>
                        <input type="text" id="filtro-material-input" placeholder="Buscar material..." style="padding: 9px 12px; border: 1px solid var(--color-borde); border-radius: 6px; font-size: 0.9rem; outline: none; width: 100%;" oninput="renderizarDropdownBusqueda('material')" onfocus="renderizarDropdownBusqueda('material')" autocomplete="off">
                        <ul id="lista-busqueda-material" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ddd; border-radius: 4px; max-height: 200px; overflow-y: auto; z-index: 1000; margin: 4px 0 0 0; padding: 0; list-style: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"></ul>
                        <input type="hidden" id="filtro-material">
                    </div>
                    <div class="filtro-acciones" style="align-self:flex-end;">
                        <button class="btn-filtro btn-filtro-reset" onclick="resetearFiltro()">Limpiar filtros</button>
                    </div>
                </div>

                <!-- Gráficas -->
                <div class="graficas-grid">
                    <div class="grafica-card">
                        <div class="tabla-header">
                            <svg class="icono-titulo" viewBox="0 0 24 24">
                                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" />
                                <polyline points="17 6 23 6 23 12" />
                            </svg>
                            <span>Tendencia de Ventas por Mes</span>
                        </div>
                        <div class="grafica-contenedor">
                            <canvas id="grafica-tendencia"></canvas>
                        </div>
                    </div>
                    <div class="grafica-card">
                        <div class="tabla-header">
                            <svg class="icono-titulo" viewBox="0 0 24 24">
                                <circle cx="9" cy="21" r="1" />
                                <circle cx="20" cy="21" r="1" />
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                            </svg>
                            <span>Ventas por Área</span>
                        </div>
                        <div class="grafica-contenedor">
                            <canvas id="grafica-area"></canvas>
                        </div>
                        <div id="leyenda-area"></div>
                    </div>
                </div>

                <!-- Lista de tickets (cards expandibles) -->
                <div class="tabla-card">
                    <div class="tabla-header">
                        <svg class="icono-titulo" viewBox="0 0 24 24">
                            <circle cx="9" cy="21" r="1" />
                            <circle cx="20" cy="21" r="1" />
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                        </svg>
                        <span>Registro de Ventas</span>
                        <span id="contador-resultados"
                            style="margin-left:auto; font-size:0.8rem; font-weight:400; color:var(--color-muted);"></span>
                    </div>
                    <div class="ticket-list-grid-header">
                        <span>Cliente</span>
                        <span>Fecha</span>
                        <span>Info. Ticket</span>
                        <span style="text-align: right;">Acciones</span>
                    </div>
                    <!-- AQUI VAN LAS VENTAS DINAMICAS -->
                    <div id="lista-tickets" class="tickets-lista" style="padding: 0 16px 16px;"></div>

                    <!-- Contenedor para controles de paginación -->
                    <div id="paginacion-contenedor" style="padding: 0 16px 16px;"></div>
                </div>

            </main>
        </div>
    </div>

    <!-- Script Principal -->
    <script src="paginacion.js"></script>
    <script src="ventas.js"></script>
    <script src="sidebar.js"></script>
</body>

</html>