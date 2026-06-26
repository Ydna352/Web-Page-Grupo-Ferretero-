    // ── Estado global ──────────────────────────────────────────────────
    let currentPage = 1;
    let totalItems = 0;
    const itemsPerPage = 10;

    // ── Ruta de imágenes (relativa al archivo HTML en /Cliente/ y /Home/)
    const IMG_BASE = '../imagenes_materiales/';

    // ── Colores de área  ───────────────────────────────────────────────
    function getAreaBadgeClass(area) {
      const map = {
        'Carpinteria': 'badge-carpinteria',
        'Electricidad': 'badge-electricidad',
        'Plomeria': 'badge-plomeria',
        'Pintura': 'badge-pintura',
      };
      return map[area] || 'badge-default';
    }

    function getAreaLabel(area) {
      const map = {
        'Carpinteria': 'Carpintería',
        'Electricidad': 'Electricidad',
        'Plomeria': 'Plomería',
        'Pintura': 'Pintura',
      };
      return map[area] || area;
    }

    // ── Utilidades de stock ────────────────────────────────────────────
    function stockColor(n) {
      if (n >= 50) return 'stock-high';
      if (n >= 20) return 'stock-medium';
      return 'stock-low';
    }

    function stockBadge(n) {
      if (n >= 50) return '<span class="badge-stock stock-badge-avail">Disponible</span>';
      if (n >= 20) return '<span class="badge-stock stock-badge-medium">Stock Medio</span>';
      if (n >= 10) return '<span class="badge-stock stock-badge-low">Stock Bajo</span>';
      return '<span class="badge-stock stock-badge-critical">Pocas unidades</span>';
    }

    // ── Formato moneda MX ──────────────────────────────────────────────
    function formatMXN(value) {
      return Number(value).toLocaleString('es-MX', {
        style: 'currency',
        currency: 'MXN',
        minimumFractionDigits: 2
      });
    }

    // ── Construir HTML de una tarjeta ──────────────────────────────────
    function buildCard(product) {
      const imageName = product.imagen || (product.id + '.png');
      const ts     = product.imagen_ts || Date.now();
      const imgSrc = IMG_BASE + imageName + '?t=' + ts;

      return `
        <article class="product-card" aria-label="${escapeHtml(product.nombre)}">
          <div class="card-name">${escapeHtml(product.nombre)}</div>

          <div class="card-image-wrap">
            <img
              src="${imgSrc}"
              alt="${escapeHtml(product.nombre)}"
              loading="lazy"
              onerror="this.style.display='none';this.nextElementSibling.style.display='block';"
            >
            <span class="img-fallback" style="display:none;">📦</span>
          </div>

          <div class="card-body">
            <div class="card-meta">
              <span class="card-id">ID: ${escapeHtml(String(product.id))}</span>
              <span class="badge ${getAreaBadgeClass(product.area)}">${getAreaLabel(product.area)}</span>
            </div>

            <p class="card-desc" id="desc-${product.id}" onclick="toggleDesc(${product.id})">${escapeHtml(product.descripcion)}</p>
            <button class="desc-toggle" onclick="toggleDesc(${product.id})" id="toggle-${product.id}">▼ Ver más</button>

            <hr class="card-divider">

            <div class="card-price-row">
              <span class="card-price-label">Precio unitario:</span>
              <div class="card-price">
                <svg viewBox="0 0 24 24">
                  <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/>
                  <polyline points="16 7 22 7 22 13"/>
                </svg>
                ${formatMXN(product.precio_unitario)}
              </div>
            </div>

            <div class="card-stock-row">
              <div style="display:flex;align-items:center;gap:0.4rem;">
                <span class="card-stock-label">Existencias:</span>
                <span class="card-stock-val ${stockColor(product.existencias)}">${product.existencias}</span>
              </div>
              ${stockBadge(product.existencias)}
            </div>
          </div>
        </article>
      `;
    }

    // ── Expandir/colapsar descripción ──────────────────────────────────
    function toggleDesc(id) {
      const desc = document.getElementById('desc-' + id);
      const toggle = document.getElementById('toggle-' + id);
      if (!desc) return;

      const expanded = desc.classList.toggle('expanded');
      toggle.textContent = expanded ? '▲ Ver menos' : '▼ Ver más';
    }

    // ── Escape XSS ─────────────────────────────────────────────────────
    function escapeHtml(str) {
      if (str == null) return '';
      return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
    }

    // ── Renderizar grid ────────────────────────────────────────────────
    function renderProducts(products) {
      const container = document.getElementById('productsContainer');
      const counter = document.getElementById('resultsCount');

      if (!container || !counter) return;

      // Actualizar contador
      const n = products.length;
      counter.textContent = totalItems === 1
        ? '1 producto encontrado'
        : `${totalItems} productos encontrados`;

      if (n === 0) {
        container.innerHTML = `
          <div class="state-box">
            <span class="state-icon">🔍</span>
            <h3>No se encontraron productos</h3>
            <p>Intenta con otros términos de búsqueda o cambia el filtro de área</p>
          </div>`;
        return;
      }

      container.innerHTML = `<div class="products-grid">${products.map(buildCard).join('')}</div>`;
    }

    // ── Paginación y Filtros ───────────────────────────────────────────
    function applyFilters() {
      currentPage = 1;
      loadProducts();
    }

    function changePage(delta) {
      const totalPages = Math.ceil(totalItems / itemsPerPage);
      const newPage = currentPage + delta;
      
      if (newPage >= 1 && newPage <= totalPages) {
        currentPage = newPage;
        loadProducts();
      }
    }

    function renderPagination() {
      const container = document.getElementById('paginationContainer');
      const totalPages = Math.ceil(totalItems / itemsPerPage);
      
      if (!container) return;

      if (totalPages <= 1) {
        container.style.display = 'none';
        return;
      }
      
      container.style.display = 'flex';
      container.innerHTML = `
        <button class="pagination-btn" onclick="changePage(-1)" ${currentPage === 1 ? 'disabled' : ''}>
          &laquo; Anterior
        </button>
        <span class="pagination-info">Página ${currentPage} de ${totalPages}</span>
        <button class="pagination-btn" onclick="changePage(1)" ${currentPage === totalPages ? 'disabled' : ''}>
          Siguiente &raquo;
        </button>
      `;
    }

    // ── Cargar productos desde la API PHP ──────────────────────────────
    async function loadProducts() {
      const container = document.getElementById('productsContainer');
      const counter = document.getElementById('resultsCount');
      
      if (!container || !counter) return;

      const searchInput = document.getElementById('searchInput');
      const filterArea = document.getElementById('filterArea');
      const sortBy = document.getElementById('sortBy');

      const term = searchInput ? encodeURIComponent(searchInput.value.trim()) : '';
      const area = filterArea ? encodeURIComponent(filterArea.value) : '';
      const sort = sortBy ? encodeURIComponent(sortBy.value) : 'nombre';

      // Mostrar spinner
      container.innerHTML = `
        <div class="state-box" id="loadingBox">
          <div class="loading-spinner"></div>
          <h3>Cargando catálogo…</h3>
          <p>Obteniendo productos de la base de datos</p>
        </div>`;
      counter.textContent = 'Cargando productos…';
      
      const paginationContainer = document.getElementById('paginationContainer');
      if (paginationContainer) {
          paginationContainer.style.display = 'none';
      }

      try {
        const url = `../PHP/api_catalogo.php?page=${currentPage}&search=${term}&area=${area}&sortBy=${sort}`;
        const response = await fetch(url);

        if (!response.ok) {
          throw new Error(`Error HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();

        if (data.error) {
          throw new Error(data.error);
        }

        if (!data.productos || !Array.isArray(data.productos)) {
          throw new Error('Formato de respuesta incorrecto de la base de datos');
        }

        totalItems = data.total || 0;
        currentPage = data.pagina_actual || 1;

        renderProducts(data.productos);
        renderPagination();

      } catch (err) {
        container.innerHTML = `
          <div class="state-box">
            <span class="state-icon">⚠️</span>
            <h3>No se pudo cargar el catálogo</h3>
            <p>${escapeHtml(err.message)}</p>
            <br>
            <button onclick="loadProducts()" style="
              margin-top:0.5rem;
              padding:0.6rem 1.4rem;
              background:var(--azul);
              color:#fff;
              border:none;
              border-radius:0.5rem;
              cursor:pointer;
              font-size:0.9rem;
              font-family:inherit;
            ">🔄 Reintentar</button>
          </div>`;
        counter.textContent = 'Error al cargar';
        console.error('[Catálogo] Error:', err);
      }
    }
