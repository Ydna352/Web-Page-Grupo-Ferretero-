<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Catálogo de Productos | Portal Cliente</title>
  <meta name="description"
    content="Explora nuestra amplia selección de herramientas y gf_materiales de ferretería. Busca por área, precio o existencias.">

  <!-- Favicon -->
  <link rel="apple-touch-icon" sizes="180x180" href="../Administrador/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="../Administrador/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="../Administrador/favicon-16x16.png">
  <link rel="manifest" href="../Administrador/site.webmanifest">

  <link rel="stylesheet" href="catalogo_shared.css">
</head>

<body>

  <!-- ══ Contenido principal (ClienteSidebar.js lo envuelve automáticamente) ══ -->
  <div class="catalogo-wrapper">

    <!-- Header -->
    <div class="page-header">
      <h1>Catálogo de Productos</h1>
      <p>Explora nuestra amplia selección de herramientas</p>
    </div>

    <!-- Toolbar: búsqueda + filtros -->
    <div class="toolbar">
      <!-- Búsqueda -->
      <div class="search-box">
        <svg viewBox="0 0 24 24">
          <circle cx="11" cy="11" r="8" />
          <line x1="21" y1="21" x2="16.65" y2="16.65" />
        </svg>
        <input type="text" id="searchInput" placeholder="Buscar producto…" autocomplete="off" oninput="applyFilters()"
          aria-label="Buscar producto">
      </div>

      <!-- Filtro por área -->
      <select class="filter-select" id="filterArea" onchange="applyFilters()" aria-label="Filtrar por área">
        <option value="">Todas las áreas</option>
        <option value="Carpinteria">Carpintería</option>
        <option value="Electricidad">Electricidad</option>
        <option value="Plomeria">Plomería</option>
        <option value="Pintura">Pintura</option>
      </select>

      <!-- Ordenar por -->
      <select class="filter-select" id="sortBy" onchange="applyFilters()" aria-label="Ordenar por">
        <option value="nombre">Ordenar: Nombre</option>
        <option value="precio">Ordenar: Precio ↑</option>
        <option value="precio_desc">Ordenar: Precio ↓</option>
        <option value="stock">Ordenar: Existencias ↓</option>
      </select>
    </div>

    <!-- Contador de resultados -->
    <div class="results-bar" id="resultsBar">
      <svg viewBox="0 0 24 24">
        <path
          d="M16.5 9.4l-9-5.19M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
      </svg>
      <span id="resultsCount">Cargando productos…</span>
    </div>

    <!-- Contenedor de productos -->
    <div id="productsContainer">
      <!-- Estado de carga inicial -->
      <div class="state-box" id="loadingBox">
        <div class="loading-spinner"></div>
        <h3>Cargando catálogo…</h3>
        <p>Obteniendo productos de la base de datos</p>
      </div>
    </div>

    <!-- Controles de Paginación -->
    <div id="paginationContainer" class="pagination-container" style="display: none;"></div>

  </div><!-- /.catalogo-wrapper -->

  <!-- ══ Sidebar del portal cliente ══ -->
  <script src="ClienteSidebar.js"></script>

  <script src="catalogo_shared.js"></script>
  <script>
    // Iniciar carga al cargar el DOM en el portal de cliente
    document.addEventListener('DOMContentLoaded', loadProducts);
  </script>

</body>

</html>