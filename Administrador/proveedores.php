<?php require_once '../PHP/auth.php';
auth_require_rol('admin'); ?>
<!DOCTYPE html>
<!--
    ============================================================
    gf_proveedores.php — Gestión de Proveedores
    ============================================================
    Equivale al componente Providers.tsx de React.

    ESTRUCTURA:
    ┌──────────────────────────────────────────────────────────┐
    │  HEADER                                                  │
    ├───────────┬──────────────────────────────────────────────┤
    │           │  Título + botón "Agregar Proveedor"         │
    │ SIDEBAR   ├──────────────────────────────────────────────┤
    │ (fijo)    │  STAT: [Total Proveedores]                  │
    │           ├──────────────────────────────────────────────┤
    │           │  TABLA: ID, Nombre, Contacto, Email...      │
    └───────────┴──────────────────────────────────────────────┘
    + MODAL para agregar / editar gf_proveedores
    ============================================================
-->
<html lang="es">

<head>
    <!-- FAVICON -->
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="manifest" href="site.webmanifest">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Proveedores — PanelAdmin</title>

    <!-- Estilos compartidos del proyecto -->
    <link rel="stylesheet" href="../estilos.css">
</head>

<body>
    <!-- Notificación flotante (equivale al toast.success() de sonner) -->
    <div id="notificacion" class="notificacion" style="display:none;"></div>

    <!-- ============================================================
         MODAL — Agregar / Editar Proveedor
         Equivale a <Dialog> de React. Se muestra/oculta con JS.
         ============================================================ -->
    <div id="modal-proveedores" class="modal-overlay" style="display:none;">
        <div class="modal-contenido">

            <div class="modal-header">
                <!-- El título cambia entre "Agregar Nuevo Proveedor" y "Editar Proveedor" -->
                <h2 id="modal-titulo" class="modal-titulo">Agregar Nuevo Proveedor</h2>
                <button class="btn-cerrar-modal" onclick="cerrarModal()" title="Cerrar">×</button>
            </div>

            <!--
                onsubmit="guardarProveedor(event)" → Llama a la función JS al enviar.
                event.preventDefault() dentro evita que se recargue la página.
            -->
            <form id="form-proveedor" onsubmit="guardarProveedor(event)">

                <div class="form-grid">

                    <div class="form-grupo">
                        <label for="campo-nombre">Nombre de la Empresa *</label>
                        <input type="text" id="campo-nombre" name="campo-nombre"
                            placeholder="Ej: Distribuidora del Norte" required>
                    </div>

                    <div class="form-grupo">
                        <label for="campo-contacto">Persona de Contacto *</label>
                        <input type="text" id="campo-contacto" name="campo-contacto" placeholder="Ej: Juan Pérez"
                            required>
                    </div>

                    <div class="form-grupo">
                        <label for="campo-correo">Email *</label>
                        <input type="email" id="campo-correo" name="campo-correo" placeholder="Ej: contacto@empresa.com"
                            required>
                    </div>

                    <div class="form-grupo">
                        <label for="campo-telefono">Teléfono *</label>
                        <input type="tel" id="campo-telefono" name="campo-telefono" placeholder="Ej: 5551234567"
                            required>
                    </div>

                </div>

                <div class="form-botones">
                    <button type="button" class="btn btn-cancelar" onclick="cerrarModal()">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-guardar">
                        Guardar
                    </button>
                </div>

            </form>
        </div>
    </div>
    <!-- FIN MODAL -->


    <!-- ============================================================
         LAYOUT PRINCIPAL
         ============================================================ -->
    <div class="layout">

        <!-- SIDEBAR FIJO -->
        <div id="sidebar"></div>
        <!-- FIN SIDEBAR -->


        <!-- CONTENIDO PRINCIPAL -->
        <div class="contenido-principal">

            <header class="header">
                <span>Gestión de Proveedores</span>
            </header>

            <main class="area-pagina">

                <!-- Encabezado de la página + botón Agregar -->
                <div class="pagina-header">
                    <div>
                        <h1 class="pagina-titulo">
                            Gestión de Proveedores
                        </h1>
                        <p class="pagina-subtitulo">
                            Administra la información de los proveedores
                        </p>
                    </div>

                    <!-- Botón verde "Agregar Proveedor" (equiv. a <DialogTrigger> en React) -->
                    <button class="btn btn-agregar-principal" onclick="abrirModalNuevo()">
                        <svg class="icono-btn" viewBox="0 0 24 24">
                            <line x1="12" y1="5" x2="12" y2="19" />
                            <line x1="5" y1="12" x2="19" y2="12" />
                        </svg>
                        Agregar Proveedor
                    </button>
                </div>

                <!-- =============================================
                     TARJETA DE ESTADÍSTICAS (solo 1)
                     Se eliminó "Proveedores Registrados" según
                     las instrucciones del usuario.
                     ============================================= -->
                <div class="stats-grid stats-grid-1">

                    <div class="stat-card stat-azul">
                        <div class="stat-label">Total Proveedores</div>
                        <!-- JS actualiza este valor con actualizarEstadisticas() -->
                        <div id="stat-total-proveedores" class="stat-valor stat-valor-azul">0</div>
                    </div>

                </div>

                <!-- FILTROS -->
                <div class="filtro-panel"
                    style="background: #fff; border-radius: 0.625rem; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.07); padding: 18px 24px; margin-bottom: 24px; display: flex; align-items: flex-start; flex-wrap: wrap; gap: 20px;">
                    <div
                        style="width: 100%; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px; margin-bottom: 4px;">
                        <span
                            style="font-weight: 700; color: #3e8cdd; font-size: 0.95rem; display: flex; align-items: center; gap: 6px;">🔍
                            Filtros</span>
                    </div>

                    <div class="filtro-grupo"
                        style="display: flex; flex-direction: column; gap: 6px; min-width: 200px; position: relative; flex: 1;">
                        <label
                            style="font-size: 0.8rem; font-weight: 700; color: #3e8cdd; text-transform: uppercase; letter-spacing: 0.04em;">🏢
                            Nombre del Proveedor</label>
                        <input type="text" id="filtro-nombre" placeholder="Buscar por nombre..."
                            style="padding: 9px 12px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 0.9rem; color: #1e293b; background-color: #f9f9fb; outline: none; width: 100%;"
                            oninput="filtrarProveedores('nombre')" onkeyup="filtrarProveedores('nombre')"
                            autocomplete="off">
                        <ul id="lista-busqueda-nombre"
                            style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ddd; border-radius: 4px; max-height: 200px; overflow-y: auto; z-index: 1000; margin: 4px 0 0 0; padding: 0; list-style: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                        </ul>
                    </div>

                    <div class="filtro-grupo"
                        style="display: flex; flex-direction: column; gap: 6px; min-width: 200px; position: relative; flex: 1;">
                        <label
                            style="font-size: 0.8rem; font-weight: 700; color: #3e8cdd; text-transform: uppercase; letter-spacing: 0.04em;">👤
                            Persona de Contacto</label>
                        <input type="text" id="filtro-contacto" placeholder="Buscar por contacto..."
                            style="padding: 9px 12px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 0.9rem; color: #1e293b; background-color: #f9f9fb; outline: none; width: 100%;"
                            oninput="filtrarProveedores('contacto')" onkeyup="filtrarProveedores('contacto')"
                            autocomplete="off">
                        <ul id="lista-busqueda-contacto"
                            style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ddd; border-radius: 4px; max-height: 200px; overflow-y: auto; z-index: 1000; margin: 4px 0 0 0; padding: 0; list-style: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                        </ul>
                    </div>

                    <div style="display: flex; align-items: flex-end; align-self: flex-end;">
                        <button
                            style="padding: 9px 18px; border: none; border-radius: 6px; font-size: 0.875rem; font-weight: 600; cursor: pointer; background-color: #f1f5f9; color: #1e293b;"
                            onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'"
                            onclick="limpiarBusqueda()">Limpiar filtros</button>
                    </div>
                </div>


                <!-- (stats-grid fue movido arriba) -->


                <!-- TABLA DE PROVEEDORES -->
                <div class="tabla-card">

                    <div class="tabla-header">
                        <!-- Ícono Truck de lucide-react como SVG puro -->
                        <svg class="icono-titulo" viewBox="0 0 24 24">
                            <rect x="1" y="3" width="15" height="13" />
                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8" />
                            <circle cx="5.5" cy="18.5" r="2.5" />
                            <circle cx="18.5" cy="18.5" r="2.5" />
                        </svg>
                        <span>Lista de Proveedores</span>
                    </div>

                    <div class="tabla-contenedor">
                        <table class="tabla">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Contacto</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <!-- gf_proveedores.js llena este <tbody> al cargar -->
                            <tbody id="cuerpo-tabla-proveedores">
                            </tbody>
                        </table>
                    </div>

                    <!-- Contenedor para controles de paginación -->
                    <div id="paginacion-contenedor"></div>

                </div>
                <!-- FIN tabla-card -->

            </main>
        </div>
        <!-- FIN contenido-principal -->

    </div>
    <!-- FIN layout -->

    <script src="paginacion.js"></script>
    <script src="proveedores.js"></script>
    <script src="sidebar.js"></script>
</body>

</html>