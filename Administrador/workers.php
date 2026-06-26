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

    <title>Gestión de Trabajadores — PanelAdmin</title>

    <link rel="stylesheet" href="../estilos.css">
</head>

<body>
    <div id="notificacion" class="notificacion" style="display:none;"></div>

    <div id="modal-trabajador" class="modal-overlay" style="display:none;">
        <div class="modal-contenido">
            <div class="modal-header">
                <h2 id="modal-titulo" class="modal-titulo" style="color: #3e8cdd;">Agregar Nuevo Trabajador</h2>
                <button class="btn-cerrar-modal" onclick="cerrarModal()" title="Cerrar">×</button>
            </div>
            <form id="form-trabajador" onsubmit="guardarTrabajador(event)">
                <div class="form-grid">
                    <!-- Ocultamos el campo ID para guardarlo durante la edición pero no es editable -->
                    <input type="hidden" id="campo-id" name="campo-id">

                    <div class="form-grupo form-grupo-completo">
                        <label for="campo-nombre">Nombre Completo *</label>
                        <input type="text" id="campo-nombre" name="campo-nombre" required>
                    </div>

                    <div class="form-grupo">
                        <label for="campo-puesto">Puesto *</label>
                        <select id="campo-puesto" name="campo-puesto" required onchange="actualizarSalarioSelect()">
                            <option value="" disabled selected>Selecciona un puesto</option>
                            <!-- Se llenará dinámicamente con JS -->
                        </select>
                    </div>

                    <div class="form-grupo">
                        <label for="campo-salario">Salario Mensual *</label>
                        <input type="number" id="campo-salario" name="campo-salario" step="0.01" required>
                    </div>

                    <div class="form-grupo">
                        <label for="campo-email">Email *</label>
                        <input type="email" id="campo-email" name="campo-email" required>
                    </div>

                    <div class="form-grupo">
                        <label for="campo-telefono">Teléfono *</label>
                        <input type="text" id="campo-telefono" name="campo-telefono" required>
                    </div>
                </div>

                <div class="form-botones">
                    <button type="button" class="btn btn-cancelar" onclick="cerrarModal()">Cancelar</button>
                    <button type="submit" class="btn btn-guardar" style="background-color: #3e8cdd;">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="layout">
        <div id="sidebar"></div>

        <div class="contenido-principal">
            <header class="header">
                <span>Gestión de Trabajadores</span>
            </header>

            <main class="area-pagina">
                <div class="pagina-header">
                    <div>
                        <h1 class="pagina-titulo" style="color: #3e8cdd;">Gestión de Trabajadores</h1>
                        <p class="pagina-subtitulo">Administra la información de los empleados</p>
                    </div>
                    <button class="btn btn-agregar-principal" style="background-color: #74cb3c;"
                        onclick="abrirModalNuevo()">
                        <svg class="icono-btn" viewBox="0 0 24 24" style="stroke: white; fill: none; stroke-width: 2;">
                            <line x1="12" y1="5" x2="12" y2="19" />
                            <line x1="5" y1="12" x2="19" y2="12" />
                        </svg>
                        <span style="color: white; font-weight: 500;">Agregar Trabajador</span>
                    </button>
                </div>

                <div class="stats-grid">
                    <div class="stat-card" style="border: 2px solid #3e8cdd;">
                        <div class="stat-label" style="color: #3e8cdd;">Total Empleados</div>
                        <div id="stat-total" class="stat-valor" style="color: #3e8cdd;">0</div>
                    </div>
                    <div class="stat-card" style="border: 2px solid #3e8cdd;">
                        <div class="stat-label" style="color: #3e8cdd;">Nómina Total</div>
                        <div id="stat-nomina" class="stat-valor" style="color: #3e8cdd;">$0.00</div>
                    </div>
                    <div class="stat-card" style="border: 2px solid #74cb3c;">
                        <div class="stat-label" style="color: #74cb3c;">Salario Promedio</div>
                        <div id="stat-promedio" class="stat-valor" style="color: #74cb3c;">$0.00</div>
                    </div>
                </div>

                <!-- FILTROS -->
                <div class="filtro-panel" style="background: #fff; border-radius: 0.625rem; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.07); padding: 18px 24px; margin-bottom: 24px; display: flex; align-items: flex-start; flex-wrap: wrap; gap: 20px;">
                    <div style="width: 100%; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px; margin-bottom: 4px;">
                        <span style="font-weight: 700; color: #3e8cdd; font-size: 0.95rem; display: flex; align-items: center; gap: 6px;">🔍 Filtros</span>
                    </div>
                    
                    <div class="filtro-grupo" style="display: flex; flex-direction: column; gap: 6px; min-width: 200px; position: relative; flex: 1;">
                        <label style="font-size: 0.8rem; font-weight: 700; color: #3e8cdd; text-transform: uppercase; letter-spacing: 0.04em;">👤 Nombre del Trabajador</label>
                        <input type="text" id="filtro-nombre" placeholder="Buscar por nombre..." style="padding: 9px 12px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 0.9rem; color: #1e293b; background-color: #f9f9fb; outline: none; width: 100%;" oninput="filtrarTrabajadores('nombre')" onkeyup="filtrarTrabajadores('nombre')" autocomplete="off">
                        <ul id="lista-busqueda-nombre" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ddd; border-radius: 4px; max-height: 200px; overflow-y: auto; z-index: 1000; margin: 4px 0 0 0; padding: 0; list-style: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"></ul>
                    </div>
                    
                    <div class="filtro-grupo" style="display: flex; flex-direction: column; gap: 6px; min-width: 200px; position: relative; flex: 1;">
                        <label style="font-size: 0.8rem; font-weight: 700; color: #3e8cdd; text-transform: uppercase; letter-spacing: 0.04em;">💼 Puesto</label>
                        <select id="filtro-puesto" onchange="filtrarTrabajadores('puesto')" style="padding: 9px 12px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 0.9rem; color: #1e293b; background-color: #f9f9fb; outline: none; width: 100%; cursor: pointer;">
                            <option value="">Todos los puestos</option>
                        </select>
                    </div>
                    
                    <div style="display: flex; align-items: flex-end; align-self: flex-end;">
                        <button style="padding: 9px 18px; border: none; border-radius: 6px; font-size: 0.875rem; font-weight: 600; cursor: pointer; background-color: #f1f5f9; color: #1e293b;" onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'" onclick="limpiarFiltros()">Limpiar filtros</button>
                    </div>
                </div>

                <div class="tabla-card"
                    style="border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);">
                    <div class="tabla-header"
                        style="color: #3e8cdd; border-bottom: 1px solid #e2e8f0; padding: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <svg class="icono-titulo" viewBox="0 0 24 24"
                                style="width: 24px; height: 24px; stroke: currentColor; fill: none; stroke-width: 2;">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                            <span style="font-size: 1.2rem; font-weight: 600;">Lista de Trabajadores</span>
                        </div>
                    </div>
                    <div class="tabla-contenedor">
                        <table class="tabla">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Puesto</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Salario</th>
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

    <script src="paginacion.js"></script>
    <script src="workers.js"></script>
    <script src="sidebar.js"></script>
</body>

</html>