// ============================================================
// supplies.js — Gestión de Suministros (tickets multi-material)
// ============================================================

var API_URL = '../PHP/api_suministros.php';

// Datos globales
var PROVEEDORES = [];   // [{id, nombre, ...}]
var MATERIALES = [];   // [{id, nombre, area, precio_unitario, ...}]
var TICKETS = [];   // [{id, id_proveedores, fecha}]
var DETALLES = [];   // [{id_suministros, id_materiales, cantidad, costo_unitario}]

var paginaActual     = 1;
var limitePaginacion = 10;

// Mapa nombre→id para resolución rápida del datalist de proveedores
// Ej: { "ProveedorXYZ (ID:3)": 3 }
var proveedorMap = {};

// Estado
var lineasDetalle = 0; // Contador ID para líneas del modal

// ============================================================
// INICIALIZACIÓN
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    cargarDatos();
    var hoy = new Date().toISOString().split('T')[0];
    document.getElementById('campo-fecha').value = hoy;
});

// ============================================================
// CARGA DESDE BASE DE DATOS
// ============================================================
function cargarDatos(resetPagina = false) {
    if (resetPagina) {
        paginaActual = 1;
    }

    var filtros = {
        desde: document.getElementById('filtro-desde').value,
        hasta: document.getElementById('filtro-hasta').value,
        proveedor: document.getElementById('filtro-proveedor').value,
        material: document.getElementById('filtro-material').value
    };

    cargarTablaPaginada({
        url: API_URL,
        pagina: paginaActual,
        limite: limitePaginacion,
        filtros: filtros,
        onDataLoaded: function(respuesta) {
            var data = respuesta.data;
            TICKETS = (data.tickets || []).map(function (t) {
                return {
                    id: parseInt(t.id),
                    id_proveedores: parseInt(t.id_proveedores),
                    fecha: t.fecha ? t.fecha.split(' ')[0].split('T')[0] : t.fecha
                };
            });

            DETALLES = (data.detalles || []).map(function (d) {
                return {
                    id_suministros: parseInt(d.id_suministros),
                    id_materiales: parseInt(d.id_materiales),
                    cantidad: parseInt(d.cantidad),
                    costo_unitario: parseFloat(d.costo_unitario)
                };
            });

            PROVEEDORES = data.proveedores || [];
            MATERIALES = data.materiales || [];

            llenarSelectsUI();
            renderizarTickets(TICKETS);
            actualizarStats(TICKETS);
            
            renderizarControlesPaginacion({
                total: respuesta.total,
                limite: respuesta.limit,
                paginaActual: respuesta.page,
                contenedorId: 'paginacion-contenedor',
                onPageChange: function(nuevaPagina) {
                    paginaActual = nuevaPagina;
                    cargarDatos(false);
                }
            });
        },
        onError: function(err) {
            mostrarNotificacion('Error: ' + err, true);
        }
    });
}

// ============================================================
// SELECTORES DE FILTROS Y MODAL
// ============================================================
function llenarSelectsUI() {
    /* ── Datalist de Proveedores (Punto 1) ─────────────────────────────────
     * Sustituye el select por un datalist + input de texto.
     * Se construye proveedorMap para name→id al escribir.
     * Las etiquetas usan "Nombre (ID:X)" como clave única.
     */
    var dlProv = document.getElementById('datalist-proveedores');
    if (dlProv) {
        dlProv.innerHTML = '';
        proveedorMap = {};
        PROVEEDORES.slice()
            .sort(function (a, b) { return a.nombre.localeCompare(b.nombre); })
            .forEach(function (p) {
                var etiqueta = p.nombre + ' (ID:' + p.id + ')';
                proveedorMap[etiqueta] = parseInt(p.id);
                var op = document.createElement('option');
                op.value = etiqueta;
                dlProv.appendChild(op);
            });
    }

    // ============================================================
    // Filtros de Proveedor y Material (ya no se llenan en <select>)
    // Se manejarán dinámicamente con renderizarDropdownBusqueda()
    // ============================================================

    // Datalist global de materiales (para los inputs de líneas — Punto 2)
    var dl = document.getElementById('datalist-materiales');
    if (!dl) {
        dl = document.createElement('datalist');
        dl.id = 'datalist-materiales';
        document.body.appendChild(dl);
    }
    dl.innerHTML = '';
    MATERIALES.sort(function (a, b) { return a.nombre.localeCompare(b.nombre); })
        .forEach(function (m) {
            var op = document.createElement('option');
            op.value = m.nombre + ' (' + m.area + ')';
            op.setAttribute('data-id', m.id);
            op.setAttribute('data-precio', m.precio_unitario);
            dl.appendChild(op);
        });
}

