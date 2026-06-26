// ============================================================
// clientes.js — Gestión de Clientes (dinámica: MySQL via PHP)
// ============================================================

var API_URL = '../PHP/api_clientes_admin.php';

// Variables globales para la vista principal
var clientes = [];
var paginaActual = 1;
var limitePaginacion = 10;
var campoActivo = null;

// DOM Elements
var tbodyTabla = document.getElementById('cuerpo-tabla');
var statTotalClientes = document.getElementById('stat-total-clientes');

// Variables para el modal "Ver más"
var modalBg = document.getElementById('modalBg');
var cuerpoTablaCompras = document.getElementById('cuerpo-tabla-compras');
var cuerpoTablaFacturas = document.getElementById('cuerpo-tabla-facturas');
var contenedorCompras = document.getElementById('contenedor-compras');
var contenedorFacturas = document.getElementById('contenedor-facturas');
var pestanaCompras = document.getElementById('tab-compras');
var pestanaFacturas = document.getElementById('tab-facturas');

// Datos del Modal
var modalTickets = [];
var modalFacturas = [];
var modalPaginaCompras = 1;
var modalPaginaFacturas = 1;
var modalLimite = 5;

// ============================================================
// INICIALIZACIÓN
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    cargarClientes('', true);
    actualizarTotalClientes();

    // Click fuera del dropdown de búsqueda lo cierra
    document.addEventListener('click', function(e) {
        var dropdown = document.getElementById('lista-busqueda-cliente');
        var input = document.getElementById('filtro-nombre-cliente');
        if (dropdown && input && !dropdown.contains(e.target) && e.target !== input) {
            dropdown.style.display = 'none';
        }
    });
});

// ============================================================
// CARGA DESDE BASE DE DATOS (CLIENTES)
// ============================================================
function actualizarTotalClientes() {
    fetch(API_URL + '?accion=total_clientes')
        .then(res => res.json())
        .then(data => {
            if (data.total !== undefined && statTotalClientes) {
                statTotalClientes.textContent = data.total;
            }
        });
}

function cargarClientes(busqueda = '', resetPagina = false) {
    if (resetPagina) {
        paginaActual = 1;
    }

    if (typeof cargarTablaPaginada !== 'function') {
        console.error("No se encontró cargarTablaPaginada. Verifica paginacion.js");
        return;
    }

    cargarTablaPaginada({
        url: API_URL,
        pagina: paginaActual,
        limite: limitePaginacion,
        filtros: { search: busqueda },
        onDataLoaded: function(respuesta) {
            clientes = respuesta.data;
            renderizarTabla();
            
            renderizarControlesPaginacion({
                total: respuesta.total,
                limite: respuesta.limit,
                paginaActual: respuesta.page,
                contenedorId: 'paginacion-contenedor',
                onPageChange: function(nuevaPagina) {
                    paginaActual = nuevaPagina;
                    var valNombre = document.getElementById('filtro-nombre-cliente').value.trim();
                    cargarClientes(valNombre, false);
                }
            });

            if (campoActivo) {
                renderizarDropdownBusqueda(campoActivo);
            } else {
                var listaN = document.getElementById('lista-busqueda-cliente');
                if (listaN) listaN.style.display = 'none';
            }
        },
        onError: function(err) {
            alert('Error cargando clientes: ' + err);
        }
    });
}

