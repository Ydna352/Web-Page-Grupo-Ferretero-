// ============================================================
// workers.js — Gestión de Trabajadores (dinámica: MySQL via PHP)
// ============================================================

// Ruta base de la API
var API_URL = '../PHP/api_trabajadores.php';

// Variables de estado global
var trabajadores   = [];    // Se llenará desde la BD
var PUESTOS        = [];    // Se llenará desde la BD
var modoEdicion    = false;
var trabajadorEditandoId = null;

var paginaActual     = 1;
var limitePaginacion = 10;

var campoActivo = null;
var ignoreDropdownNombre = false;

// Selección de DOM
var modalContenedor = document.getElementById('modal-trabajador');
var formTrabajador  = document.getElementById('form-trabajador');
var tituloModal     = document.getElementById('modal-titulo');
var tbodyTabla      = document.getElementById('cuerpo-tabla');
var inputId         = document.getElementById('campo-id');
var inputNombre     = document.getElementById('campo-nombre');
var inputPuesto     = document.getElementById('campo-puesto');
var inputSalario    = document.getElementById('campo-salario');
var inputEmail      = document.getElementById('campo-email');
var inputTelefono   = document.getElementById('campo-telefono');
var notificacion    = document.getElementById('notificacion');
var statTotal       = document.getElementById('stat-total');
var statNomina      = document.getElementById('stat-nomina');
var statPromedio    = document.getElementById('stat-promedio');

// ============================================================
// INICIALIZACIÓN
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    cargarPuestos();   // Primero puestos, luego tabla
});

// ============================================================
// CARGA DESDE BASE DE DATOS
// ============================================================

function cargarPuestos() {
    fetch(API_URL + '?accion=gf_puestos')
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.error) {
                mostrarNotificacion('Error cargando puestos: ' + data.error);
                return;
            }
            PUESTOS = data;
            llenarSelectPuestos();
            llenarFiltroPuestos();
            cargarTrabajadores();   // Cargar trabajadores después de puestos
        })
        .catch(function (err) {
            mostrarNotificacion('No se pudo conectar con el servidor. ' + err);
        });
}

function cargarTrabajadores(busqueda = '', resetPagina = false) {
    if (resetPagina) {
        paginaActual = 1;
    }

    cargarTablaPaginada({
        url: API_URL,
        pagina: paginaActual,
        limite: limitePaginacion,
        filtros: { search: busqueda },
        onDataLoaded: function(respuesta) {
            trabajadores = respuesta.data;
            renderizarTabla();
            
            // Actualizar el UI de estadísticas usando la nómina y total provistos por el backend
            actualizarEstadisticas(respuesta.total, respuesta.nomina_total);

            renderizarControlesPaginacion({
                total: respuesta.total,
                limite: respuesta.limit,
                paginaActual: respuesta.page,
                contenedorId: 'paginacion-contenedor',
                onPageChange: function(nuevaPagina) {
                    paginaActual = nuevaPagina;
                    var valNombre = document.getElementById('filtro-nombre') ? document.getElementById('filtro-nombre').value.trim() : '';
                    var valPuesto = document.getElementById('filtro-puesto') ? document.getElementById('filtro-puesto').value : '';
                    var b = valNombre || valPuesto;
                    cargarTrabajadores(b, false);
                }
            });
            
            if (campoActivo) {
                renderizarDropdownBusqueda(campoActivo);
            } else {
                var listaN = document.getElementById('lista-busqueda-nombre');
                if (listaN) listaN.style.display = 'none';
            }
        },
        onError: function(err) {
            mostrarNotificacion('Error cargando trabajadores: ' + err);
        }
    });
}

// ============================================================
// FUNCIONES DE RENDERIZADO
// ============================================================

function llenarSelectPuestos() {
    inputPuesto.innerHTML = '<option value="" disabled selected>Selecciona un puesto</option>';
    PUESTOS.forEach(function (p) {
        var option = document.createElement('option');
        option.value       = p.nombre_puesto;
        option.textContent = p.nombre_puesto;
        inputPuesto.appendChild(option);
    });
}

function llenarFiltroPuestos() {
    var filtroPuesto = document.getElementById('filtro-puesto');
    if (!filtroPuesto) return;
    filtroPuesto.innerHTML = '<option value="">Todos los puestos</option>';
    PUESTOS.forEach(function (p) {
        var option = document.createElement('option');
        option.value       = p.nombre_puesto;
        option.textContent = p.nombre_puesto;
        filtroPuesto.appendChild(option);
    });
}

