/* ============================================================
   proveedores.js — Gestión de Proveedores (dinámica: MySQL via PHP)
   ============================================================ */

var API_URL = '../PHP/api_proveedores.php';

// Variable global de datos (se puebla desde BD)
var proveedores         = [];
var proveedorEditando   = null;
var paginaActual        = 1;
var limitePaginacion    = 10;

/* ============================================================
   INICIALIZACIÓN
   ============================================================ */
window.onload = function () {
    cargarProveedores();

    // Marcar sidebar activo
    var links = document.querySelectorAll('.sidebar-nav a');
    for (var i = 0; i < links.length; i++) {
        if (links[i].textContent.indexOf('Proveedores') !== -1) {
            links[i].classList.add('activo');
            break;
        }
    }

    // Cerrar modal al hacer clic en el fondo
    document.getElementById('modal-proveedores').addEventListener('click', function (e) {
        if (e.target === this) cerrarModal();
    });
};

/* ============================================================
   CARGA DESDE BASE DE DATOS
   ============================================================ */
function cargarProveedores(nombre = '', contacto = '', campoActivo = null, resetPagina = false) {
    if (resetPagina) {
        paginaActual = 1;
    }

    cargarTablaPaginada({
        url: API_URL,
        pagina: paginaActual,
        limite: limitePaginacion,
        filtros: { search_nombre: nombre, search_contacto: contacto },
        onDataLoaded: function(respuesta) {
            proveedores = respuesta.data;
            renderizarTabla();
            
            // Actualizar estadística total real
            var statEl = document.getElementById('stat-total-proveedores');
            if (statEl) statEl.textContent = respuesta.total;
            
            if (campoActivo) {
                renderizarDropdownBusqueda(campoActivo);
            } else {
                var listaN = document.getElementById('lista-busqueda-nombre');
                if (listaN) listaN.style.display = 'none';
                var listaC = document.getElementById('lista-busqueda-contacto');
                if (listaC) listaC.style.display = 'none';
            }
            
            renderizarControlesPaginacion({
                total: respuesta.total,
                limite: respuesta.limit,
                paginaActual: respuesta.page,
                contenedorId: 'paginacion-contenedor',
                onPageChange: function(nuevaPagina) {
                    paginaActual = nuevaPagina;
                    var valorNombre = document.getElementById('filtro-nombre').value.trim();
                    var valorContacto = document.getElementById('filtro-contacto').value.trim();
                    cargarProveedores(valorNombre, valorContacto, null, false);
                }
            });
        },
        onError: function(err) {
            mostrarNotificacion('Error al cargar proveedores: ' + err, 'error');
        }
    });
}

/* ============================================================
   RENDERIZADO
   ============================================================ */
function renderizarTabla() {
    var cuerpo = document.getElementById('cuerpo-tabla-proveedores');

    if (proveedores.length === 0) {
        cuerpo.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:32px;color:#717182;">Sin proveedores registrados</td></tr>';
        return;
    }

    var html = '';
    for (var i = 0; i < proveedores.length; i++) {
        var p = proveedores[i];
        html += '<tr class="fila-tabla">';
        html += '  <td data-label="ID"><strong class="celda-id">' + p.id + '</strong></td>';
        html += '  <td data-label="Nombre">' + p.nombre + '</td>';
        html += '  <td data-label="Contacto">' + p.persona_de_contacto + '</td>';
        html += '  <td data-label="Email" class="celda-email">' + p.correo_electronico + '</td>';
        html += '  <td data-label="Teléfono">' + p.telefono + '</td>';
        html += '  <td>';
        html += '    <div class="acciones-celda">';
        html += '      <button class="btn-accion btn-editar" onclick="abrirModalEditar(' + i + ')" title="Editar">';
        html += '        <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>';
        html += '      </button>';
        html += '      <button class="btn-accion btn-eliminar" onclick="eliminarProveedor(\'' + p.id + '\')" title="Eliminar">';
        html += '        <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>';
        html += '      </button>';
        html += '    </div>';
        html += '  </td>';
        html += '</tr>';
    }
    cuerpo.innerHTML = html;
}

function actualizarEstadisticas() {
    // Ya se actualiza en el onDataLoaded usando la respuesta.total
}

/* ============================================================
   MODAL
   ============================================================ */
