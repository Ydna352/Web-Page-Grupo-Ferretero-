/**
 * sidebar.js — Componente de barra lateral compartida
 * Uso: <script src="sidebar.js"></script>  (antes del cierre de </body>)
 * El script inyecta el sidebar y adapta el layout de la página automáticamente.
 */

(function () {

  /* ── 1. Estilos del sidebar ─────────────────────────────────────────── */
  const style = document.createElement('style');
  style.textContent = `
    /* ── Variables (sistema de diseño del admin) ── */
    :root {
      --color-azul:  #3e8cdd;
      --color-verde: #74cb3c;
      --color-texto: #1a1a1a;
      --color-muted: #717182;
      --color-muted-fondo: #ececf0;
      --color-borde: rgba(0,0,0,0.1);
      --radio-borde: 0.625rem;
    }

    /* Reset básico para el layout */
    body {
      margin: 0;
      padding: 0 !important;
      font-family: 'Segoe UI', Arial, sans-serif;
      background-color: #f3f4f6;
      color: var(--color-texto);
    }

    /* Wrapper que envuelve sidebar + contenido */
    #app-layout {
      display: flex;
      min-height: 100vh;
    }

    /* ── Sidebar ── */
    #app-sidebar {
      width: 240px;
      flex-shrink: 0;
      background: #f8f9fc;
      border-right: 1px solid var(--color-borde);
      padding: 24px 0;
      display: flex;
      flex-direction: column;
      gap: 2px;
      position: sticky;
      top: 0;
      height: 100vh;
      overflow-y: auto;
    }

    #app-sidebar .sb-logo {
      padding: 0 20px 24px 20px;
      border-bottom: 1px solid var(--color-borde);
      margin-bottom: 16px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    #app-sidebar .sb-logo img {
      height: 48px;
      width: auto;
      object-fit: contain;
    }

    #app-sidebar .sb-logo-text {
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--color-azul);
      line-height: 1.2;
      font-family: 'Segoe UI', Arial, sans-serif;
    }

    #app-sidebar a {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 20px;
      border-radius: 0 var(--radio-borde) var(--radio-borde) 0;
      text-decoration: none;
      color: var(--color-texto);
      font-family: 'Segoe UI', Arial, sans-serif;
      font-size: 0.9rem;
      font-weight: 500;
      transition: background 0.2s, color 0.2s;
      margin-bottom: 2px;
    }

    #app-sidebar a:hover {
      background-color: var(--color-muted-fondo);
      color: var(--color-azul);
    }

    /* Enlace activo: la página actual se resalta */
    #app-sidebar a.sb-active {
      background-color: #e8f1fb;
      color: var(--color-azul);
      font-weight: 600;
      border-left: 3px solid var(--color-azul);
    }

    #app-sidebar a svg {
      flex-shrink: 0;
      stroke: currentColor;
      fill: none;
      stroke-width: 2;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    /* Cerrar Sesión al final: estilo de botón rojo como en styles.css */
    #app-sidebar a.sb-logout {
      color: #e53e3e;
      margin: 8px 12px 16px;
      padding: 10px 12px;
      border-radius: var(--radio-borde);
      font-weight: 600;
      font-size: 0.85rem;
    }

    #app-sidebar a.sb-logout:hover {
      background-color: #fff5f5;
      color: #e53e3e;
      border-color: #e53e3e;
    }

    /* ── Contenido principal ── */
    #app-content {
      flex: 1;
      overflow-y: auto;
      padding: 40px 36px;
      background: #f3f4f6;
    }

    /* En páginas de formulario el container ya tiene max-width */
    #app-content > .container,
    #app-content > div > .container {
      padding: 0;
    }

    /* ── Responsive ── */
    @media (max-width: 900px) {
      #app-sidebar { width: 180px; }
      #app-content { padding: 32px 20px; }
    }
    @media (max-width: 600px) {
      #app-sidebar { display: none; }
      #app-content { padding: 24px 16px; }
    }
  `;
  document.head.appendChild(style);


  /* ── 2. Markup del sidebar ──────────────────────────────────────────── */
  const NAV_ITEMS = [
    {
      href: 'Dashboard.php',
      label: 'Panel Principal',
      icon: `<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>`
    },
    {
      href: 'RegisterSale.php',
      label: 'Registrar Venta',
      icon: `<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>`
    },
    {
      href: 'RegisterClient.php',
      label: 'Registrar Cliente',
      icon: `<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>`
    },
    {
      href: '../Home/Home.html',
      label: 'Cerrar Sesión',
      className: 'sb-logout',
      icon: `<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>`
    }
  ];

  // Detectar la página actual por nombre de archivo
  const currentFile = window.location.pathname.split('/').pop() || 'dashboard.php';

  const sidebarEl = document.createElement('nav');
  sidebarEl.id = 'app-sidebar';

  // ── Logo / cabecera ──
  const logoDiv = document.createElement('div');
  logoDiv.className = 'sb-logo';
  logoDiv.innerHTML = `
    <img src="../Administrador/Logo_GF_piedra .PNG"
         alt="Grupo Ferretero Piedras"
         onerror="this.style.display='none'" />
    <span class="sb-logo-text">Portal<br>Empleado</span>
  `;
  sidebarEl.appendChild(logoDiv);

  // ── Nav items ──
  NAV_ITEMS.forEach(item => {
    const a = document.createElement('a');
    a.href = item.href;
    a.innerHTML = item.icon + item.label;
    if (item.className) a.classList.add(item.className);
    if (currentFile.toLowerCase() === item.href.toLowerCase()) {
      a.classList.add('sb-active');
    }
    sidebarEl.appendChild(a);
  });

  // Si ningún enlace quedó activo, marcar Dashboard por defecto
  if (!sidebarEl.querySelector('.sb-active:not(.sb-logout)')) {
    const dashLink = sidebarEl.querySelector('a[href="Dashboard.php"]');
    if (dashLink) dashLink.classList.add('sb-active');
  }


  /* ── 3. Inyección del layout ────────────────────────────────────────── */
  // Esperar a que el DOM esté listo
  function inject() {
    // Evitar doble inyección
    if (document.getElementById('app-layout')) return;

    // Tomar todos los hijos actuales del body
    const bodyChildren = Array.from(document.body.childNodes);

    // Crear el wrapper de contenido
    const contentEl = document.createElement('div');
    contentEl.id = 'app-content';

    // Mover los hijos del body al contenedor de contenido
    bodyChildren.forEach(node => contentEl.appendChild(node));

    // Crear el layout principal
    const layoutEl = document.createElement('div');
    layoutEl.id = 'app-layout';
    layoutEl.appendChild(sidebarEl);
    layoutEl.appendChild(contentEl);

    // Insertar el layout en el body
    document.body.appendChild(layoutEl);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', inject);
  } else {
    inject();
  }

})();