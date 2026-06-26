/* ============================================================
   admin_facturas.js — Gestión de Facturas (dinámica: MySQL via PHP)
   ============================================================ */

var API_URL = '../PHP/api_facturas.php';

// Datos globales – se poblarán desde la BD
var facturas = [];
var ventasDetalle = [];
var clientesDB = {};
var precios = {};

// Variables de filtro
var filtroDesde = null;
var filtroHasta = null;

var paginaActual = 1;
var limitePaginacion = 10;

/* ============================================================
   DICCIONARIO DE USO CFDI (constante, no viene de BD)
   ============================================================ */
var descripcionCFDI = {
    "G01": "Adquisición de bienes",
    "G02": "Devoluciones / descuentos",
    "G03": "Gastos en general",
    "I01": "Construcciones",
    "I02": "Mobiliario de oficina",
    "D04": "Donativos",
    "D07": "Primas por seguros",
    "D08": "Gastos de transportación",
    "P01": "Por definir"
};

/* ============================================================
   INICIALIZACIÓN
   ============================================================ */
window.onload = function () {
    cargarDatos();

    // Marcar sidebar activo
    var links = document.querySelectorAll('.sidebar-nav a');
    for (var i = 0; i < links.length; i++) {
        if (links[i].textContent.indexOf('Facturas') !== -1) {
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
        cliente: document.getElementById('filtro-cliente') ? document.getElementById('filtro-cliente').value : ''
    };

    cargarTablaPaginada({
        url: API_URL,
        pagina: paginaActual,
        limite: limitePaginacion,
        filtros: filtros,
        onDataLoaded: function (respuesta) {
            var data = respuesta.data;
            facturas = data.facturas || [];
            ventasDetalle = data.detalles || [];
            clientesDB = data.clientes || {};
            precios = data.precios || {};

            var preciosNorm = {};
            Object.keys(precios).forEach(function (k) {
                preciosNorm[String(k)] = parseFloat(precios[k]);
            });
            precios = preciosNorm;

            ventasDetalle = ventasDetalle.map(function (d) {
                return {
                    id_ventas: parseInt(d.id_ventas),
                    id_materiales: String(d.id_materiales),
                    cantidad: parseInt(d.cantidad)
                };
            });

            facturas = facturas.map(function (f) {
                return {
                    folio: f.folio,
                    id_ventas: parseInt(f.id_ventas),
                    id_clientes: String(f.id_clientes),
                    fecha: f.fecha,
                    uso_cfdi: f.uso_cfdi,
                    pdf: f.pdf
                };
            });

            // Parse filter dates locally for stats/chart if needed, but the server applies it to data
            var valDesde = document.getElementById('filtro-desde') ? document.getElementById('filtro-desde').value : '';
            var valHasta = document.getElementById('filtro-hasta') ? document.getElementById('filtro-hasta').value : '';
            filtroDesde = valDesde ? new Date(valDesde + 'T00:00:00') : null;
            filtroHasta = valHasta ? new Date(valHasta + 'T23:59:59') : null;

            var facturas_all = (data.facturas_all || []).map(function (f) {
                return {
                    folio: parseInt(f.folio),
                    id_ventas: parseInt(f.id_ventas),
                    fecha: f.fecha ? f.fecha.split(' ')[0].split('T')[0] : f.fecha
                };
            });
            if (facturas_all.length === 0 && facturas.length > 0) {
                facturas_all = facturas;
            }

            actualizarEstadisticasFacturas(facturas_all);
            generarGraficaFacturas(facturas_all);
            renderizarTablaFacturas(facturas);

            var statEl = document.getElementById('stat-total-gf_facturas');
            if (statEl) statEl.textContent = respuesta.total;

            renderizarControlesPaginacion({
                total: respuesta.total,
                limite: respuesta.limit,
                paginaActual: respuesta.page,
                contenedorId: 'paginacion-contenedor',
                onPageChange: function (nuevaPagina) {
                    paginaActual = nuevaPagina;
                    cargarDatos(false);
                }
            });
        },
        onError: function (err) {
            console.error('No se pudo conectar con el servidor:', err);
        }
    });
}

/* ============================================================
   CÁLCULO DE TOTALES
   ============================================================ */
function calcularTotalFactura(idTicket) {
    var total = 0;
    for (var i = 0; i < ventasDetalle.length; i++) {
        if (ventasDetalle[i].id_ventas === idTicket) {
            var idMaterial = String(ventasDetalle[i].id_materiales);
            var cantidad = ventasDetalle[i].cantidad;
            var precioUnit = precios[idMaterial] || 0;
            total += cantidad * precioUnit;
        }
    }
    return total;
}

function aplicarFiltro() {
    cargarDatos(true);
}

function resetearFiltro() {
    if (document.getElementById('filtro-desde')) document.getElementById('filtro-desde').value = '';
    if (document.getElementById('filtro-hasta')) document.getElementById('filtro-hasta').value = '';
    
    var fcliInput = document.getElementById('filtro-cliente-input');
    if (fcliInput) fcliInput.value = '';
    var fcli = document.getElementById('filtro-cliente');
    if (fcli) fcli.value = '';

    filtroDesde = null;
    filtroHasta = null;
    cargarDatos(true);
}

// ============================================================
// AUTOCOMPLETADO DE FILTROS (NUEVO UI)
// ============================================================
var ignoreDropdown = { cliente: false };

function renderizarDropdownBusqueda(campo) {
    if (campo !== 'cliente') return;
    var inputId = 'filtro-cliente-input';
    var listaId = 'lista-busqueda-cliente';
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

    Object.keys(clientesDB).forEach(function(id) {
        var nombre = clientesDB[id] || 'Cliente #' + id;
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
    var input = document.getElementById('filtro-cliente-input');
    var lista = document.getElementById('lista-busqueda-cliente');
    if (input && lista) {
        if (e.target !== input && e.target !== lista && !lista.contains(e.target)) {
            lista.style.display = 'none';
        }
    }
});

document.addEventListener("DOMContentLoaded", () => {
    const btnLimpiar = document.getElementById("btnLimpiarFiltros");
    if (btnLimpiar) {
        btnLimpiar.addEventListener("click", resetearFiltro);
    }
});

/* ============================================================
   RENDERIZADO
   ============================================================ */
function renderizarTablaFacturas(facturasAMostrar) {
    var datos = facturasAMostrar || facturas;
    var cuerpo = document.getElementById('cuerpo-tabla-facturas');

    if (datos.length === 0) {
        cuerpo.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:32px;color:#717182;">Sin facturas en el período seleccionado</td></tr>';
        return;
    }

    var html = '';
    for (var i = 0; i < datos.length; i++) {
        var f = datos[i];
        var total = calcularTotalFactura(f.id_ventas);
        var fechaObj = new Date(f.fecha + 'T12:00:00');
        var fechaFormateada = fechaObj.toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' });
        var tipoCFDI = descripcionCFDI[f.uso_cfdi] || f.uso_cfdi;
        var totalFormateado = '$' + total.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        var nombreCliente = clientesDB[f.id_clientes] || f.id_clientes;

        // Celda PDF
        var pdfCelda = '';
        if (f.pdf && f.pdf.trim() !== '') {
            var fileName = f.pdf.trim();
            // El botón llamará al modal, pasándole la URL de la API
            pdfCelda = '<button onclick="abrirModalPdf(\'../PHP/api_clientes_admin.php?accion=ver_pdf&folio=' + f.folio + '\')" '
                + 'style="background:#3e8cdd;color:white;border:none;border-radius:5px;'
                + 'padding:5px 12px;cursor:pointer;font-size:0.8rem;font-weight:600;">'
                + '📄 Ver PDF</button>';
        } else {
            pdfCelda = '<span style="color:#aaa;font-size:0.8rem;">Sin PDF</span>';
        }

        html += '<tr class="fila-tabla">';
        html += '  <td data-label="Folio"><strong class="celda-id">' + f.folio + '</strong></td>';
        html += '  <td data-label="ID Ticket">' + f.id_ventas + '</td>';
        html += '  <td data-label="Cliente">' + nombreCliente + '</td>';
        html += '  <td data-label="Fecha" class="celda-fecha">' + fechaFormateada + '</td>';
        html += '  <td data-label="Tipo CFDI"><span class="badge-cfdi badge-' + f.uso_cfdi + '">' + f.uso_cfdi + ' — ' + tipoCFDI + '</span></td>';
        html += '  <td data-label="Total" class="celda-precio celda-total">' + totalFormateado + '</td>';
        html += '  <td>' + pdfCelda + '</td>';
        html += '</tr>';
    }
    cuerpo.innerHTML = html;
}

function actualizarEstadisticasFacturas(facturasAMostrar) {
    var datos = facturasAMostrar || facturas;
    // stat-total-gf_facturas ya se actualiza en cargarDatos con respuesta.total

    var totalGeneral = 0;
    for (var i = 0; i < datos.length; i++) {
        totalGeneral += calcularTotalFactura(datos[i].id_ventas);
    }
    document.getElementById('stat-total-facturado').textContent = '$' + totalGeneral.toLocaleString('es-MX', {
        minimumFractionDigits: 2, maximumFractionDigits: 2
    });
}

/* ============================================================
   GRÁFICA (igual que antes — solo datos dinámicos)
   ============================================================ */
function generarGraficaFacturas(facturasAMostrar) {
    var datos = facturasAMostrar || facturas;
    var nombresMeses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    var totalesPorMes = {};

    for (var i = 0; i < datos.length; i++) {
        var f = datos[i];
        var fechaObj = new Date(f.fecha + 'T12:00:00');
        var clave = nombresMeses[fechaObj.getMonth()] + ' ' + fechaObj.getFullYear();
        var importe = calcularTotalFactura(f.id_ventas);
        totalesPorMes[clave] = (totalesPorMes[clave] || 0) + importe;
    }

    var claves = Object.keys(totalesPorMes).sort(function (a, b) {
        var mesesIdx = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        var pA = a.split(' '), pB = b.split(' ');
        var dA = new Date(parseInt(pA[1]), mesesIdx.indexOf(pA[0]), 1);
        var dB = new Date(parseInt(pB[1]), mesesIdx.indexOf(pB[0]), 1);
        return dA - dB;
    });

    var etiquetas = claves;
    var valores = claves.map(function (k) { return totalesPorMes[k]; });

    if (window.graficaFacturas) window.graficaFacturas.destroy();

    var canvas = document.getElementById('grafica-facturas');
    if (!canvas) return;
    var ctx = canvas.getContext('2d');

    window.graficaFacturas = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: etiquetas,
            datasets: [{
                label: 'Total Facturado ($)',
                data: valores,
                backgroundColor: 'rgba(62, 140, 221, 0.80)',
                borderColor: '#3e8cdd',
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true, position: 'top' },
                tooltip: {
                    callbacks: {
                        label: function (ctx) {
                            return ' $' + ctx.parsed.y.toLocaleString('es-MX', {
                                minimumFractionDigits: 2, maximumFractionDigits: 2
                            });
                        }
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#717182' } },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.06)' },
                    ticks: { callback: function (v) { return '$' + (v / 1000).toFixed(0) + 'k'; } }
                }
            }
        }
    });
}

/* ============================================================
   UTILIDADES
   ============================================================ */
function cerrarSesion() {
    window.location.href = '../Home/Home.html';
}