// ============================================================
// FILTROS Y AUTOCOMPLETADO
// ============================================================
function renderizarDropdownBusqueda(tipo) {
    campoActivo = tipo;
    var input = document.getElementById('filtro-nombre-cliente');
    var lista = document.getElementById('lista-busqueda-cliente');
    var busqueda = input.value.trim().toLowerCase();

    lista.innerHTML = '';

    if (busqueda === '') {
        lista.style.display = 'none';
        cargarClientes('', true);
        campoActivo = null;
        return;
    }

    cargarClientes(busqueda, true);
    lista.style.display = 'block';

    if (clientes.length === 0) {
        var li = document.createElement('li');
        li.textContent = "No hay resultados";
        li.style.padding = '8px 12px';
        li.style.color = '#999';
        lista.appendChild(li);
        return;
    }

    clientes.forEach(function (c) {
        var li = document.createElement('li');
        li.textContent = c.nombre;
        li.style.padding = '8px 12px';
        li.style.cursor = 'pointer';
        li.style.borderBottom = '1px solid #eee';
        
        li.onmouseover = function() { li.style.backgroundColor = '#f1f5f9'; };
        li.onmouseout = function() { li.style.backgroundColor = 'transparent'; };
        
        li.onclick = function() { seleccionarDropdown(c.nombre, tipo); };
        
        lista.appendChild(li);
    });
}

function seleccionarDropdown(valor, tipo) {
    var input = document.getElementById('filtro-nombre-cliente');
    var lista = document.getElementById('lista-busqueda-cliente');
    input.value = valor;
    lista.style.display = 'none';
    campoActivo = null;
    cargarClientes(valor, true);
}

function limpiarFiltros() {
    var input = document.getElementById('filtro-nombre-cliente');
    input.value = '';
    var lista = document.getElementById('lista-busqueda-cliente');
    if (lista) lista.style.display = 'none';
    campoActivo = null;
    cargarClientes('', true);
}

// ============================================================
// TABLA PRINCIPAL
// ============================================================
function renderizarTabla() {
    tbodyTabla.innerHTML = '';
    if (clientes.length === 0) {
        var tr = document.createElement('tr');
        var td = document.createElement('td');
        td.colSpan = 5;
        td.textContent = 'No se encontraron clientes.';
        td.style.textAlign = 'center';
        tr.appendChild(td);
        tbodyTabla.appendChild(tr);
        return;
    }

    clientes.forEach(function (c) {
        var tr = document.createElement('tr');
        
        var constanciaHtml = '<span style="color:#9ca3af;font-style:italic;">No agregó constancia</span>';
        if (c.tiene_constancia == 1) {
            constanciaHtml = `<a href="javascript:void(0);" onclick="abrirModalPdf('${API_URL}?accion=ver_constancia&id=${c.id}')" style="display:inline-flex;align-items:center;gap:5px;padding:5px 10px;background:#f1f5f9;color:#3e8cdd;border-radius:4px;text-decoration:none;font-weight:600;font-size:0.8rem;"><svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg> Ver</a>`;
        }

        tr.innerHTML = `
            <td>${c.nombre}</td>
            <td>${c.correo_electronico || '-'}</td>
            <td>${c.telefono || '-'}</td>
            <td>${constanciaHtml}</td>
            <td>
                <div class="acciones" style="display:flex; gap:8px; align-items:center;">
                    <button class="btn-ver-mas" onclick="abrirModalVerMas(${c.id})" style="font-size: 0.8rem; font-weight: 600; color: #3e8cdd; background: rgba(62, 140, 221, 0.1); border: 1px solid rgba(62, 140, 221, 0.25); border-radius: 20px; padding: 4px 14px; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='rgba(62, 140, 221, 0.2)'" onmouseout="this.style.background='rgba(62, 140, 221, 0.1)'">Ver más ▾</button>
                    <button class="btn" style="padding:4px 8px;border:1px solid #ef4444;color:#ef4444;border-radius:4px;background:transparent;cursor:pointer;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'" title="Eliminar Cliente" onclick="eliminarCliente(${c.id})">
                        <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2;"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                    </button>
                </div>
            </td>
        `;
        tbodyTabla.appendChild(tr);
    });
}

function eliminarCliente(id) {
    if (!confirm('¿Seguro que desea eliminar a este cliente? Esta acción no se puede deshacer.')) return;
    
    fetch(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ accion: 'eliminar', id: id })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            actualizarTotalClientes();
            cargarClientes(document.getElementById('filtro-nombre-cliente').value.trim(), false);
        } else {
            alert('Error eliminando cliente: ' + data.error);
        }
    })
    .catch(err => alert('No se pudo conectar: ' + err));
}

