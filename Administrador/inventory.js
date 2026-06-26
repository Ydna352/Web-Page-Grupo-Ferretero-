/* ============================================================
   inventory.js — Gestión de Materiales (CRUD con imágenes)
   ============================================================
   Cambios clave vs. versión anterior:
     - Las peticiones POST usan FormData (multipart/form-data)
       en lugar de JSON, para poder enviar el archivo de imagen.
     - El mapa de inventario ahora guarda también el campo `imagen`.
     - abrirEditar() carga la preview de la imagen actual y puebla
       el hidden input `imagen_actual` para garantizar persistencia.
     - guardarMaterial() elige dinámicamente si el campo imagen
       es required (alta) o no (edición, ya tiene imagen).
   ============================================================ */

/* ── Ruta de la API ─────────────────────────────────────────── */
var API_URL = '../PHP/api_materiales.php';

/*
 * ── Ruta base de imágenes de materiales ──────────────────────
 *
 * CONSISTENCIA: Esta variable define el mismo directorio que usa
 * DIR_IMAGENES en api_materiales.php.
 * Tanto inventory.js (admin) como catalogo.html (cliente) deben
 * apuntar a '../imagenes_materiales/' para que la misma imagen
 * sea visible en ambas vistas.
 */
var IMG_BASE_MAT = '../imagenes_materiales/';

/* ── Estado global ───────────────────────────────────────────── */
var inventario       = [];
var materialEditando = null;
var paginaActual     = 1;
var limitePaginacion = 10;

/* ============================================================
   PUNTO DE ENTRADA
   ============================================================ */
window.onload = function () {
    cargarInventario();

    // Marcar sidebar activo
    var enlaces = document.querySelectorAll('.sidebar-nav a');
    for (var i = 0; i < enlaces.length; i++) {
        if (enlaces[i].textContent.indexOf('Materiales') !== -1) {
            enlaces[i].classList.add('activo');
            break;
        }
    }

    // Cerrar modal haciendo clic en el fondo
    document.getElementById('modal-inventario').addEventListener('click', function (e) {
        if (e.target === this) cerrarModal();
    });
};

/* ============================================================
   FILTROS Y AUTOCOMPLETADO
   ============================================================ */
var ignoreDropdown = { material: false };