function actualizarSalarioSelect() {
    var puestoSeleccionado = inputPuesto.value;
    var puesto = PUESTOS.find(function (p) { return p.nombre_puesto === puestoSeleccionado; });
    if (puesto) {
        inputSalario.value = puesto.salario_mensual;
    }
}

function formatoMoneda(valor) {
    return '$' + parseFloat(valor).toLocaleString('es-MX', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function actualizarEstadisticas(total, nomina_total) {
    var promedio = total > 0 ? (nomina_total / total) : 0;

    statTotal.textContent   = total || 0;
    statNomina.textContent  = formatoMoneda(nomina_total || 0);
    statPromedio.textContent= formatoMoneda(promedio || 0);
}

function renderizarTabla() {
    tbodyTabla.innerHTML = '';

    if (trabajadores.length === 0) {
        tbodyTabla.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:32px;color:#717182;">Sin trabajadores registrados</td></tr>';
        return;
    }

    trabajadores.forEach(function (trabajador) {
        var tr = document.createElement('tr');
        tr.innerHTML =
            '<td data-label="ID" class="font-medium" style="font-weight:500;">' + trabajador.id + '</td>' +
            '<td data-label="Nombre">' + trabajador.nombre + '</td>' +
            '<td data-label="Puesto">' + trabajador.puesto + '</td>' +
            '<td data-label="Email">' + trabajador.correo_electronico + '</td>' +
            '<td data-label="Teléfono">' + trabajador.telefono + '</td>' +
            '<td data-label="Salario">' + formatoMoneda(trabajador.salario) + '</td>' +
            '<td>' +
                '<div style="display:flex;gap:8px;">' +
                    '<button class="btn" style="padding:4px 8px;border:1px solid #3e8cdd;color:#3e8cdd;border-radius:4px;background:transparent;cursor:pointer;" onclick="abrirModalEditar(\'' + trabajador.id + '\')">' +
                        '<svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2;"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>' +
                    '</button>' +
                    '<button class="btn" style="padding:4px 8px;border:1px solid #ef4444;color:#ef4444;border-radius:4px;background:transparent;cursor:pointer;" onmouseover="this.style.background=\'#fef2f2\'" onmouseout="this.style.background=\'transparent\'" onclick="eliminarTrabajador(\'' + trabajador.id + '\')">' +
                        '<svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2;"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>' +
                    '</button>' +
                '</div>' +
            '</td>';
        tbodyTabla.appendChild(tr);
    });
}

// ============================================================
// CRUD — MODAL
// ============================================================

function abrirModalNuevo() {
    modoEdicion          = false;
    trabajadorEditandoId = null;
    tituloModal.textContent = 'Agregar Nuevo Trabajador';
    formTrabajador.reset();
    inputId.value = '';
    modalContenedor.style.display = 'flex';
}

function abrirModalEditar(id) {
    modoEdicion = true;
    var trabajador = trabajadores.find(function (t) { return String(t.id) === String(id); });
    if (!trabajador) return;

    trabajadorEditandoId = trabajador.id;
    tituloModal.textContent = 'Editar Trabajador';
    inputId.value       = trabajador.id;
    inputNombre.value   = trabajador.nombre;
    inputPuesto.value   = trabajador.puesto;
    inputSalario.value  = trabajador.salario;
    inputEmail.value    = trabajador.correo_electronico;
    inputTelefono.value = trabajador.telefono;
    modalContenedor.style.display = 'flex';
}

function cerrarModal() {
    modalContenedor.style.display = 'none';
}

// ============================================================
// CRUD — GUARDAR (INSERT o UPDATE)
// ============================================================

function guardarTrabajador(e) {
    e.preventDefault();

    var payload = {
        accion:              modoEdicion ? 'actualizar' : 'insertar',
        nombre:              inputNombre.value.trim(),
        puesto:              inputPuesto.value,
        telefono:            inputTelefono.value.trim(),
        correo_electronico:  inputEmail.value.trim()
    };

    if (modoEdicion) {
        payload.id = trabajadorEditandoId;
    }

    fetch(API_URL, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify(payload)
    })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.error) {
                mostrarNotificacion('Error: ' + data.error);
                return;
            }
            var msg = modoEdicion
                ? 'Trabajador actualizado correctamente'
                : 'Trabajador agregado correctamente';
            mostrarNotificacion(msg);
            cerrarModal();
            cargarTrabajadores();   // Recarga desde BD
        })
        .catch(function (err) {
            mostrarNotificacion('Error de conexión: ' + err);
        });
}

// ============================================================
// CRUD — ELIMINAR
// ============================================================

