/* ============================================================
   paginacion.js — Funciones globales para paginación de tablas
   ============================================================ */

/**
 * Función global para cargar datos de tabla con paginación
 * @param {Object} config Configuración
 * @param {string} config.url - URL del endpoint API
 * @param {number} config.pagina - Página actual (1-indexed)
 * @param {number} config.limite - Registros por página
 * @param {Object} config.filtros - Objeto con filtros clave: valor
 * @param {Function} config.onDataLoaded - Callback a llamar con la data (data.data, data.total)
 * @param {Function} config.onError - Callback a llamar en caso de error
 */
function cargarTablaPaginada({ url, pagina = 1, limite = 10, filtros = {}, onDataLoaded, onError }) {
    var queryParams = new URLSearchParams();
    
    // Agregar paginación
    queryParams.append('page', pagina);
    queryParams.append('limit', limite);
    
    // Agregar filtros si existen
    for (var key in filtros) {
        if (filtros[key] !== undefined && filtros[key] !== null && filtros[key] !== '') {
            queryParams.append(key, filtros[key]);
        }
    }
    
    var urlFinal = url + '?' + queryParams.toString();
    
    fetch(urlFinal)
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.error) {
                if (onError) onError(data.error);
                return;
            }
            if (onDataLoaded) {
                // Asumimos que el backend retorna { data: [...], total: X, page: Y, limit: Z }
                onDataLoaded(data);
            }
        })
        .catch(function(err) {
            if (onError) onError(err);
        });
}

/**
 * Renderiza los controles de paginación
 * @param {Object} config Configuración
 * @param {number} config.total - Total de registros
 * @param {number} config.limite - Registros por página
 * @param {number} config.paginaActual - Página actual
 * @param {string} config.contenedorId - ID del contenedor div
 * @param {Function} config.onPageChange - Callback al cambiar página, recibe (nuevaPagina)
 */
function renderizarControlesPaginacion({ total, limite, paginaActual, contenedorId, onPageChange }) {
    var contenedor = document.getElementById(contenedorId);
    if (!contenedor) return;
    
    var totalPaginas = Math.ceil(total / limite);
    
    // Si no hay datos y estamos en la primera página, no mostrar paginación
    if (totalPaginas <= 1) {
        contenedor.innerHTML = '';
        return;
    }
    
    var esPrimera = (paginaActual <= 1);
    var esUltima = (paginaActual >= totalPaginas);
    
    var html = `
        <div class="paginacion-controles" style="display: flex; align-items: center; justify-content: center; gap: 15px; margin-top: 20px; padding: 10px;">
            <button id="btn-prev-page" class="btn-paginacion" ${esPrimera ? 'disabled' : ''} style="padding: 8px 16px; border: 1px solid #ddd; background: ${esPrimera ? '#f3f4f6' : '#fff'}; color: ${esPrimera ? '#9ca3af' : '#3e8cdd'}; border-radius: 6px; cursor: ${esPrimera ? 'not-allowed' : 'pointer'}; font-weight: 600; transition: background 0.2s;">
                Anterior
            </button>
            <span style="font-size: 0.9rem; color: #666; font-weight: 500;">
                Página <strong style="color: #333;">${paginaActual}</strong> de ${totalPaginas}
            </span>
            <button id="btn-next-page" class="btn-paginacion" ${esUltima ? 'disabled' : ''} style="padding: 8px 16px; border: 1px solid #ddd; background: ${esUltima ? '#f3f4f6' : '#fff'}; color: ${esUltima ? '#9ca3af' : '#3e8cdd'}; border-radius: 6px; cursor: ${esUltima ? 'not-allowed' : 'pointer'}; font-weight: 600; transition: background 0.2s;">
                Siguiente
            </button>
        </div>
    `;
    
    contenedor.innerHTML = html;
    
    var btnPrev = contenedor.querySelector('#btn-prev-page');
    var btnNext = contenedor.querySelector('#btn-next-page');
    
    if (btnPrev && !esPrimera) {
        btnPrev.addEventListener('click', function() {
            onPageChange(paginaActual - 1);
        });
        btnPrev.addEventListener('mouseover', function() { this.style.background = '#f8faff'; });
        btnPrev.addEventListener('mouseout', function() { this.style.background = '#fff'; });
    }
    if (btnNext && !esUltima) {
        btnNext.addEventListener('click', function() {
            onPageChange(paginaActual + 1);
        });
        btnNext.addEventListener('mouseover', function() { this.style.background = '#f8faff'; });
        btnNext.addEventListener('mouseout', function() { this.style.background = '#fff'; });
    }
}
