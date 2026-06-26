/* ============================================================
   ventas.js — Gestión de Ventas (ticket-cards expandibles)
   Misma dinámica que supplies.js
   ============================================================ */

var API_URL = '../PHP/api_ventas.php';

// Datos globales
var ventasTickets = [];
var ventasDetalle = [];
var nombreCliente = {};
var nombreTrabajador = {};
var precios = {};
var areaMaterial = {};
var nombreMaterial = {};
var MATERIALES_LISTA = []; // [{id, nombre, area}]

// Filtros
var filtroDesde = null;
var filtroHasta = null;

var paginaActual     = 1;
var limitePaginacion = 10;

// Gráficas
var graficaTendencia = null;
var graficaArea = null;

/* ============================================================
   INICIALIZACIÓN
   ============================================================ */
window.onload = function () {
    cargarDatos();

    var links = document.querySelectorAll('.sidebar-nav a');
    for (var i = 0; i < links.length; i++) {
        if (links[i].textContent.indexOf('Ventas') !== -1) {
            links[i].classList.add('activo');
            break;
        }
    }
};

/* ============================================================
   CARGA DESDE BASE DE DATOS
   ============================================================ */
function cargarDatos(resetPagina = false) {
    if (resetPagina) {
        paginaActual = 1;
    }

    var filtros = {
        desde: document.getElementById('filtro-desde') ? document.getElementById('filtro-desde').value : '',
        hasta: document.getElementById('filtro-hasta') ? document.getElementById('filtro-hasta').value : '',
        cliente: document.getElementById('filtro-cliente') ? document.getElementById('filtro-cliente').value : '',
        material: document.getElementById('filtro-material') ? document.getElementById('filtro-material').value : ''
    };

    cargarTablaPaginada({
        url: API_URL,
        pagina: paginaActual,
        limite: limitePaginacion,
        filtros: filtros,
        onDataLoaded: function(respuesta) {
            var data = respuesta.data;

            // Tickets
            ventasTickets = (data.tickets || []).map(function (t) {
                return {
                    id: parseInt(t.id),
                    id_clientes: String(t.id_clientes),
                    id_trabajadores: String(t.id_trabajadores),
                    fecha: t.fecha ? t.fecha.split(' ')[0].split('T')[0] : t.fecha
                };
            });

            // Detalles
            ventasDetalle = (data.detalles || []).map(function (d) {
                return {
                    id_ventas: parseInt(d.id_ventas),
                    id_materiales: String(d.id_materiales),
                    cantidad: parseInt(d.cantidad)
                };
            });

            // Clientes (mapa id → nombre)
            var clientesRaw = data.clientes || {};
            nombreCliente = {};
            Object.keys(clientesRaw).forEach(function (k) {
                nombreCliente[String(k)] = clientesRaw[k];
            });

            // Trabajadores (mapa id → nombre)
            var trabRaw = data.trabajadores || {};
            nombreTrabajador = {};
            Object.keys(trabRaw).forEach(function (k) {
                nombreTrabajador[String(k)] = trabRaw[k];
            });

            // Precios (mapa id → precio)
            var preciosRaw = data.precios || {};
            precios = {};
            Object.keys(preciosRaw).forEach(function (k) {
                precios[String(k)] = parseFloat(preciosRaw[k]);
            });

            // Área de materiales (mapa id → area)
            var areaRaw = data.areaMaterial || {};
            areaMaterial = {};
            Object.keys(areaRaw).forEach(function (k) {
                areaMaterial[String(k)] = areaRaw[k];
            });

            // Nombre de materiales (mapa id → nombre)
            var nombreRaw = data.nombreMaterial || {};
            nombreMaterial = {};
            Object.keys(nombreRaw).forEach(function (k) {
                nombreMaterial[String(k)] = nombreRaw[k];
            });

            // Lista de materiales para nombre desde ID
            MATERIALES_LISTA = Object.keys(preciosRaw).map(function (k) {
                return { id: String(k), nombre: nombreRaw[k] || 'Material #' + k, area: areaRaw[k] || '—' };
            });

            // Llenar selects UI eliminado porque ahora se renderiza dinámicamente el autocompletado

            var tickets_all = (data.tickets_all || []).map(function (t) {
                return {
                    id: parseInt(t.id),
                    fecha: t.fecha ? t.fecha.split(' ')[0].split('T')[0] : t.fecha
                };
            });
            if (tickets_all.length === 0 && ventasTickets.length > 0) {
                tickets_all = ventasTickets;
            }

            actualizarKPIs(tickets_all);
            generarGraficaTendenciaVentas(tickets_all);
            generarGraficaAreaVentas(tickets_all);
            renderizarTickets(ventasTickets);

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
            console.error('No se pudo conectar con el servidor:', err);
        }
    });
}

