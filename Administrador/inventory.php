<?php require_once '../PHP/auth.php'; auth_require_rol('admin'); ?>
<!DOCTYPE html>
<!--
    ============================================================
    inventory.php — Gestión de Materiales (Inventario)
    ============================================================

    ¿QUÉ ES ESTE ARCHIVO?
    Es la página del inventario de gf_materiales del sistema.
    Equivale al componente Inventory.tsx de React.

    ESTRUCTURA DE LA PÁGINA:
    ┌──────────────────────────────────────────────────────────┐
    │  HEADER (barra superior con título y botón "Agregar")   │
    ├──────────┬───────────────────────────────────────────────┤
    │          │  Título + subtítulo + botón "Agregar"        │
    │ SIDEBAR  ├───────────────────────────────────────────────┤
    │ (menú   │  ESTADÍSTICAS: [Total] [ValorTotal] [Áreas]  │
    │ lateral) ├───────────────────────────────────────────────┤
    │          │  TABLA: ID, Nombre, Área, Stock, Precio...   │
    └──────────┴───────────────────────────────────────────────┘

    + MODAL (ventana emergente) para agregar/editar gf_materiales

    ARCHIVOS QUE NECESITA:
    - styles.css    → Estilos del layout y nuevos estilos de tabla/modal
    - inventory.js  → Lógica CRUD, datos y funciones JS
    ============================================================
-->
<html lang="es">

<head>
    <!-- ============================================================
         FAVICON — Igual que en dashboard.php (obligatorio)
         Estos son los íconos que aparecen en la pestaña del navegador
         ============================================================ -->
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="manifest" href="site.webmanifest">

    <!-- Codificación de caracteres: permite usar ñ, á, é, ú, etc. -->
    <meta charset="UTF-8">

    <!-- Responsive: hace que la página se vea bien en celulares -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Gestión de Materiales — PanelAdmin</title>

    <!-- Enlaces a CSS combinado -->
    <link rel="stylesheet" href="../estilos.css">
</head>