function renderizarDropdownBusqueda(campo) {
    if (campo !== 'material') return;
    var input = document.getElementById('filtro-material-input');
    var lista = document.getElementById('lista-busqueda-material');
    if (!input || !lista) return;

    if (ignoreDropdown[campo]) {
        ignoreDropdown[campo] = false;
        return;
    }

    var val = input.value.trim().toLowerCase();
    var html = '';
    var conteo = 0;

    // Use the existing 'inventario' array to filter autocomplete locally
    inventario.forEach(function(m) {
        if (val === '' || m.name.toLowerCase().indexOf(val) > -1 || m.area.toLowerCase().indexOf(val) > -1) {
            var valEscapado = m.name.replace(/'/g, "\\'");
            html += '<li style="padding: 10px; cursor: pointer; border-bottom: 1px solid #eee; text-align: left;" ' +
                    'onmouseover="this.style.background=\'#f3f4f6\'" onmouseout="this.style.background=\'white\'" ' +
                    'onclick="seleccionarDropdown(\'' + campo + '\', \'' + m.id + '\', \'' + valEscapado + '\')">';
            html += '<strong style="color: #333;">' + m.name + '</strong>';
            if (m.area && m.area !== '—') html += ' <span style="color: #666; font-size: 13px;">- ' + m.area + '</span>';
            html += '</li>';
            conteo++;
        }
    });

    if (conteo === 0) {
        lista.style.display = 'none';
    } else {
        lista.innerHTML = html;
        lista.style.display = 'block';
    }

    if (val === '') {
        document.getElementById('filtro-' + campo).value = '';
        aplicarFiltro();
    }
}

function seleccionarDropdown(campo, id, texto) {
    document.getElementById('filtro-' + campo + '-input').value = texto;
    document.getElementById('filtro-' + campo).value = id;
    document.getElementById('lista-busqueda-' + campo).style.display = 'none';
    ignoreDropdown[campo] = true;
    aplicarFiltro();
}

document.addEventListener('click', function(e) {
    var input = document.getElementById('filtro-material-input');
    var lista = document.getElementById('lista-busqueda-material');
    if (input && lista) {
        if (e.target !== input && e.target !== lista && !lista.contains(e.target)) {
            lista.style.display = 'none';
        }
    }
});

function aplicarFiltro() {
    // Tomamos el valor de texto como búsqueda principal
    var busquedaStr = '';
    var inputSearch = document.getElementById('filtro-material-input');
    if (inputSearch) busquedaStr = inputSearch.value.trim();
    cargarInventario(busquedaStr, true);
}

function resetearFiltros() {
    if (document.getElementById('filtro-material-input')) document.getElementById('filtro-material-input').value = '';
    if (document.getElementById('filtro-material')) document.getElementById('filtro-material').value = '';
    if (document.getElementById('filtro-area')) document.getElementById('filtro-area').value = '';
    if (document.getElementById('filtro-stock')) document.getElementById('filtro-stock').value = '';
    aplicarFiltro();
}

/* ============================================================
   CARGA DESDE BASE DE DATOS
   ============================================================ */
function cargarInventario(busqueda = '', resetPagina = false) {
    if (resetPagina) {
        paginaActual = 1;
    }

    cargarTablaPaginada({
        url: API_URL,
        pagina: paginaActual,
        limite: limitePaginacion,
        filtros: { 
            search: busqueda,
            area: document.getElementById('filtro-area') ? document.getElementById('filtro-area').value : '',
            stock: document.getElementById('filtro-stock') ? document.getElementById('filtro-stock').value : ''
        },
        onDataLoaded: function(respuesta) {
            inventario = respuesta.data.map(function (m) {
                return {
                    id:          m.id,
                    name:        m.nombre,
                    area:        m.area,
                    description: m.descripcion,
                    unitPrice:   parseFloat(m.precio_unitario),
                    existencias: parseInt(m.existencias),
                    imagen:      m.imagen   || '',  
                    imagen_ts:   m.imagen_ts || 0   
                };
            });
            renderizarTabla();
            actualizarEstadisticas();
            
            var statEl = document.getElementById('stat-total');
            if (statEl) statEl.textContent = respuesta.total;
            
            renderizarControlesPaginacion({
                total: respuesta.total,
                limite: respuesta.limit,
                paginaActual: respuesta.page,
                contenedorId: 'paginacion-contenedor',
                onPageChange: function(nuevaPagina) {
                    paginaActual = nuevaPagina;
                    var busquedaStr = '';
                    var inputSearch = document.getElementById('input-busqueda');
                    if (inputSearch) busquedaStr = inputSearch.value.trim();
                    cargarInventario(busquedaStr, false);
                }
            });
        },
        onError: function(err) {
            mostrarNotificacion('Error al cargar inventario: ' + err, 'rojo');
        }
    });
}

/* ============================================================
   RENDERIZADO DE LA TABLA
   ============================================================ */
function renderizarTabla() {
    var cuerpoTabla = document.getElementById('cuerpo-tabla');

    if (inventario.length === 0) {
        cuerpoTabla.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:32px;color:#717182;">No hay materiales en el inventario</td></tr>';
        return;
    }

    var htmlFilas = '';
    for (var i = 0; i < inventario.length; i++) {
        var m = inventario[i];
        var precioFormateado = '$' + m.unitPrice.toLocaleString('es-MX', {
            minimumFractionDigits: 2, maximumFractionDigits: 2
        });

        // Miniatura de imagen en la fila — con cache busting (?t=imagen_ts)
        var imgTs  = m.imagen_ts || Date.now();
        var imgHtml = m.imagen
            ? '<img src="' + IMG_BASE_MAT + m.imagen + '?t=' + imgTs + '" alt="' + m.name + '"'
              + ' style="width:38px;height:38px;object-fit:contain;border-radius:4px;'
              + 'border:1px solid #e5e7eb;background:#f9f9f9;padding:2px;vertical-align:middle;"'
              + ' onerror="this.outerHTML=\'<span title=&quot;Imagen no encontrada: ' + m.imagen + '&quot; style=&quot;display:inline-flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:4px;border:1px dashed #f87171;background:#fef2f2;font-size:1rem;&quot;>📷</span>\'">'
            : '<span style="color:#9ca3af;font-size:0.75rem;">Sin img.</span>';

        htmlFilas += '<tr class="fila-tabla">';
        htmlFilas += '  <td data-label="ID" class="celda-id"><strong>' + m.id + '</strong></td>';
        htmlFilas += '  <td data-label="Nombre" class="celda-nombre">'
                     + imgHtml
                     + ' <span style="vertical-align:middle;margin-left:6px;">' + m.name + '</span>'
                     + '</td>';
        htmlFilas += '  <td data-label="Área"><span class="badge-area badge-' + m.area.toLowerCase() + '">' + m.area + '</span></td>';
        htmlFilas += '  <td data-label="Existencias" class="celda-numero"><strong>' + m.existencias.toLocaleString('es-MX') + '</strong></td>';
        htmlFilas += '  <td data-label="Precio" class="celda-precio">' + precioFormateado + '</td>';

        // Descripción con expand/collapse para textos largos
        var descId     = 'desc-' + m.id;
        var needsToggle = m.description && m.description.length > 80;
        htmlFilas += '  <td data-label="Descripción" class="celda-descripcion">';
        htmlFilas += '    <div id="' + descId + '" class="desc-wrap">' + m.description + '</div>';
        if (needsToggle) {
            htmlFilas += '    <button class="desc-toggle" onclick="toggleDescripcion(\'' + descId + '\', this)" title="Ver más">';
            htmlFilas += '      <svg viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>';
            htmlFilas += '    </button>';
        }
        htmlFilas += '  </td>';

        // Acciones: editar + eliminar
        htmlFilas += '  <td class="celda-acciones">';
        htmlFilas += '    <button class="btn-accion btn-editar" onclick="abrirEditar(\'' + m.id + '\')" title="Editar material">';
        htmlFilas += '      <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>';
        htmlFilas += '    </button>';
        htmlFilas += '    <button class="btn-accion btn-eliminar" onclick="eliminarMaterial(\'' + m.id + '\')" title="Eliminar material">';
        htmlFilas += '      <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>';
        htmlFilas += '    </button>';
        htmlFilas += '  </td>';
        htmlFilas += '</tr>';
    }
    cuerpoTabla.innerHTML = htmlFilas;
}

function actualizarEstadisticas() {
    // stat-total lo actualizamos con respuesta.total en cargarInventario

    var valorTotal = 0;
    for (var i = 0; i < inventario.length; i++) {
        valorTotal += inventario[i].existencias * inventario[i].unitPrice;
    }
    document.getElementById('stat-valor').textContent = '$' + valorTotal.toLocaleString('es-MX', {
        minimumFractionDigits: 2, maximumFractionDigits: 2
    });

    var areasUnicas = [];
    for (var j = 0; j < inventario.length; j++) {
        var a = inventario[j].area;
        if (areasUnicas.indexOf(a) === -1) areasUnicas.push(a);
    }
    document.getElementById('stat-areas').textContent = areasUnicas.length;
}

/* ============================================================
   MODAL — ABRIR / CERRAR / LIMPIAR
   ============================================================ */
function abrirModalNuevo() {
    materialEditando = null;
    document.getElementById('modal-titulo').textContent = 'Agregar Nuevo Material';
    limpiarFormulario();

    // En alta: imagen obligatoria
    document.getElementById('campo-imagen').required = true;

    // Ocultar preview (no hay imagen previa en alta)
    document.getElementById('preview-imagen-contenedor').style.display = 'none';
    document.getElementById('campo-imagen-actual').value = '';

    document.getElementById('modal-inventario').style.display = 'flex';
}

function abrirEditar(id) {
    var material = null;
    for (var i = 0; i < inventario.length; i++) {
        if (String(inventario[i].id) === String(id)) { material = inventario[i]; break; }
    }
    if (!material) return;

    materialEditando = material;
    document.getElementById('modal-titulo').textContent = 'Editar Material';

    // Poblar campos de texto
    document.getElementById('campo-nombre').value      = material.name;
    document.getElementById('campo-existencias').value = material.existencias;
    document.getElementById('campo-precio').value      = material.unitPrice;
    document.getElementById('campo-descripcion').value = material.description;

    // Poblar el select de área (value debe coincidir con la option value)
    var selectArea = document.getElementById('campo-area');
    if (selectArea) selectArea.value = material.area;

    // En edición: imagen opcional (ya tiene una)
    document.getElementById('campo-imagen').required = false;
    document.getElementById('campo-imagen').value    = '';

    /*
     * PERSISTENCIA: poblar el hidden input con el nombre actual de la imagen.
     * Si el usuario guarda sin subir nueva imagen, el servidor usará este valor
     * para mantener intacta la columna imagen en la BD.
     */
    document.getElementById('campo-imagen-actual').value = material.imagen;

    // Mostrar preview de la imagen actual
    var contenedorPreview = document.getElementById('preview-imagen-contenedor');
    var imgPreview        = document.getElementById('preview-imagen');
    var nombrePreview     = document.getElementById('preview-imagen-nombre');

    if (material.imagen) {
        // ?t=imagen_ts → fuerza al browser a no usar la versión en caché
        var tsPreview = material.imagen_ts || Date.now();
        imgPreview.src            = IMG_BASE_MAT + material.imagen + '?t=' + tsPreview;
        imgPreview.onerror        = function () {
            this.style.display = 'none';
            nombrePreview.textContent = '⚠️ Imagen no encontrada en servidor: ' + material.imagen;
            nombrePreview.style.color = '#ef4444';
        };
        nombrePreview.textContent = 'Actual: ' + material.imagen;
        nombrePreview.style.color = '';
        contenedorPreview.style.display = 'block';
    } else {
        contenedorPreview.style.display = 'none';
    }

    document.getElementById('modal-inventario').style.display = 'flex';
}

function cerrarModal() {
    document.getElementById('modal-inventario').style.display = 'none';
    materialEditando = null;
    limpiarFormulario();
}

function limpiarFormulario() {
    document.getElementById('campo-nombre').value      = '';
    document.getElementById('campo-existencias').value = '';
    document.getElementById('campo-precio').value      = '';
    document.getElementById('campo-descripcion').value = '';
    document.getElementById('campo-imagen').value      = '';
    document.getElementById('campo-imagen').required   = true;  // default: requerido
    document.getElementById('campo-imagen-actual').value = '';
    document.getElementById('preview-imagen-contenedor').style.display = 'none';
    // Resetear el select de área a la opción vacía
    var selectArea = document.getElementById('campo-area');
    if (selectArea) selectArea.value = '';
}

/* ============================================================
   PREVIEW DE IMAGEN NUEVA (onchange del input file)
   ============================================================ */
function previewImagenNueva(input) {
    var archivo = input.files[0];
    if (!archivo) return;

    // Validación básica en cliente (el servidor vuelve a validar con rigor)
    var tiposPermitidos = ['image/jpeg', 'image/png', 'image/webp'];
    if (!tiposPermitidos.includes(archivo.type)) {
        mostrarNotificacion('❌ Solo se permiten imágenes JPG, PNG o WebP.', 'rojo');
        input.value = '';
        return;
    }
    if (archivo.size > 5 * 1024 * 1024) {
        mostrarNotificacion('❌ La imagen no debe superar 5 MB.', 'rojo');
        input.value = '';
        return;
    }

    // Mostrar preview local
    var reader = new FileReader();
    reader.onload = function (e) {
        var imgPreview = document.getElementById('preview-imagen');
        imgPreview.src           = e.target.result;
        imgPreview.style.display = 'block';
        document.getElementById('preview-imagen-nombre').textContent = 'Nueva imagen: ' + archivo.name;
        document.getElementById('preview-imagen-contenedor').style.display = 'block';
    };
    reader.readAsDataURL(archivo);
}

/* ============================================================
   CRUD — GUARDAR (INSERT o UPDATE)
   Usa FormData para enviar texto + archivo en multipart/form-data
   ============================================================ */
function guardarMaterial(evento) {
    evento.preventDefault();

    var nombre      = document.getElementById('campo-nombre').value.trim();
    var area        = document.getElementById('campo-area').value.trim();
    var existencias = parseInt(document.getElementById('campo-existencias').value);
    var precio      = parseFloat(document.getElementById('campo-precio').value);
    var descripcion = document.getElementById('campo-descripcion').value.trim();

    if (!nombre || !area) {
        mostrarNotificacion('Nombre y área son requeridos.', 'rojo');
        return;
    }
    if (isNaN(precio) || precio <= 0) {
        mostrarNotificacion('El precio debe ser mayor a 0.', 'rojo');
        return;
    }

    /*
     * FormData recoge automáticamente todos los campos del formulario,
     * incluyendo el archivo de imagen y el hidden input imagen_actual.
     * No se establece Content-Type manual: el navegador lo pone con el
     * boundary correcto para multipart/form-data.
     */
    var formData = new FormData(document.getElementById('form-material'));
    formData.set('accion', materialEditando ? 'actualizar' : 'insertar');
    if (materialEditando) {
        formData.set('id', materialEditando.id);
    }

    var btnGuardar = document.querySelector('#form-material .btn-guardar');
    btnGuardar.textContent = 'Guardando…';
    btnGuardar.disabled    = true;

    fetch(API_URL, { method: 'POST', body: formData })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            btnGuardar.textContent = 'Guardar';
            btnGuardar.disabled    = false;

            if (data.error) {
                mostrarNotificacion('❌ Error: ' + data.error, 'rojo');
                return;
            }
            var msg = materialEditando
                ? '✅ Material actualizado correctamente'
                : '✅ Material registrado correctamente (ID ' + data.id + ')';
            mostrarNotificacion(msg, 'verde');
            cerrarModal();
            cargarInventario();
        })
        .catch(function (err) {
            btnGuardar.textContent = 'Guardar';
            btnGuardar.disabled    = false;
            mostrarNotificacion('Error de conexión: ' + err, 'rojo');
        });
}