// ============================================================
// MODAL "VER MÁS" Y SUBVISTA
// ============================================================
function abrirModalVerMas(idCliente) {
    modalBg.classList.add('open');
    cambiarPestana('compras');
    
    var listaCompras = document.getElementById('lista-compras-tickets');
    if(listaCompras) listaCompras.innerHTML = '<div style="text-align:center;">Cargando...</div>';
    
    if(cuerpoTablaFacturas) cuerpoTablaFacturas.innerHTML = '<tr><td colspan="4" style="text-align:center;">Cargando...</td></tr>';
    
    fetch(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ accion: 'compras_facturas', id: idCliente })
    })
    .then(res => res.json())
    .then(data => {
        if (data.error) {
            alert('Error: ' + data.error);
            cerrarModal();
            return;
        }
        modalTickets = data.tickets || [];
        modalFacturas = data.facturas || [];
        modalPaginaCompras = 1;
        modalPaginaFacturas = 1;
        
        renderizarComprasModal();
        renderizarFacturasModal();
    })
    .catch(err => {
        alert('Error conectando: ' + err);
        cerrarModal();
    });
}

function cerrarModal() {
    modalBg.classList.remove('open');
}

function cambiarPestana(tipo) {
    if (tipo === 'compras') {
        pestanaCompras.classList.add('active');
        pestanaFacturas.classList.remove('active');
        contenedorCompras.style.display = 'block';
        contenedorFacturas.style.display = 'none';
    } else {
        pestanaFacturas.classList.add('active');
        pestanaCompras.classList.remove('active');
        contenedorFacturas.style.display = 'block';
        contenedorCompras.style.display = 'none';
    }
}