// ============================================================
// AUTOCOMPLETADO DE FILTROS (NUEVO UI)
// ============================================================
var ignoreDropdown = { proveedor: false, material: false };

function renderizarDropdownBusqueda(campo) {
    var inputId = campo === 'proveedor' ? 'filtro-proveedor-input' : 'filtro-material-input';
    var listaId = campo === 'proveedor' ? 'lista-busqueda-proveedor' : 'lista-busqueda-material';
    var input = document.getElementById(inputId);
    var lista = document.getElementById(listaId);
    if (!input || !lista) return;

    if (ignoreDropdown[campo]) {
        ignoreDropdown[campo] = false;
        return;
    }

    var val = input.value.trim().toLowerCase();
    var html = '';
    var conteo = 0;

    if (campo === 'proveedor') {
        PROVEEDORES.forEach(function(p) {
            if (val === '' || p.nombre.toLowerCase().indexOf(val) > -1) {
                var valEscapado = p.nombre.replace(/'/g, "\\'");
                html += '<li style="padding: 10px; cursor: pointer; border-bottom: 1px solid #eee; text-align: left;" ' +
                        'onmouseover="this.style.background=\'#f3f4f6\'" onmouseout="this.style.background=\'white\'" ' +
                        'onclick="seleccionarDropdown(\'' + campo + '\', \'' + p.id + '\', \'' + valEscapado + '\')">';
                html += '<strong style="color: #333;">' + p.nombre + '</strong>';
                html += '</li>';
                conteo++;
            }
        });
    } else if (campo === 'material') {
        MATERIALES.forEach(function(m) {
            if (val === '' || m.nombre.toLowerCase().indexOf(val) > -1 || m.area.toLowerCase().indexOf(val) > -1) {
                var valEscapado = m.nombre.replace(/'/g, "\\'");
                html += '<li style="padding: 10px; cursor: pointer; border-bottom: 1px solid #eee; text-align: left;" ' +
                        'onmouseover="this.style.background=\'#f3f4f6\'" onmouseout="this.style.background=\'white\'" ' +
                        'onclick="seleccionarDropdown(\'' + campo + '\', \'' + m.id + '\', \'' + valEscapado + '\')">';
                html += '<strong style="color: #333;">' + m.nombre + '</strong>';
                if (m.area && m.area !== '—') html += ' <span style="color: #666; font-size: 13px;">- ' + m.area + '</span>';
                html += '</li>';
                conteo++;
            }
        });
    }

    if (conteo === 0) {
        lista.style.display = 'none';
    } else {
        lista.innerHTML = html;
        lista.style.display = 'block';
    }

    if (val === '') {
        document.getElementById('filtro-' + campo).value = '';
        aplicarFiltros();
    }
}

function seleccionarDropdown(campo, id, texto) {
    document.getElementById('filtro-' + campo + '-input').value = texto;
    document.getElementById('filtro-' + campo).value = id;
    document.getElementById('lista-busqueda-' + campo).style.display = 'none';
    ignoreDropdown[campo] = true;
    aplicarFiltros();
}

document.addEventListener('click', function(e) {
    ['proveedor', 'material'].forEach(function(campo) {
        var input = document.getElementById('filtro-' + campo + '-input');
        var lista = document.getElementById('lista-busqueda-' + campo);
        if (input && lista) {
            if (e.target !== input && e.target !== lista && !lista.contains(e.target)) {
                lista.style.display = 'none';
            }
        }
    });
});

// ============================================================
// FILTROS
// ============================================================
function aplicarFiltros() {
    cargarDatos(true);
}

function resetearFiltros() {
    document.getElementById('filtro-desde').value = '';
    document.getElementById('filtro-hasta').value = '';
    
    var fprovInput = document.getElementById('filtro-proveedor-input');
    if (fprovInput) fprovInput.value = '';
    var fprov = document.getElementById('filtro-proveedor');
    if (fprov) fprov.value = '';
    
    var fmatInput = document.getElementById('filtro-material-input');
    if (fmatInput) fmatInput.value = '';
    var fmat = document.getElementById('filtro-material');
    if (fmat) fmat.value = '';
    
    aplicarFiltros();
}

// ============================================================
// CÁLCULO
// ============================================================
function calcularTotalTicket(idTicket) {
    return DETALLES.filter(function (d) { return d.id_suministros === idTicket; })
        .reduce(function (sum, d) { return sum + d.cantidad * d.costo_unitario; }, 0);
}

function obtenerNombreProveedor(id) {
    var p = PROVEEDORES.find(function (x) { return parseInt(x.id) === id; });
    return p ? p.nombre : 'Proveedor #' + id;
}

function obtenerNombreMaterial(id) {
    var m = MATERIALES.find(function (x) { return parseInt(x.id) === id; });
    return m ? m.nombre : 'Material #' + id;
}

function formatoMoneda(v) {
    return '$' + parseFloat(v).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatearFecha(f) {
    if (!f) return '';
    var soloFecha = f.split('T')[0].split(' ')[0];
    var partes = soloFecha.split('-');
    if (partes.length !== 3) return f;
    return partes[2] + '/' + partes[1] + '/' + partes[0];
}

// ============================================================
// RENDERIZADO DE TICKET CARDS
// ============================================================
function renderizarTickets(lista) {
    var contenedor = document.getElementById('lista-tickets');
    var contador = document.getElementById('contador-resultados');

    if (lista.length === 0) {
        contenedor.innerHTML = '<div style="text-align:center;padding:40px;color:var(--color-muted);">Sin tickets en el período seleccionado</div>';
        contador.textContent = '0 resultados';
        return;
    }

    var html = '';
    lista.forEach(function (t) {
        var total = calcularTotalTicket(t.id);
        var proveedor = obtenerNombreProveedor(t.id_proveedores);
        var lineasT = DETALLES.filter(function (d) { return d.id_suministros === t.id; });
        var numMat = lineasT.length;

        html += '<div class="ticket-card" id="ticket-' + t.id + '">';

        html += '<div class="ticket-card-header">';
        
        // 1. Proveedor
        html += '  <div class="ticket-col-proveedor" style="display:flex; flex-direction:column; gap:4px;">';
        html += '    <span class="ticket-proveedor" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; display: inline-block;">' + proveedor + '</span>';
        html += '    <span class="ticket-id" style="font-size:0.82rem; color:var(--color-muted);">Ticket #' + t.id + '</span>';
        html += '  </div>';

        // 2. Fecha
        html += '  <div class="ticket-col-fecha">';
        html += '    <span class="ticket-fecha">📅 ' + formatearFecha(t.fecha) + '</span>';
        html += '  </div>';

        // 3. Info Ticket
        html += '  <div class="ticket-col-info" style="display:flex; flex-direction:column; gap:4px;">';
        html += '    <span class="ticket-total">' + formatoMoneda(total) + '</span>';
        html += '    <span style="font-size:0.82rem; color:var(--color-muted);">' + numMat + ' material(es)</span>';
        html += '  </div>';

        // 4. Acciones
        html += '  <div class="ticket-header-derecha">';
        html += '    <span class="btn-ver-mas" onclick="event.stopPropagation(); toggleDetalle(' + t.id + ')">Ver más ▾</span>';
        html += '    <button class="btn-accion btn-editar" onclick="event.stopPropagation(); abrirModalEditar(' + t.id + ')" title="Editar ticket">';
        html += '      <svg viewBox="0 0 24 24"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>';
        html += '    </button>';
        html += '    <button class="btn-accion btn-eliminar" onclick="event.stopPropagation(); eliminarTicket(' + t.id + ')" title="Eliminar ticket">';
        html += '      <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>';
        html += '    </button>';
        html += '  </div>';
        
        html += '</div>';

        html += '<div class="ticket-detalle" id="detalle-' + t.id + '">';
        html += '  <table class="detalle-tabla">';
        html += '    <thead><tr><th>Material</th><th>Área</th><th>Cantidad</th><th>Costo Unit.</th><th>Subtotal</th></tr></thead>';
        html += '    <tbody>';
        lineasT.forEach(function (d) {
            var mat = MATERIALES.find(function (m) { return parseInt(m.id) === d.id_materiales; });
            var nom = mat ? mat.nombre : 'Material #' + d.id_materiales;
            var area = mat ? mat.area : '—';
            var sub = d.cantidad * d.costo_unitario;
            html += '<tr><td>' + nom + '</td><td>' + area + '</td>';
            html += '<td style="text-align:right;">' + d.cantidad.toLocaleString('es-MX') + '</td>';
            html += '<td>' + formatoMoneda(d.costo_unitario) + '</td>';
            html += '<td class="detalle-total-row">' + formatoMoneda(sub) + '</td></tr>';
        });
        html += '    </tbody>';
        html += '    <tfoot><tr>';
        html += '      <td colspan="4" style="text-align:right;font-weight:700;padding:10px;">Total del Ticket:</td>';
        html += '      <td class="detalle-total-row">' + formatoMoneda(total) + '</td>';
        html += '    </tr></tfoot>';
        html += '  </table>';
        html += '  <div class="detalle-footer">';
        html += '    <span style="font-size:0.82rem;color:var(--color-muted);">Proveedor: <strong>' + proveedor + '</strong></span>';
        html += '  </div>';
        html += '</div>';

        html += '</div>';
    });

    contenedor.innerHTML = html;
    contador.textContent = lista.length + ' ticket(s)';
}

function toggleDetalle(id) {
    var el = document.getElementById('detalle-' + id);
    var btn = document.querySelector('#ticket-' + id + ' .btn-ver-mas');
    if (!el) return;
    if (el.classList.contains('abierto')) {
        el.classList.remove('abierto');
        if (btn) btn.textContent = 'Ver más ▾';
    } else {
        el.classList.add('abierto');
        if (btn) btn.textContent = 'Ocultar ▴';
    }
}

// ============================================================
// ESTADÍSTICAS
// ============================================================
function actualizarStats(lista) {
    var total = lista.reduce(function (sum, t) { return sum + calcularTotalTicket(t.id); }, 0);
    document.getElementById('stat-total').textContent = lista.length;
    document.getElementById('stat-inversion').textContent = formatoMoneda(total);
}

// ============================================================
// MODAL — NUEVO / EDITAR TICKET
// ============================================================
function abrirModalNuevo() {
    document.getElementById('modal-titulo').textContent = 'Nuevo Pedido de Suministro';
    document.getElementById('campo-id').value = '';
    // Limpiar buscador de proveedor y su hidden id
    document.getElementById('campo-proveedor-busqueda').value = '';
    document.getElementById('campo-proveedor-id').value = '';
    var hint = document.getElementById('hint-proveedor');
    if (hint) { hint.textContent = ''; hint.className = 'hint-proveedor'; }
    document.getElementById('campo-fecha').value = new Date().toISOString().split('T')[0];
    limpiarLineas();
    agregarLinea();
    actualizarTotalModal();
    document.getElementById('modal-suministro').style.display = 'flex';
}

function abrirModalEditar(idTicket) {
    var ticket = TICKETS.find(function (t) { return t.id === idTicket; });
    if (!ticket) return;

    document.getElementById('modal-titulo').textContent = 'Editar Ticket #' + idTicket;
    document.getElementById('campo-id').value = idTicket;

    // Resolver nombre del proveedor para mostrar en el input de texto
    var prov = PROVEEDORES.find(function (p) { return parseInt(p.id) === ticket.id_proveedores; });
    var etiquetaProv = prov ? prov.nombre + ' (ID:' + prov.id + ')' : '';
    document.getElementById('campo-proveedor-busqueda').value = etiquetaProv;
    document.getElementById('campo-proveedor-id').value = ticket.id_proveedores || '';
    var hint = document.getElementById('hint-proveedor');
    if (hint) {
        hint.textContent = prov ? '✓ ' + prov.nombre : '';
        hint.className = 'hint-proveedor' + (prov ? ' valido' : '');
    }

    document.getElementById('campo-fecha').value = ticket.fecha ? ticket.fecha.split('T')[0] : '';

    limpiarLineas();

    var lineasExistentes = DETALLES.filter(function (d) { return d.id_suministros === idTicket; });
    if (lineasExistentes.length === 0) {
        agregarLinea();
    } else {
        lineasExistentes.forEach(function (d) {
            agregarLinea(d.id_materiales, d.cantidad, d.costo_unitario);
        });
    }

    actualizarTotalModal();
    document.getElementById('modal-suministro').style.display = 'flex';
}

function cerrarModal() {
    document.getElementById('modal-suministro').style.display = 'none';
}

function cerrarModalDetalle() {
    document.getElementById('modal-detalle').style.display = 'none';
}

// ============================================================
// LÍNEAS DE MATERIALES — ahora con input + datalist
// ============================================================
function limpiarLineas() {
    document.getElementById('lineas-materiales-container').innerHTML = '';
    lineasDetalle = 0;
}

/**
 * agregarLinea — crea una fila con:
 *   - input text + datalist (permite escribir O seleccionar)
 *   - input cantidad
 *   - input costo unitario (se autocompleta al elegir del datalist)
 *   - botón quitar
 *
 * @param {number|null} idMat    — id del material (para edición)
 * @param {number|null} cantidad
 * @param {number|null} costo
 */
function agregarLinea(idMat, cantidad, costo) {
    lineasDetalle++;
    var lid = 'linea-' + lineasDetalle;

    // Resolver nombre del material si viene de edición
    var nombreMat = '';
    if (idMat) {
        var matEncontrado = MATERIALES.find(function (m) { return parseInt(m.id) === parseInt(idMat); });
        if (matEncontrado) {
            nombreMat = matEncontrado.nombre + ' (' + matEncontrado.area + ')';
        }
    }

    var trLinea = document.createElement('tr');
    trLinea.className = 'linea-material';
    trLinea.id = lid;

    trLinea.innerHTML =
        '<td style="padding: 6px 10px; border-bottom: 1px solid #f0f2f7;">' +
        '<input type="text" ' +
        'list="datalist-materiales" ' +
        'placeholder="Escribe o selecciona material..." ' +
        'value="' + nombreMat + '" ' +
        'data-id="' + (idMat || '') + '" ' +
        'class="linea-input-material" ' +
        'style="width: 100%; box-sizing: border-box; padding: 6px; border: 1px solid #ccc; border-radius: 4px;" ' +
        'oninput="onEscrituraMaterial(this)" ' +
        'required' +
        '></td>' +
        '<td style="padding: 6px 10px; border-bottom: 1px solid #f0f2f7;"><input type="number" placeholder="Cant." min="1" value="' + (cantidad || '') + '" required oninput="actualizarTotalModal()" style="width: 100%; box-sizing: border-box; padding: 6px; border: 1px solid #ccc; border-radius: 4px;"></td>' +
        '<td style="padding: 6px 10px; border-bottom: 1px solid #f0f2f7;"><input type="number" placeholder="Costo unit." min="0" step="0.01" value="' + (costo || '') + '" oninput="actualizarTotalModal()" style="width: 100%; box-sizing: border-box; padding: 6px; border: 1px solid #ccc; border-radius: 4px;"></td>' +
        '<td style="padding: 6px 10px; border-bottom: 1px solid #f0f2f7; text-align: center;"><button type="button" class="btn-quitar-linea" onclick="quitarLinea(\'' + lid + '\')" style="background: #ef4444; color: white; border: none; border-radius: 4px; padding: 6px 10px; cursor: pointer;">×</button></td>';

    document.getElementById('lineas-materiales-container').appendChild(trLinea);
    actualizarTotalModal();
}

/**
 * onEscrituraMaterial — se llama en cada keystroke del input de material.
 * Si el texto coincide exactamente con una opción del datalist,
 * autocompleta el precio y guarda el id en data-id.
 */
function onEscrituraMaterial(input) {
    var valor = input.value.trim();
    var dl = document.getElementById('datalist-materiales');
    if (!dl) { actualizarTotalModal(); return; }

    // Buscar coincidencia exacta en el datalist
    var opciones = dl.querySelectorAll('option');
    var encontrado = false;
    for (var i = 0; i < opciones.length; i++) {
        if (opciones[i].value === valor) {
            var idMat = opciones[i].getAttribute('data-id');
            var precio = parseFloat(opciones[i].getAttribute('data-precio') || 0);
            input.setAttribute('data-id', idMat);
            // Autocompleta el costo unitario en el 3er input de la misma línea
            var linea = input.closest('.linea-material');
            if (linea) {
                var inputs = linea.querySelectorAll('input');
                if (inputs[2]) inputs[2].value = precio; // índice 2 = costo unit.
            }
            encontrado = true;
            break;
        }
    }
    if (!encontrado) {
        input.setAttribute('data-id', ''); // limpiar si no coincide
    }
    actualizarTotalModal();
}

/**
 * onEscrituraProveedor — oninput del campo-proveedor-busqueda.
 * Resuelve el id numérico del proveedor al coincidir exactamente
 * con una opción del datalist, y actualiza el hint debajo del campo.
 */
function onEscrituraProveedor(input) {
    var valor = input.value.trim();
    var hiddenId = document.getElementById('campo-proveedor-id');
    var hint    = document.getElementById('hint-proveedor');
    var idResuelto = proveedorMap[valor];

    if (idResuelto !== undefined) {
        hiddenId.value = idResuelto;
        if (hint) {
            // Extraer nombre limpio (sin el "(ID:X)" del final)
            var nombreLimpio = valor.replace(/\s*\(ID:\d+\)$/, '');
            hint.textContent = '✓ ' + nombreLimpio + ' seleccionado';
            hint.className = 'hint-proveedor valido';
        }
    } else {
        hiddenId.value = '';
        if (hint && valor.length > 0) {
            hint.textContent = 'Escribe y selecciona de la lista';
            hint.className = 'hint-proveedor invalido';
        } else if (hint) {
            hint.textContent = '';
            hint.className = 'hint-proveedor';
        }
    }
}

function quitarLinea(lid) {
    var el = document.getElementById(lid);
    if (el) el.parentNode.removeChild(el);
    actualizarTotalModal();
    if (document.getElementById('lineas-materiales-container').children.length === 0) {
        agregarLinea();
    }
}

function actualizarTotalModal() {
    var total = 0;
    var lineas = document.querySelectorAll('.linea-material');
    lineas.forEach(function (l) {
        var inputs = l.querySelectorAll('input');
        // inputs[0]=material(text), inputs[1]=cantidad, inputs[2]=costo
        var cant = parseFloat(inputs[1] ? inputs[1].value : 0) || 0;
        var costo = parseFloat(inputs[2] ? inputs[2].value : 0) || 0;
        total += cant * costo;
    });
    document.getElementById('modal-total-preview').textContent = 'Total estimado: ' + formatoMoneda(total);
}

// ============================================================
// GUARDAR TICKET (INSERT o UPDATE vía eliminar+insertar)
// ============================================================
function guardarTicket(e) {
    e.preventDefault();

    var idTicket = document.getElementById('campo-id').value
        ? parseInt(document.getElementById('campo-id').value) : null;

    /* ── Leer proveedor desde el hidden input (Punto 3) ──────────────────
     * El id numérico se depositó en campo-proveedor-id al seleccionar
     * del datalist (vía onEscrituraProveedor).
     * Si está vacío, el proveedor no fue seleccionado correctamente.
     */
    var idProv = parseInt(document.getElementById('campo-proveedor-id').value || '0');
    if (!idProv || idProv <= 0) {
        mostrarNotificacion('Selecciona un proveedor válido de la lista.', true);
        document.getElementById('campo-proveedor-busqueda').focus();
        return;
    }

    var fecha = document.getElementById('campo-fecha').value;

    var lineasEls = document.querySelectorAll('.linea-material');
    var lineas = [];
    var valido = true;

    lineasEls.forEach(function (l) {
        var inputs = l.querySelectorAll('input');
        var inputMat = inputs[0]; // input de texto del material
        var idMat = parseInt(inputMat ? inputMat.getAttribute('data-id') : 0);
        var cant = inputs[1] ? parseInt(inputs[1].value) : 0;
        var costo = inputs[2] ? parseFloat(inputs[2].value) : 0;

        if (!idMat || cant <= 0) { valido = false; return; }
        lineas.push({ id_materiales: idMat, cantidad: cant, costo_unitario: costo });
    });

    if (!valido || lineas.length === 0) {
        mostrarNotificacion('Selecciona un material válido del listado y una cantidad > 0', true);
        return;
    }

    if (idTicket) {
        fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ accion: 'eliminar_ticket', id: idTicket })
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.error) { mostrarNotificacion('Error al actualizar: ' + data.error, true); return; }
                insertarTicket(idProv, fecha, lineas);
            });
    } else {
        insertarTicket(idProv, fecha, lineas);
    }
}