function eliminarTrabajador(id) {
    if (!confirm('¿Estás seguro de que deseas eliminar este trabajador?')) return;

    fetch(API_URL, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ accion: 'eliminar', id: id })
    })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.error) {
                mostrarNotificacion('Error: ' + data.error);
                return;
            }
            mostrarNotificacion('Trabajador eliminado correctamente');
            cargarTrabajadores();
        })
        .catch(function (err) {
            mostrarNotificacion('Error de conexión: ' + err);
        });
}

// ============================================================
// UTILIDADES
// ============================================================

function mostrarNotificacion(mensaje) {
    notificacion.textContent = mensaje;
    notificacion.style.display     = 'block';
    notificacion.style.position    = 'fixed';
    notificacion.style.top         = '20px';
    notificacion.style.right       = '20px';
    notificacion.style.background  = '#10b981';
    notificacion.style.color       = 'white';
    notificacion.style.padding     = '12px 24px';
    notificacion.style.borderRadius= '8px';
    notificacion.style.boxShadow   = '0 4px 6px -1px rgba(0,0,0,0.1)';
    notificacion.style.zIndex      = '9999';
    setTimeout(function () { notificacion.style.display = 'none'; }, 3000);
}

function cerrarSesion() {
    window.location.href = '../Home/Home.html';
}

function marcarActivo(enlaceClicado) {
    var navLinks = document.querySelectorAll('.sidebar-nav a');
    navLinks.forEach(function (link) { link.classList.remove('activo'); });
    enlaceClicado.classList.add('activo');
}

// ============================================================
// LÓGICA DE FILTROS DINÁMICOS
// ============================================================
function filtrarTrabajadores(campo) {
    campoActivo = campo;
    
    if (campo === 'nombre') {
        ignoreDropdownNombre = false;
        var inputPuesto = document.getElementById('filtro-puesto');
        if (inputPuesto && inputPuesto.value !== '') {
            inputPuesto.value = '';
        }
        var valor = document.getElementById('filtro-nombre').value.trim();
        cargarTrabajadores(valor, true);
    } else if (campo === 'puesto') {
        var inputNombre = document.getElementById('filtro-nombre');
        if (inputNombre && inputNombre.value !== '') {
            inputNombre.value = '';
        }
        var listaN = document.getElementById('lista-busqueda-nombre');
        if (listaN) listaN.style.display = 'none';
        var valor = document.getElementById('filtro-puesto').value;
        cargarTrabajadores(valor, true);
    }
}

function limpiarFiltros() {
    var inputN = document.getElementById('filtro-nombre');
    var inputP = document.getElementById('filtro-puesto');
    
    if (inputN) inputN.value = '';
    if (inputP) inputP.value = '';
    
    var listaN = document.getElementById('lista-busqueda-nombre');
    if (listaN) listaN.style.display = 'none';
    
    campoActivo = null;
    cargarTrabajadores('', true);
}

function renderizarDropdownBusqueda(campo) {
    var listaN = document.getElementById('lista-busqueda-nombre');
    
    if (trabajadores.length === 0) {
        if (listaN) listaN.style.display = 'none';
        return;
    }
    
    if (campo === 'nombre' && listaN && !ignoreDropdownNombre) {
        var html = '';
        for (var i = 0; i < trabajadores.length; i++) {
            var t = trabajadores[i];
            var valEscapado = t.nombre.replace(/'/g, "\\'");
            html += '<li style="padding: 10px; cursor: pointer; border-bottom: 1px solid #eee; text-align: left;" onmouseover="this.style.background=\'#f3f4f6\'" onmouseout="this.style.background=\'white\'" onclick="seleccionarTrabajadorDropdown(\'' + valEscapado + '\')">';
            html += '<strong style="color: #333;">' + t.nombre + '</strong> <span style="color: #666; font-size: 13px;">- ' + t.puesto + '</span>';
            html += '</li>';
        }
        listaN.innerHTML = html;
        listaN.style.display = 'block';
    } else {
        if (listaN) listaN.style.display = 'none';
    }
}

function seleccionarTrabajadorDropdown(valor) {
    var input = document.getElementById('filtro-nombre');
    if (input) input.value = valor;
    var lista = document.getElementById('lista-busqueda-nombre');
    if (lista) lista.style.display = 'none';
    
    ignoreDropdownNombre = true;
    cargarTrabajadores(valor, true);
}

document.addEventListener('click', function(e) {
    var inputN = document.getElementById('filtro-nombre');
    var listaN = document.getElementById('lista-busqueda-nombre');
    if (inputN && listaN) {
        if (e.target !== inputN && e.target !== listaN && !listaN.contains(e.target)) {
            listaN.style.display = 'none';
        }
    }
});