function abrirModalNuevo() {
    proveedorEditando = null;
    document.getElementById('modal-titulo').textContent = 'Agregar Nuevo Proveedor';
    document.getElementById('form-proveedor').reset();
    document.getElementById('modal-proveedores').style.display = 'flex';
}

function abrirModalEditar(indice) {
    proveedorEditando = proveedores[indice];
    document.getElementById('modal-titulo').textContent = 'Editar Proveedor';
    document.getElementById('campo-nombre').value   = proveedorEditando.nombre;
    document.getElementById('campo-contacto').value = proveedorEditando.persona_de_contacto;
    document.getElementById('campo-correo').value   = proveedorEditando.correo_electronico;
    document.getElementById('campo-telefono').value = proveedorEditando.telefono;
    document.getElementById('modal-proveedores').style.display = 'flex';
}

function cerrarModal() {
    document.getElementById('modal-proveedores').style.display = 'none';
    document.getElementById('form-proveedor').reset();
    proveedorEditando = null;
}

/* ============================================================
   CRUD — GUARDAR
   ============================================================ */
function guardarProveedor(event) {
    event.preventDefault();

    var nombre   = document.getElementById('campo-nombre').value.trim();
    var contacto = document.getElementById('campo-contacto').value.trim();
    var correo   = document.getElementById('campo-correo').value.trim();
    var telefono = document.getElementById('campo-telefono').value.trim();

    if (!nombre) {
        mostrarNotificacion('El nombre es requerido', 'error');
        return;
    }

    var payload = {
        accion:                 proveedorEditando ? 'actualizar' : 'insertar',
        nombre:                 nombre,
        persona_de_contacto:   contacto,
        correo_electronico:    correo,
        telefono:              telefono
    };

    if (proveedorEditando) {
        payload.id = proveedorEditando.id;
    }

    fetch(API_URL, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify(payload)
    })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.error) {
                mostrarNotificacion('Error: ' + data.error, 'error');
                return;
            }
            var msg = proveedorEditando
                ? '✅ Proveedor actualizado correctamente'
                : '✅ Proveedor agregado correctamente';
            mostrarNotificacion(msg, 'exito');
            cerrarModal();
            cargarProveedores();
        })
        .catch(function (err) {
            mostrarNotificacion('Error de conexión: ' + err, 'error');
        });
}

/* ============================================================
   CRUD — ELIMINAR
   ============================================================ */
function eliminarProveedor(id) {
    if (!confirm('¿Deseas eliminar este proveedor?')) return;

    fetch(API_URL, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ accion: 'eliminar', id: id })
    })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.error) {
                mostrarNotificacion('Error: ' + data.error, 'error');
                return;
            }
            mostrarNotificacion('✅ Proveedor eliminado correctamente', 'exito');
            cargarProveedores();
        })
        .catch(function (err) {
            mostrarNotificacion('Error de conexión: ' + err, 'error');
        });
}

/* ============================================================
   UTILIDADES
   ============================================================ */
function mostrarNotificacion(mensaje, tipo) {
    var notif = document.getElementById('notificacion');
    notif.textContent = mensaje;
    notif.className   = 'notificacion notif-' + (tipo || 'exito');
    notif.style.display = 'block';
    setTimeout(function () { notif.style.display = 'none'; }, 3000);
}

function marcarActivo(el) {
    var links = document.querySelectorAll('.sidebar-nav a');
    for (var i = 0; i < links.length; i++) {
        links[i].classList.remove('activo');
    }
    el.classList.add('activo');
}

function cerrarSesion() {
    window.location.href = '../Home/Home.html';
}

/* ============================================================
   BÚSQUEDA Y FILTRADO CON DROPDOWN
   ============================================================ */
var searchTimeout = null;
var ignoreDropdownNombre = false;
var ignoreDropdownContacto = false;

function filtrarProveedores(campo) {
    var valorNombre = document.getElementById('filtro-nombre').value.trim();
    var valorContacto = document.getElementById('filtro-contacto').value.trim();
    
    if (campo === 'nombre') ignoreDropdownNombre = false;
    if (campo === 'contacto') ignoreDropdownContacto = false;
    
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    
    // Ocultar dropdowns si sus inputs están vacíos
    if (valorNombre === '') {
        var listaN = document.getElementById('lista-busqueda-nombre');
        if (listaN) listaN.style.display = 'none';
    }
    if (valorContacto === '') {
        var listaC = document.getElementById('lista-busqueda-contacto');
        if (listaC) listaC.style.display = 'none';
    }
    
    if (valorNombre === '' && valorContacto === '') {
        cargarProveedores('', '', null, true);
        return;
    }
    
    searchTimeout = setTimeout(function() {
        cargarProveedores(valorNombre, valorContacto, campo, true);
    }, 300);
}