function insertarTicket(idProv, fecha, lineas) {
    fetch(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            accion: 'insertar_ticket',
            id_proveedores: idProv,
            fecha: fecha,
            lineas: lineas
        })
    })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.error) { mostrarNotificacion('Error: ' + data.error, true); return; }
            mostrarNotificacion('✅ Ticket guardado correctamente');
            cerrarModal();
            cargarDatos();
        })
        .catch(function (e) { mostrarNotificacion('Error de conexión: ' + e, true); });
}

// ============================================================
// ELIMINAR TICKET
// ============================================================
function eliminarTicket(id) {
    if (!confirm('¿Eliminar el ticket #' + id + ' y todos sus materiales?')) return;
    fetch(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ accion: 'eliminar_ticket', id: id })
    })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.error) { mostrarNotificacion('Error: ' + data.error, true); return; }
            mostrarNotificacion('🗑️ Ticket eliminado');
            cargarDatos();
        })
        .catch(function (e) { mostrarNotificacion('Error de conexión: ' + e, true); });
}

// ============================================================
// UTILIDADES
// ============================================================
function mostrarNotificacion(msg, esError) {
    var notif = document.getElementById('notificacion');
    notif.textContent = msg;
    notif.style.background = esError ? '#ef4444' : '#10b981';
    notif.style.display = 'block';
    notif.style.position = 'fixed';
    notif.style.top = '20px';
    notif.style.right = '20px';
    notif.style.color = '#fff';
    notif.style.padding = '12px 24px';
    notif.style.borderRadius = '8px';
    notif.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
    notif.style.zIndex = '9999';
    setTimeout(function () { notif.style.display = 'none'; }, 3000);
}

function marcarActivo(el) {
    document.querySelectorAll('.sidebar-nav a').forEach(function (a) { a.classList.remove('activo'); });
    el.classList.add('activo');
}

function cerrarSesion() {
    window.location.href = '../Home/Home.html';
}