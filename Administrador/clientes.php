<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../Home/Home.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clientes - Panel Administrador</title>
    
    <!-- Mismos iconos que las otras vistas -->
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="manifest" href="site.webmanifest">

    <!-- CSS Base del admin -->
    <link rel="stylesheet" href="../estilos.css">
    <!-- Estilos específicos locales opcionales, pero reusamos inline del workers.php -->
    
    <style>
        /* ESTILOS DEL MODAL (Subvista estilo MyPurchases) */
        .modal-bg {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 1000;
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
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .2);
            position: relative;
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #3e8cdd;
            margin-bottom: 1rem;
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

        .subvista-tabs {
            display: flex;
            gap: 1rem;
            border-bottom: 2px solid #e5e7eb;
            margin-bottom: 1rem;
        }

        .subvista-tab {
            padding: 0.5rem 1rem;
            cursor: pointer;
            font-weight: 600;
            color: #6b7280;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
        }

        .subvista-tab.active {
            color: #3e8cdd;
            border-bottom-color: #3e8cdd;
        }

        /* Tablas dentro del modal */
        .subvista-tabla {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        .subvista-tabla th, .subvista-tabla td {
            text-align: left;
            padding: 12px 15px;
            border-bottom: 1px solid #e2e8f0;
        }

        .subvista-tabla th {
            background-color: #f8fafc;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }
        
        .subvista-tabla tr:hover {
            background-color: #f1f5f9;
        }

        .modal-pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 15px;
        }

        .btn-page {
            padding: 5px 10px;
            border: 1px solid #e2e8f0;
            background: white;
            cursor: pointer;
            border-radius: 4px;
        }

        .btn-page:hover:not(:disabled) {
            background: #f1f5f9;
        }

        .btn-page:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        /* ── ESTILOS DE TICKETS (idéntico a ventas.php) ── */
        .ticket-list-container {
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
        .ticket-card:hover { box-shadow: 0 4px 12px rgba(0, 0, 0, 0.10); }
        .ticket-card-header {
            display: grid;
            grid-template-columns: 2fr 1fr 1.5fr 150px;
            gap: 15px;
            align-items: center;
            padding: 14px 20px;
            background: linear-gradient(90deg, #f8faff 0%, #fff 100%);
            cursor: pointer;
        }
        .ticket-card-header:hover { background: #f0f7ff; }
        .ticket-id { font-size: 0.95rem; font-weight: 700; color: #3e8cdd; min-width: 56px; }
        .ticket-fecha { font-size: 0.88rem; color: #64748b; display: flex; align-items: center; gap: 6px; }
        .ticket-total { font-weight: 700; font-size: 1rem; color: #74cb3c; }
        .btn-ver-mas {
            color: #3e8cdd;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 6px 12px;
            background: #eef5ff;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.2s;
            user-select: none;
            -webkit-user-select: none;
        }
        .btn-ver-mas:hover { background: #dceafc; }
        
        .ticket-detalle {
            display: none;
            border-top: 1px solid #e2e8f0;
            padding: 16px 20px;
            background: #fafbff;
            animation: slideDown 0.2s ease;
        }
        .ticket-detalle.abierto { display: block; }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
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
            color: #64748b;
            text-transform: uppercase;
            padding: 6px 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        .detalle-tabla td {
            padding: 8px 10px;
            font-size: 0.88rem;
            color: #1e293b;
            border-bottom: 1px solid #f0f2f7;
        }
        .detalle-tabla tr:last-child td { border-bottom: none; }
        .detalle-total-row { font-weight: 700; color: #74cb3c; }
    </style>
</head>

<body>
    <!-- El sidebar y el layout se inyectan dinámicamente vía sidebar.js -->

    <div class="layout">
        <div id="sidebar"></div>

        <div class="contenido-principal">
            <header class="header">
                <span>Gestión de Clientes</span>
            </header>

            <main class="area-pagina">
                <!-- CABECERA -->
                <div class="pagina-header">
                    <div>
                        <h1 class="pagina-titulo" style="color: #3e8cdd;">Gestión de Clientes</h1>
                        <p class="pagina-subtitulo">Administra la información y visualiza compras de clientes</p>
                    </div>
                </div>

                <!-- ESTADÍSTICAS -->
                <div class="stats-grid">
                    <div class="stat-card" style="border: 2px solid #3e8cdd;">
                        <div class="stat-label" style="color: #3e8cdd;">Total Clientes</div>
                        <div id="stat-total-clientes" class="stat-valor" style="color: #3e8cdd;">0</div>
                    </div>
                </div>

                <!-- FILTROS (Formato exacto workers.php) -->
                <div class="filtro-panel" style="background: #fff; border-radius: 0.625rem; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.07); padding: 18px 24px; margin-bottom: 24px; display: flex; align-items: flex-start; flex-wrap: wrap; gap: 20px;">
                    <div style="width: 100%; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px; margin-bottom: 4px;">
                        <span style="font-weight: 700; color: #3e8cdd; font-size: 0.95rem; display: flex; align-items: center; gap: 6px;">🔍 Filtros</span>
                    </div>
                    
                    <div class="filtro-grupo" style="display: flex; flex-direction: column; gap: 6px; min-width: 200px; position: relative; flex: 1;">
                        <label style="font-size: 0.8rem; font-weight: 700; color: #3e8cdd; text-transform: uppercase; letter-spacing: 0.04em;">👤 Nombre del Cliente</label>
                        <input type="text" id="filtro-nombre-cliente" placeholder="Buscar por nombre..." style="padding: 9px 12px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 0.9rem; color: #1e293b; background-color: #f9f9fb; outline: none; width: 100%;" oninput="renderizarDropdownBusqueda('cliente')" onfocus="renderizarDropdownBusqueda('cliente')" autocomplete="off">
                        <ul id="lista-busqueda-cliente" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ddd; border-radius: 4px; max-height: 200px; overflow-y: auto; z-index: 1000; margin: 4px 0 0 0; padding: 0; list-style: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"></ul>
                    </div>
                    
                    <div style="display: flex; align-items: flex-end; align-self: flex-end;">
                        <button style="padding: 9px 18px; border: none; border-radius: 6px; font-size: 0.875rem; font-weight: 600; cursor: pointer; background-color: #f1f5f9; color: #1e293b;" onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'" onclick="limpiarFiltros()">Limpiar filtros</button>
                    </div>
                </div>

                <!-- TABLA DE CLIENTES -->
                <div class="tabla-card"
                    style="border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);">
                    <div class="tabla-header"
                        style="color: #3e8cdd; border-bottom: 1px solid #e2e8f0; padding: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <svg class="icono-titulo" viewBox="0 0 24 24"
                                style="width: 24px; height: 24px; stroke: currentColor; fill: none; stroke-width: 2;">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                            <span style="font-size: 1.2rem; font-weight: 600;">Lista de Clientes</span>
                        </div>
                    </div>
                    <div class="tabla-contenedor">
                        <table class="tabla">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Correo electrónico</th>
                                    <th>Teléfono</th>
                                    <th>Constancia fiscal</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="cuerpo-tabla">
                                <!-- Filas JS -->
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Contenedor para controles de paginación -->
                    <div id="paginacion-contenedor"></div>
                </div>
            </main>
        </div>
    </div>

    <!-- MODAL "Ver Más" (Subvista de compras y facturas) -->
    <div class="modal-bg" id="modalBg">
        <div class="modal">
            <div class="modal-title" id="mTitle">Detalles del Cliente</div>
            
            <div class="subvista-tabs">
                <div class="subvista-tab active" id="tab-compras" onclick="cambiarPestana('compras')">Compras</div>
                <div class="subvista-tab" id="tab-facturas" onclick="cambiarPestana('facturas')">Facturas</div>
            </div>

            <!-- Contenedor de Compras -->
            <div id="contenedor-compras">
                <div class="ticket-list-grid-header" style="display: grid; grid-template-columns: 2fr 1fr 1.5fr 150px; gap: 15px; padding: 10px 20px; font-size: 0.85rem; font-weight: 700; color: #64748b; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; margin-bottom: 10px;">
                    <span>Info. Ticket</span>
                    <span>Fecha</span>
                    <span>Total</span>
                    <span style="text-align: right;">Acciones</span>
                </div>
                <div id="lista-compras-tickets" class="ticket-list-container">
                    <!-- Llenado vía JS -->
                </div>
                <div class="modal-pagination" id="paginacion-compras"></div>
            </div>

            <!-- Contenedor de Facturas -->
            <div id="contenedor-facturas" style="display: none;">
                <table class="subvista-tabla">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Fecha</th>
                            <th>Uso CFDI</th>
                            <th>PDF</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpo-tabla-facturas">
                        <!-- Llenado vía JS -->
                    </tbody>
                </table>
                <div class="modal-pagination" id="paginacion-facturas"></div>
            </div>

            <button class="modal-close" onclick="cerrarModal()">Cerrar</button>
        </div>
    </div>

    <!-- MODAL PARA VER PDF (Iframe dinámico) -->
    <div id="pdfModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.6); align-items:center; justify-content:center;">
        <div style="background:#fff; width:90%; max-width:900px; height:85vh; border-radius:8px; display:flex; flex-direction:column; overflow:hidden;">
            <div style="padding:15px; background:#3e8cdd; color:white; display:flex; justify-content:space-between; align-items:center;">
                <h3 style="margin:0; font-size:1.1rem;">Visor de Documento</h3>
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

    <script src="paginacion.js"></script>
    <script src="clientes.js?v=2"></script>
    <script src="sidebar.js"></script>
</body>
</html>