<body>

    <!-- ============================================================
         NOTIFICACIÓN FLOTANTE (equivalente al toast de React/Sonner)
         Aparece arriba a la derecha cuando se agrega, edita o elimina.
         Comienza oculto (display:none) y JS lo muestra con mostrarNotificacion()
         ============================================================ -->
    <div id="notificacion" class="notificacion" style="display:none;"></div>


    <!-- ============================================================
         MODAL (equivalente a <Dialog> en React)
         ============================================================
         En React, el Dialog usaba un estado isDialogOpen (true/false)
         para mostrarse u ocultarse.

         Aquí el modal es un <div> que:
         - Normalmente está oculto: display: none
         - Se muestra cuando JS hace: modal.style.display = 'flex'
         - El fondo oscuro es el propio div modal con position:fixed

         PARTES DEL MODAL:
         1. modal-inventario → El fondo oscuro (overlay)
         2. modal-contenido  → La caja blanca del centro
         3. modal-titulo     → Título (cambia entre "Agregar" y "Editar")
         4. formulario       → Los campos de entrada de datos
         ============================================================ -->
    <div id="modal-inventario" class="modal-overlay" style="display:none;">

        <div class="modal-contenido">

            <!-- Encabezado del modal con título y botón cerrar -->
            <div class="modal-header">
                <h2 id="modal-titulo" class="modal-titulo">Agregar Nuevo Material</h2>
                <!--
                    El botón × (cerrar) llama a cerrarModal() en JS.
                    En React, esto era: onOpenChange={setIsDialogOpen}
                -->
                <button class="btn-cerrar-modal" onclick="cerrarModal()" title="Cerrar">×</button>
            </div>

            <!-- ================================================
                 FORMULARIO DEL MODAL
                 ================================================
                 onsubmit="guardarMaterial(event)"
                 → Cuando el usuario hace clic en "Guardar",
                   el formulario llama a la función guardarMaterial()
                   y le pasa el evento como argumento.

                 required = el campo es obligatorio (HTML nativo).
                 No se necesita JS para validar campos vacíos.
                 ================================================ -->
            <form id="form-material" onsubmit="guardarMaterial(event)" enctype="multipart/form-data">

                <!-- Fila 1: Nombre + Área (2 columnas en pantallas grandes) -->
                <div class="form-grid">

                    <div class="form-grupo">
                        <label for="campo-nombre">Nombre del Material *</label>
                        <input type="text" id="campo-nombre" name="nombre"
                            placeholder="Ej: Clavos de concreto 2&quot;" required>
                    </div>

                    <div class="form-grupo">
                        <label for="campo-area">Área *</label>
                        <!--
                            name="area" debe coincidir exactamente con $_POST['area'] en el PHP.
                            Los valores (Carpinteria, Plomeria...) coinciden con los datos en BD.
                        -->
                        <select id="campo-area" name="area" required>
                            <option value="">Selecciona un área…</option>
                            <option value="Carpinteria">Carpintería</option>
                            <option value="Electricidad">Electricidad</option>
                            <option value="Plomeria">Plomería</option>
                            <option value="Pintura">Pintura</option>
                        </select>
                    </div>

                    <div class="form-grupo">
                        <label for="campo-existencias">Existencias *</label>
                        <input type="number" id="campo-existencias" name="existencias" min="0"
                            placeholder="Ej: 150" required>
                    </div>

                    <div class="form-grupo">
                        <label for="campo-precio">Precio Unitario *</label>
                        <input type="number" id="campo-precio" name="precio_unitario" min="0" step="0.01"
                            placeholder="Ej: 2.00" required>
                    </div>

                    <!-- Descripción ocupa las 2 columnas completas -->
                    <div class="form-grupo form-grupo-completo">
                        <label for="campo-descripcion">Descripción *</label>
                        <input type="text" id="campo-descripcion" name="descripcion"
                            placeholder="Ej: Clavos de acero endurecido" required>
                    </div>

                </div>
                <!-- FIN form-grid -->

                <!-- ─── Imagen del material ────────────────────────────────────
                     En ALTA:   obligatoria (required), input tipo file.
                     En EDICIÓN: opcional; se muestra la imagen actual como preview.
                     El campo oculto imagen_actual conserva el nombre existente
                     en BD si el usuario no sube una imagen nueva.
                ─────────────────────────────────────────────────────────────── -->
                <div class="form-grupo form-grupo-completo" id="bloque-imagen">
                    <label for="campo-imagen">Imagen del Material</label>

                    <!-- Preview de la imagen (visible solo en modo edición) -->
                    <div id="preview-imagen-contenedor" style="display:none;margin-bottom:10px;">
                        <img id="preview-imagen"
                             src=""
                             alt="Imagen actual del material"
                             style="width:80px;height:80px;object-fit:contain;border:1px solid #e5e7eb;
                                    border-radius:6px;background:#f9f9f9;padding:4px;">
                        <p id="preview-imagen-nombre"
                           style="font-size:0.78rem;color:#6b7280;margin-top:4px;"></p>
                    </div>

                    <!-- Input de archivo para imagen nueva -->
                    <input type="file" id="campo-imagen" name="imagen"
                           accept="image/jpeg,image/png,image/webp"
                           onchange="previewImagenNueva(this)">
                    <p style="font-size:0.78rem;color:#6b7280;margin-top:4px;">
                        Formatos: JPG, PNG, WebP · Máximo 5 MB.
                    </p>

                    <!--
                        PERSISTENCIA DE IMAGEN:
                        Almacena el nombre del archivo que ya existe en la BD.
                        Si el usuario no sube imagen nueva en la edición,
                        el servidor usará este valor para no sobrescribir la columna imagen.
                    -->
                    <input type="hidden" id="campo-imagen-actual" name="imagen_actual" value="">
                </div>

                <div class="form-botones">
                    <!--
                        type="button" → NO envía el formulario, solo ejecuta onclick.
                        type="submit" → SÍ envía el formulario (activa el onsubmit).
                    -->
                    <button type="button" class="btn btn-cancelar" onclick="cerrarModal()">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-guardar">
                        Guardar
                    </button>
                </div>

            </form>

        </div>
        <!-- FIN modal-contenido -->

    </div>
    <!-- FIN modal-inventario -->


    <!-- ============================================================
         LAYOUT PRINCIPAL (igual que en dashboard.php)
         Sidebar a la izquierda + Contenido a la derecha
         ============================================================ -->
    <div class="layout">

        <!-- SIDEBAR (Barra lateral de navegación) -->
        <div id="sidebar"></div>
        <!-- FIN SIDEBAR -->


        <!-- ZONA DE CONTENIDO PRINCIPAL -->
        <div class="contenido-principal">

            <!-- Header superior -->
            <header class="header">
                <span>Gestión de Materiales — Inventario</span>
            </header>

            <!-- Área principal del contenido -->
            <main class="area-pagina">

                <!-- ============================================
                     ENCABEZADO DE LA PÁGINA + BOTÓN AGREGAR
                     Equivale al bloque de React:
                     <div className="flex items-center justify-between">
                       <h1>...</h1>
                       <Button onClick={...}>Agregar Material</Button>
                     </div>
                     ============================================ -->
                <div class="pagina-header">

                    <div>
                        <h1 class="pagina-titulo">
                            Gestión de Materiales (Inventario)
                        </h1>
                        <p class="pagina-subtitulo">
                            Control y administración del inventario
                        </p>
                    </div>

                    <!--
                        Al hacer clic se llama abrirModalNuevo() en JS.
                        Eso limpia el formulario y abre el modal en modo "nuevo".
                    -->
                    <button class="btn btn-agregar-principal" onclick="abrirModalNuevo()">
                        <!-- Ícono + (Plus) como SVG simple -->
                        <svg class="icono-btn" viewBox="0 0 24 24">
                            <line x1="12" y1="5" x2="12" y2="19" />
                            <line x1="5" y1="12" x2="19" y2="12" />
                        </svg>
                        Agregar Material
                    </button>

                </div>
                <!-- FIN pagina-header -->


                <!-- ============================================
                     TARJETAS DE ESTADÍSTICAS
                     Equivale a la sección "grid md:grid-cols-3" de React.
                     Los valores se actualizan desde JS con:
                     document.getElementById('stat-xxx').textContent = valor
                     ============================================ -->
                <div class="stats-grid">

                    <!-- Stat 1: Total de ítems -->
                    <div class="stat-card stat-azul">
                        <div class="stat-label">Total de Ítems</div>
                        <div id="stat-total" class="stat-valor stat-valor-azul">0</div>
                    </div>

                    <!-- Stat 2: Valor total del inventario -->
                    <div class="stat-card stat-verde">
                        <div class="stat-label">Valor Total del Inventario</div>
                        <div id="stat-valor" class="stat-valor stat-valor-verde">$0</div>
                    </div>

                    <!-- Stat 3: Áreas únicas -->
                    <div class="stat-card stat-azul">
                        <div class="stat-label">Áreas Registradas</div>
                        <div id="stat-areas" class="stat-valor stat-valor-azul">0</div>
                    </div>

                </div>
                <!-- FIN stats-grid -->


                <!-- ============================================
                     FILTROS DE INVENTARIO
                     ============================================ -->
                <div class="filtro-panel" style="background: #fff; border-radius: 0.625rem; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.07); padding: 18px 24px; margin-bottom: 24px; display: flex; align-items: flex-start; flex-wrap: wrap; gap: 20px;">
                    <div style="width: 100%; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px; margin-bottom: 4px;">
                        <span style="font-weight: 700; color: #3e8cdd; font-size: 0.95rem; display: flex; align-items: center; gap: 6px;">🔍 Filtros</span>
                    </div>

                    <!-- Filtro Material (Buscador) -->
                    <div class="filtro-grupo" style="display: flex; flex-direction: column; gap: 6px; min-width: 200px; position: relative; flex: 1;">
                        <label style="font-size: 0.8rem; font-weight: 700; color: #3e8cdd; text-transform: uppercase; letter-spacing: 0.04em;">📦 Material</label>
                        <input type="text" id="filtro-material-input" placeholder="Buscar material..." style="padding: 9px 12px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 0.9rem; color: #1e293b; background-color: #f9f9fb; outline: none; width: 100%;" oninput="renderizarDropdownBusqueda('material')" onfocus="renderizarDropdownBusqueda('material')" autocomplete="off">
                        <ul id="lista-busqueda-material" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ddd; border-radius: 4px; max-height: 200px; overflow-y: auto; z-index: 1000; margin: 4px 0 0 0; padding: 0; list-style: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"></ul>
                        <input type="hidden" id="filtro-material">
                    </div>

                    <!-- Filtro Área -->
                    <div class="filtro-grupo" style="display: flex; flex-direction: column; gap: 6px; min-width: 200px; position: relative; flex: 1;">
                        <label style="font-size: 0.8rem; font-weight: 700; color: #3e8cdd; text-transform: uppercase; letter-spacing: 0.04em;">🏷️ Área</label>
                        <select id="filtro-area" onchange="aplicarFiltro()" style="padding: 9px 12px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 0.9rem; color: #1e293b; background-color: #f9f9fb; outline: none; width: 100%; cursor: pointer;">
                            <option value="">Todas las áreas</option>
                            <option value="Carpinteria">Carpintería</option>
                            <option value="Electricidad">Electricidad</option>
                            <option value="Plomeria">Plomería</option>
                            <option value="Pintura">Pintura</option>
                        </select>
                    </div>

                    <!-- Filtro Stock Mínimo -->
                    <div class="filtro-grupo" style="display: flex; flex-direction: column; gap: 6px; min-width: 200px; position: relative; flex: 1;">
                        <label style="font-size: 0.8rem; font-weight: 700; color: #3e8cdd; text-transform: uppercase; letter-spacing: 0.04em;">⚠️ Stock bajo (≤)</label>
                        <input type="number" id="filtro-stock" placeholder="Ej: 10" min="0" onchange="aplicarFiltro()" style="padding: 9px 12px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 0.9rem; color: #1e293b; background-color: #f9f9fb; outline: none; width: 100%;">
                    </div>

                    <!-- Botones -->
                    <div style="display: flex; align-items: flex-end; align-self: flex-end;">
                        <button style="padding: 9px 18px; border: none; border-radius: 6px; font-size: 0.875rem; font-weight: 600; cursor: pointer; background-color: #f1f5f9; color: #1e293b;" onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'" onclick="resetearFiltros()">Limpiar filtros</button>
                    </div>
                </div>

                <!-- ============================================
                     TARJETA DE LA TABLA DE MATERIALES
                     Equivale al componente <Card> que envuelve la <Table>
                     ============================================ -->
                <div class="tabla-card">

                    <div class="tabla-header">
                        <!-- Ícono de cajas (Boxes de lucide-react) como SVG -->
                        <svg class="icono-titulo" viewBox="0 0 24 24">
                            <path
                                d="M2.97 12.92A2 2 0 0 0 2 14.63v3.24a2 2 0 0 0 .97 1.71l3 1.8a2 2 0 0 0 2.06 0L12 19v-5.5l-5-3-4.03 2.42Z" />
                            <path d="m7 16.5-4.74-2.85" />
                            <path d="m7 16.5 5-3" />
                            <path d="M7 16.5v5.17" />
                            <path
                                d="M12 13.5V19l3.97 2.38a2 2 0 0 0 2.06 0l3-1.8a2 2 0 0 0 .97-1.71v-3.24a2 2 0 0 0-.97-1.71L17 10.5l-5 3Z" />
                            <path d="m17 16.5-5-3" />
                            <path d="m17 16.5 4.74-2.85" />
                            <path d="M17 16.5v5.17" />
                            <path
                                d="M7.97 4.42A2 2 0 0 0 7 6.13v4.37l5 3 5-3V6.13a2 2 0 0 0-.97-1.71l-3-1.8a2 2 0 0 0-2.06 0l-3 1.8Z" />
                            <path d="M12 8 7.26 5.15" />
                            <path d="m12 8 4.74-2.85" />
                            <path d="M12 13.5V8" />
                        </svg>
                        <span>Inventario de Materiales</span>
                    </div>

                    <!-- Contenedor con scroll horizontal para pantallas pequeñas -->
                    <div class="tabla-contenedor">

                        <!--
                            La tabla HTML equivale exactamente a <Table> de React.
                            <thead> = TableHeader  → Encabezados de columna
                            <tbody> = TableBody    → Filas de datos (id="cuerpo-tabla")
                            <tr>    = TableRow     → Una fila
                            <th>    = TableHead    → Celda de encabezado
                            <td>    = TableCell    → Celda de datos
                        -->
                        <table class="tabla">
                            <!-- Proporciones de columna -->
                            <colgroup>
                                <col style="width: 60px;">        <!-- ID -->
                                <col style="width: 16%;">         <!-- Nombre -->
                                <col style="width: 110px;">       <!-- Área -->
                                <col style="width: 100px;">       <!-- Existencias -->
                                <col style="width: 100px;">       <!-- Precio -->
                                <col>                             <!-- Descripción (toma el resto) -->
                                <col style="width: 90px;">        <!-- Acciones -->
                            </colgroup>

                            <!-- Encabezados de la tabla (fijos, escritos en HTML) -->
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Área</th>
                                    <th>Existencias</th>
                                    <th>Precio Unit.</th>
                                    <th>Descripción</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>

                            <!-- Cuerpo de la tabla (VACÍO en HTML, JS lo llena dinámicamente) -->
                            <tbody id="cuerpo-tabla">
                                <!-- Filas generadas por JS -->
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Contenedor para controles de paginación -->
                    <div id="paginacion-contenedor"></div>

                </div>
                <!-- FIN tabla-card -->

            </main>
            <!-- FIN area-pagina -->

        </div>
        <!-- FIN contenido-principal -->

    </div>
    <!-- FIN layout -->


    <!--
        CARGA DEL JAVASCRIPT AL FINAL DEL BODY.
        Se pone aquí (no en el <head>) para que cuando JS se ejecute,
        todos los elementos HTML (la tabla, los stats, el modal) ya existan.
    -->
    <script src="paginacion.js"></script>
    <script src="inventory.js"></script>
    <script src="sidebar.js"></script>

</body>

</html>