/* ============================================================
   SELECTORES DE FILTROS
   ============================================================ */
var ignoreDropdown = { cliente: false, material: false };

function renderizarDropdownBusqueda(campo) {
    var inputId = campo === 'cliente' ? 'filtro-cliente-input' : 'filtro-material-input';
    var listaId = campo === 'cliente' ? 'lista-busqueda-cliente' : 'lista-busqueda-material';
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

    if (campo === 'cliente') {
        Object.keys(nombreCliente).forEach(function(id) {
            var nombre = nombreCliente[id] || 'Cliente #' + id;
            if (val === '' || nombre.toLowerCase().indexOf(val) > -1) {
                var valEscapado = nombre.replace(/'/g, "\\'");
                html += '<li style="padding: 10px; cursor: pointer; border-bottom: 1px solid #eee; text-align: left;" ' +
                        'onmouseover="this.style.background=\'#f3f4f6\'" onmouseout="this.style.background=\'white\'" ' +
                        'onclick="seleccionarDropdown(\'' + campo + '\', \'' + id + '\', \'' + valEscapado + '\')">';
                html += '<strong style="color: #333;">' + nombre + '</strong>';
                html += '</li>';
                conteo++;
            }
        });
    } else if (campo === 'material') {
        MATERIALES_LISTA.forEach(function(m) {
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

    // Limpiar input hidden si el usuario borró todo
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
    ['cliente', 'material'].forEach(function(campo) {
        var input = document.getElementById('filtro-' + campo + '-input');
        var lista = document.getElementById('lista-busqueda-' + campo);
        if (input && lista) {
            if (e.target !== input && e.target !== lista && !lista.contains(e.target)) {
                lista.style.display = 'none';
            }
        }
    });
});

/* ============================================================
   FILTRADO
   ============================================================ */

function aplicarFiltro() {
    cargarDatos(true);
}

function resetearFiltro() {
    document.getElementById('filtro-desde').value = '';
    document.getElementById('filtro-hasta').value = '';
    
    var fcliInput = document.getElementById('filtro-cliente-input');
    if(fcliInput) fcliInput.value = '';
    var fcli = document.getElementById('filtro-cliente');
    if(fcli) fcli.value = '';
    
    var fmatInput = document.getElementById('filtro-material-input');
    if(fmatInput) fmatInput.value = '';
    var fmat = document.getElementById('filtro-material');
    if(fmat) fmat.value = '';
    
    cargarDatos(true);
}

/* ============================================================
   CÁLCULO
   ============================================================ */
function calcularTotalTicket(idTicket) {
    var total = 0;
    for (var i = 0; i < ventasDetalle.length; i++) {
        if (ventasDetalle[i].id_ventas === idTicket) {
            var precio = precios[ventasDetalle[i].id_materiales] || 0;
            total += ventasDetalle[i].cantidad * precio;
        }
    }
    return total;
}

function formatoMoneda(v) {
    return '$' + parseFloat(v).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatearFecha(f) {
    if (!f) return '';
    var soloFecha = f.split('T')[0].split(' ')[0];
    var p = soloFecha.split('-');
    if (p.length !== 3) return f;
    return p[2] + '/' + p[1] + '/' + p[0];
}

/* ============================================================
   KPIs
   ============================================================ */
function actualizarKPIs(ticketsFiltrados) {
    var totalGeneral = 0;
    for (var i = 0; i < ticketsFiltrados.length; i++) {
        totalGeneral += calcularTotalTicket(ticketsFiltrados[i].id);
    }
    var promedio = ticketsFiltrados.length > 0 ? totalGeneral / ticketsFiltrados.length : 0;

    document.getElementById('kpi-total').textContent = formatoMoneda(totalGeneral);
    document.getElementById('kpi-promedio').textContent = formatoMoneda(promedio);
    document.getElementById('kpi-cantidad').textContent = ticketsFiltrados.length;
}

/* ============================================================
   RENDERIZADO DE TICKET CARDS (idéntico a supplies.js)
   ============================================================ */
function renderizarTickets(lista) {
    var contenedor = document.getElementById('lista-tickets');
    var contador = document.getElementById('contador-resultados');

    if (lista.length === 0) {
        contenedor.innerHTML = '<div style="text-align:center;padding:40px;color:var(--color-muted);">Sin ventas en el período seleccionado</div>';
        contador.textContent = '0 resultados';
        return;
    }

    var html = '';
    lista.forEach(function (t) {
        var total = calcularTotalTicket(t.id);
        var cliente = nombreCliente[t.id_clientes] || 'Cliente #' + t.id_clientes;
        var trabajador = nombreTrabajador[t.id_trabajadores] || 'Trabajador #' + t.id_trabajadores;
        var lineasT = ventasDetalle.filter(function (d) { return d.id_ventas === t.id; });
        var numItems = lineasT.reduce(function (s, d) { return s + d.cantidad; }, 0);

        html += '<div class="ticket-card" id="venta-' + t.id + '">';

        // ── Cabecera (siempre visible) ──
        html += '<div class="ticket-card-header">';
        
        // 1. Cliente
        html += '  <div class="ticket-col-proveedor" style="display:flex; flex-direction:column; gap:4px;">';
        html += '    <span class="ticket-cliente" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; display: inline-block;">' + cliente + '</span>';
        html += '    <span class="ticket-id" style="font-size:0.82rem; color:var(--color-muted);">Ticket #' + t.id + '</span>';
        html += '  </div>';

        // 2. Fecha
        html += '  <div class="ticket-col-fecha">';
        html += '    <span class="ticket-fecha">📅 ' + formatearFecha(t.fecha) + '</span>';
        html += '  </div>';

        // 3. Info Ticket
        html += '  <div class="ticket-col-info" style="display:flex; flex-direction:column; gap:4px;">';
        html += '    <span class="ticket-total">' + formatoMoneda(total) + '</span>';
        html += '    <span style="font-size:0.82rem; color:var(--color-muted);">' + lineasT.length + ' producto(s) · ' + numItems + ' un.</span>';
        html += '  </div>';

        // 4. Acciones
        html += '  <div class="ticket-header-derecha">';
        html += '    <span class="btn-ver-mas" onclick="event.stopPropagation(); toggleDetalle(' + t.id + ')">Ver más ▾</span>';
        html += '  </div>';
        
        html += '</div>';

        // ── Detalle expandible ──
        html += '<div class="ticket-detalle" id="detalle-' + t.id + '">';
        html += '  <table class="detalle-tabla">';
        html += '    <thead><tr><th>Material</th><th>Área</th><th style="text-align:right;">Cantidad</th><th>Precio Unit.</th><th>Subtotal</th></tr></thead>';
        html += '    <tbody>';

        lineasT.forEach(function (d) {
            var precio = precios[d.id_materiales] || 0;
            var area = areaMaterial[d.id_materiales] || '—';
            var sub = d.cantidad * precio;
            html += '<tr>';
            html += '<td>' + (nombreMaterial[d.id_materiales] || 'Material #' + d.id_materiales) + '</td>';
            html += '<td>' + area + '</td>';
            html += '<td style="text-align:right;">' + d.cantidad.toLocaleString('es-MX') + '</td>';
            html += '<td>' + formatoMoneda(precio) + '</td>';
            html += '<td class="detalle-total-row">' + formatoMoneda(sub) + '</td>';
            html += '</tr>';
        });

        html += '    </tbody>';
        html += '    <tfoot><tr>';
        html += '      <td colspan="4" style="text-align:right;font-weight:700;padding:10px;">Total de la Venta:</td>';
        html += '      <td class="detalle-total-row">' + formatoMoneda(total) + '</td>';
        html += '    </tr></tfoot>';
        html += '  </table>';
        html += '  <div class="detalle-footer">';
        html += '    <span style="font-size:0.82rem;color:var(--color-muted);">Cliente: <strong>' + cliente + '</strong></span>';
        html += '    <span style="font-size:0.82rem;color:var(--color-muted);">Atendido por: <strong>' + trabajador + '</strong></span>';
        html += '  </div>';
        html += '</div>';

        html += '</div>'; // fin ticket-card
    });

    contenedor.innerHTML = html;
    contador.textContent = lista.length + ' venta(s)';
}

function toggleDetalle(id) {
    var el = document.getElementById('detalle-' + id);
    var btn = document.querySelector('#venta-' + id + ' .btn-ver-mas');
    if (!el) return;
    if (el.classList.contains('abierto')) {
        el.classList.remove('abierto');
        if (btn) btn.textContent = 'Ver más ▾';
    } else {
        el.classList.add('abierto');
        if (btn) btn.textContent = 'Ocultar ▴';
    }
}

/* ============================================================
   GRÁFICA TENDENCIA (Line)
   ============================================================ */
function generarGraficaTendenciaVentas(ticketsFiltrados) {
    var nombresMeses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    var totalesPorMes = {};

    for (var i = 0; i < ticketsFiltrados.length; i++) {
        var t = ticketsFiltrados[i];
        var fechaObj = new Date(t.fecha + 'T12:00:00');
        var clave = nombresMeses[fechaObj.getMonth()] + ' ' + fechaObj.getFullYear();
        var importe = calcularTotalTicket(t.id);
        totalesPorMes[clave] = (totalesPorMes[clave] || 0) + importe;
    }

    var mIdx = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    var claves = Object.keys(totalesPorMes).sort(function (a, b) {
        var pA = a.split(' '), pB = b.split(' ');
        return new Date(parseInt(pA[1]), mIdx.indexOf(pA[0]), 1) - new Date(parseInt(pB[1]), mIdx.indexOf(pB[0]), 1);
    });
    var valores = claves.map(function (k) { return totalesPorMes[k]; });

    if (graficaTendencia) { graficaTendencia.destroy(); graficaTendencia = null; }

    var canvas = document.getElementById('grafica-tendencia');
    if (!canvas) return;

    graficaTendencia = new Chart(canvas.getContext('2d'), {
        type: 'line',
        data: {
            labels: claves,
            datasets: [{
                label: 'Ventas por Mes ($)',
                data: valores,
                borderColor: '#3e8cdd',
                backgroundColor: 'rgba(62,140,221,0.12)',
                borderWidth: 2,
                pointBackgroundColor: '#3e8cdd',
                pointRadius: 4,
                fill: true,
                tension: 0.35
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: true, position: 'top', labels: { color: '#1a1a1a', font: { size: 12 } } },
                tooltip: {
                    callbacks: {
                        label: function (ctx) {
                            return ' ' + formatoMoneda(ctx.parsed.y);
                        }
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#717182', font: { size: 11 } } },
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.06)' }, ticks: { color: '#717182', font: { size: 11 }, callback: function (v) { return '$' + (v / 1000).toFixed(0) + 'k'; } } }
            }
        }
    });
}

/* ============================================================
   GRÁFICA ÁREA (Doughnut)
   ============================================================ */
function generarGraficaAreaVentas(ticketsFiltrados) {
    var totalesPorArea = {};

    for (var i = 0; i < ticketsFiltrados.length; i++) {
        var idTicket = ticketsFiltrados[i].id;
        for (var j = 0; j < ventasDetalle.length; j++) {
            if (ventasDetalle[j].id_ventas === idTicket) {
                var idMat = ventasDetalle[j].id_materiales;
                var cant = ventasDetalle[j].cantidad;
                var precio = precios[idMat] || 0;
                var area = areaMaterial[idMat] || 'Otro';
                totalesPorArea[area] = (totalesPorArea[area] || 0) + cant * precio;
            }
        }
    }

    var coloresArea = { 'Carpinteria': '#3e8cdd', 'Pintura': '#74cb3c', 'Electricidad': '#5b9bd5', 'Plomeria': '#a5d8a7', 'Otro': '#c0c0c0' };
    var etiquetasArea = Object.keys(totalesPorArea);
    var valoresArea = etiquetasArea.map(function (k) { return totalesPorArea[k]; });
    var bgColores = etiquetasArea.map(function (k) { return coloresArea[k] || '#cccccc'; });

    if (graficaArea) { graficaArea.destroy(); graficaArea = null; }

    var canvas = document.getElementById('grafica-area');
    if (!canvas) return;

    graficaArea = new Chart(canvas.getContext('2d'), {
        type: 'doughnut',
        data: { labels: etiquetasArea, datasets: [{ data: valoresArea, backgroundColor: bgColores, borderWidth: 2, borderColor: '#ffffff' }] },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function (ctx) {
                            var tot = ctx.dataset.data.reduce(function (a, b) { return a + b; }, 0);
                            var pct = tot > 0 ? ((ctx.parsed / tot) * 100).toFixed(1) : 0;
                            return ' ' + formatoMoneda(ctx.parsed) + ' (' + pct + '%)';
                        }
                    }
                }
            }
        }
    });

    var totalArea = valoresArea.reduce(function (a, b) { return a + b; }, 0);
    var leyendaHTML = '';
    for (var m = 0; m < etiquetasArea.length; m++) {
        var pct = totalArea > 0 ? ((valoresArea[m] / totalArea) * 100).toFixed(0) : 0;
        leyendaHTML += '<div class="leyenda-item"><div class="leyenda-color" style="background:' + bgColores[m] + ';"></div>' + etiquetasArea[m] + ' ' + pct + '%</div>';
    }
    var leyendaDiv = document.getElementById('leyenda-area');
    if (leyendaDiv) leyendaDiv.innerHTML = leyendaHTML;
}

/* ============================================================
   UTILIDADES
   ============================================================ */
function marcarActivo(el) {
    var links = document.querySelectorAll('.sidebar-nav a');
    for (var i = 0; i < links.length; i++) links[i].classList.remove('activo');
    el.classList.add('activo');
}

function cerrarSesion() {
    window.location.href = '../Home/Home.html';
}