/* ============================================================
   CRUD — ELIMINAR
   ============================================================ */
function eliminarMaterial(id) {
    if (!confirm('¿Estás seguro de que quieres eliminar este material?\nSe borrará también la imagen del servidor.')) return;

    /*
     * La eliminación se envía como FormData para ser consistente
     * con el mismo endpoint. El servidor borrará la imagen física.
     */
    var fd = new FormData();
    fd.append('accion', 'eliminar');
    fd.append('id', id);

    fetch(API_URL, { method: 'POST', body: fd })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.error) {
                mostrarNotificacion('Error: ' + data.error, 'rojo');
                return;
            }
            mostrarNotificacion('🗑️ Material eliminado correctamente', 'rojo');
            cargarInventario();
        })
        .catch(function (err) {
            mostrarNotificacion('Error de conexión: ' + err, 'rojo');
        });
}

/* ============================================================
   UTILIDADES
   ============================================================ */
function mostrarNotificacion(mensaje, tipo) {
    var contenedor = document.getElementById('notificacion');
    contenedor.textContent  = mensaje;
    contenedor.className    = 'notificacion notificacion-' + (tipo || 'verde');
    contenedor.style.display = 'block';
    setTimeout(function () { contenedor.style.display = 'none'; }, 4000);
}

function toggleDescripcion(descId, btn) {
    var wrap = document.getElementById(descId);
    if (!wrap) return;
    var isExpanded = wrap.classList.toggle('desc-expanded');
    btn.classList.toggle('desc-toggle-open', isExpanded);
    btn.title = isExpanded ? 'Ver menos' : 'Ver más';
}

function marcarActivo(elementoClicado) {
    var todos = document.querySelectorAll('.sidebar-nav a');
    for (var i = 0; i < todos.length; i++) {
        todos[i].classList.remove('activo');
    }
    elementoClicado.classList.add('activo');
}