// ============================================================
// PAGINACIÓN INTERNA DEL MODAL
// ============================================================
function toggleDetalleTicket(id) {
    var el = document.getElementById('detalle-ticket-' + id);
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

function renderizarComprasModal() {
    var inicio = (modalPaginaCompras - 1) * modalLimite;
    var fin = inicio + modalLimite;
    var datosPagina = modalTickets.slice(inicio, fin);
    
    var listaCompras = document.getElementById('lista-compras-tickets');
    listaCompras.innerHTML = '';
    
    if (datosPagina.length === 0) {
        listaCompras.innerHTML = '<div style="text-align:center;color:#9ca3af;padding:20px;">No hay compras registradas</div>';
    } else {
        var html = '';
        datosPagina.forEach(function(t) {
            var totalTicket = parseFloat(t.total) || 0;
            
            html += '<div class="ticket-card" id="ticket-' + t.id_ticket + '">';
            html += '  <div class="ticket-card-header" onclick="toggleDetalleTicket(' + t.id_ticket + ')">';
            html += '    <div class="ticket-cliente" style="font-weight:600;"><span style="color:#64748b;font-weight:400;">Ticket #</span>' + String(t.id_ticket).padStart(5, '0') + '</div>';
            html += '    <div class="ticket-fecha">' + t.fecha.split(' ')[0] + '</div>';
            html += '    <div class="ticket-total">$' + totalTicket.toLocaleString('es-MX', {minimumFractionDigits: 2}) + '</div>';
            html += '    <div class="ticket-header-derecha" style="text-align:right;"><span class="btn-ver-mas" onclick="event.stopPropagation(); toggleDetalleTicket(' + t.id_ticket + ')">Ver más ▾</span></div>';
            html += '  </div>';
            
            html += '  <div class="ticket-detalle" id="detalle-ticket-' + t.id_ticket + '">';
            html += '    <table class="detalle-tabla">';
            html += '      <thead><tr><th>Cant.</th><th>Material</th><th>Área</th><th>Precio Unit.</th><th style="text-align:right;">Subtotal</th></tr></thead>';
            html += '      <tbody>';
            
            if (t.detalles && t.detalles.length > 0) {
                t.detalles.forEach(function(d) {
                    var precio = parseFloat(d.precio) || 0;
                    var sub = parseFloat(d.subtotal) || 0;
                    html += '<tr>';
                    html += '<td>' + d.cantidad + '</td>';
                    html += '<td>' + d.material + '</td>';
                    html += '<td>' + (d.area || '—') + '</td>';
                    html += '<td>$' + precio.toLocaleString('es-MX', {minimumFractionDigits: 2}) + '</td>';
                    html += '<td class="detalle-total-row" style="text-align:right;">$' + sub.toLocaleString('es-MX', {minimumFractionDigits: 2}) + '</td>';
                    html += '</tr>';
                });
            } else {
                html += '<tr><td colspan="5" style="text-align:center;">No hay detalles</td></tr>';
            }
            
            html += '      </tbody>';
            html += '    </table>';
            html += '  </div>';
            html += '</div>';
        });
        listaCompras.innerHTML = html;
    }
    
    renderPaginacionModal(modalTickets.length, modalPaginaCompras, 'paginacion-compras', function(nPage) {
        modalPaginaCompras = nPage;
        renderizarComprasModal();
    });
}

function renderizarFacturasModal() {
    var inicio = (modalPaginaFacturas - 1) * modalLimite;
    var fin = inicio + modalLimite;
    var datosPagina = modalFacturas.slice(inicio, fin);
    
    cuerpoTablaFacturas.innerHTML = '';
    
    if (datosPagina.length === 0) {
        cuerpoTablaFacturas.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#9ca3af;">No hay facturas registradas</td></tr>';
    } else {
        datosPagina.forEach(function(f) {
            var tr = document.createElement('tr');
            var btnPdf = f.tiene_pdf == 1 
                ? `<a href="javascript:void(0);" onclick="abrirModalPdf('${API_URL}?accion=ver_pdf&folio=${f.folio}')" style="padding:4px 8px;background:#74cb3c;color:white;border-radius:4px;text-decoration:none;font-size:0.8rem;font-weight:600;">Ver PDF</a>` 
                : `<span style="color:#9ca3af;font-size:0.8rem;">Sin PDF</span>`;
            
            tr.innerHTML = `
                <td style="font-weight:700;color:#3e8cdd;">#${String(f.folio).padStart(4, '0')}</td>
                <td>${f.fecha}</td>
                <td><span style="background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:0.8rem;">${f.uso_cfdi}</span></td>
                <td>${btnPdf}</td>
            `;
            cuerpoTablaFacturas.appendChild(tr);
        });
    }
    
    renderPaginacionModal(modalFacturas.length, modalPaginaFacturas, 'paginacion-facturas', function(nPage) {
        modalPaginaFacturas = nPage;
        renderizarFacturasModal();
    });
}

function renderPaginacionModal(total, currentPage, containerId, onPageClick) {
    var container = document.getElementById(containerId);
    container.innerHTML = '';
    
    var totalPages = Math.ceil(total / modalLimite);
    if (totalPages <= 1) return;
    
    var btnPrev = document.createElement('button');
    btnPrev.className = 'btn-page';
    btnPrev.textContent = '«';
    btnPrev.disabled = (currentPage === 1);
    btnPrev.onclick = function() { onPageClick(currentPage - 1); };
    container.appendChild(btnPrev);
    
    for (let i = 1; i <= totalPages; i++) {
        var btn = document.createElement('button');
        btn.className = 'btn-page';
        btn.textContent = i;
        if (i === currentPage) {
            btn.style.background = '#3e8cdd';
            btn.style.color = 'white';
            btn.style.borderColor = '#3e8cdd';
        }
        btn.onclick = function() { onPageClick(i); };
        container.appendChild(btn);
    }
    
    var btnNext = document.createElement('button');
    btnNext.className = 'btn-page';
    btnNext.textContent = '»';
    btnNext.disabled = (currentPage === totalPages);
    btnNext.onclick = function() { onPageClick(currentPage + 1); };
    container.appendChild(btnNext);
}