function limpiarBusqueda() {
    document.getElementById('filtro-nombre').value = '';
    document.getElementById('filtro-contacto').value = '';
    var listaN = document.getElementById('lista-busqueda-nombre');
    if (listaN) listaN.style.display = 'none';
    var listaC = document.getElementById('lista-busqueda-contacto');
    if (listaC) listaC.style.display = 'none';
    cargarProveedores('', '', null, true);
}

function renderizarDropdownBusqueda(campoActivo) {
    var listaN = document.getElementById('lista-busqueda-nombre');
    var listaC = document.getElementById('lista-busqueda-contacto');
    
    // Solo mostramos dropdown si hay resultados
    if (proveedores.length === 0) {
        if (listaN) listaN.style.display = 'none';
        if (listaC) listaC.style.display = 'none';
        return;
    }
    
    if (campoActivo === 'nombre' && listaN && !ignoreDropdownNombre) {
        var html = '';
        for (var i = 0; i < proveedores.length; i++) {
            var p = proveedores[i];
            var valEscapado = p.nombre.replace(/'/g, "\\'");
            html += '<li style="padding: 10px; cursor: pointer; border-bottom: 1px solid #eee; text-align: left;" onmouseover="this.style.background=\'#f3f4f6\'" onmouseout="this.style.background=\'white\'" onclick="seleccionarProveedorDropdown(\'nombre\', \'' + valEscapado + '\')">';
            html += '<strong style="color: #333;">' + p.nombre + '</strong>';
            html += '</li>';
        }
        listaN.innerHTML = html;
        listaN.style.display = 'block';
    } else {
        if (listaN) listaN.style.display = 'none';
    }
    
    if (campoActivo === 'contacto' && listaC && !ignoreDropdownContacto) {
        var html = '';
        for (var i = 0; i < proveedores.length; i++) {
            var p = proveedores[i];
            var valEscapado = p.persona_de_contacto.replace(/'/g, "\\'");
            html += '<li style="padding: 10px; cursor: pointer; border-bottom: 1px solid #eee; text-align: left;" onmouseover="this.style.background=\'#f3f4f6\'" onmouseout="this.style.background=\'white\'" onclick="seleccionarProveedorDropdown(\'contacto\', \'' + valEscapado + '\')">';
            html += '<strong style="color: #333;">' + p.persona_de_contacto + '</strong> <span style="color: #666; font-size: 13px;">- ' + p.nombre + '</span>';
            html += '</li>';
        }
        listaC.innerHTML = html;
        listaC.style.display = 'block';
    } else {
        if (listaC) listaC.style.display = 'none';
    }
}

function seleccionarProveedorDropdown(campo, valor) {
    if (campo === 'nombre') {
        var input = document.getElementById('filtro-nombre');
        input.value = valor;
        var lista = document.getElementById('lista-busqueda-nombre');
        if (lista) lista.style.display = 'none';
        ignoreDropdownNombre = true;
    } else {
        var input = document.getElementById('filtro-contacto');
        input.value = valor;
        var lista = document.getElementById('lista-busqueda-contacto');
        if (lista) lista.style.display = 'none';
        ignoreDropdownContacto = true;
    }
    
    var valorNombre = document.getElementById('filtro-nombre').value.trim();
    var valorContacto = document.getElementById('filtro-contacto').value.trim();
    
    cargarProveedores(valorNombre, valorContacto);
}

// Ocultar dropdown al hacer clic fuera del input y del dropdown
document.addEventListener('click', function(e) {
    var inputN = document.getElementById('filtro-nombre');
    var listaN = document.getElementById('lista-busqueda-nombre');
    if (inputN && listaN) {
        if (e.target !== inputN && e.target !== listaN && !listaN.contains(e.target)) {
            listaN.style.display = 'none';
        }
    }
    
    var inputC = document.getElementById('filtro-contacto');
    var listaC = document.getElementById('lista-busqueda-contacto');
    if (inputC && listaC) {
        if (e.target !== inputC && e.target !== listaC && !listaC.contains(e.target)) {
            listaC.style.display = 'none';
        }
    }
});
