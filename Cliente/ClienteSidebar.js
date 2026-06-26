/**
 * ClienteSidebar.js — Barra lateral compartida del Portal Cliente
 * Basada en el mismo sistema de diseño de Administrador/styles.css
 *
 * Uso: <script src="ClienteSidebar.js"></script>  (antes del cierre de </body>)
 * El script inyecta el sidebar y ajusta el layout automáticamente.
 *
 * FIX: logout creado fuera de NAV_ITEMS para garantizar clase cl-logout (color rojo).
 */

(function () {

  /* ── 1. Estilos (sistema de diseño idéntico al panel admin) ─────────── */
  const style = document.createElement('style');
  style.textContent = `
    /* ── Variables (mismas que styles.css del admin) ── */
    :root {
      --color-azul:  #3e8cdd;
      --color-verde: #74cb3c;
      --color-texto: #1a1a1a;
      --color-muted: #717182;
      --color-muted-fondo: #ececf0;
      --color-borde: rgba(0,0,0,0.1);
      --radio-borde: 0.625rem;
      --fuente-principal: 'Segoe UI', Arial, sans-serif;
    }

    /* ── Reset ── */
    body {
      margin: 0;
      padding: 0 !important;
      font-family: var(--fuente-principal);
      background-color: #f3f4f6;
      color: var(--color-texto);
    }

    /* ── Layout principal ── */
    #cl-layout {
      display: flex;
      min-height: 100vh;
    }

    /* ── Sidebar ── */
    #cl-sidebar {
      width: 240px;
      flex-shrink: 0;
      background: #f8f9fc;
      border-right: 1px solid var(--color-borde);
      display: flex;
      flex-direction: column;
      padding: 24px 0;
      position: fixed;
      top: 0;
      left: 0;
      height: 100vh;         /* altura completa para que el spacer funcione */
      overflow-y: auto;
      z-index: 100;
      box-sizing: border-box; /* padding incluido en el 100vh */
    }

    /* Logo / título */
    #cl-sidebar .cl-logo {
      padding: 0 20px 24px 20px;
      border-bottom: 1px solid var(--color-borde);
      margin-bottom: 16px;
      display: flex;
      align-items: center;
      gap: 10px;
      flex-shrink: 0; /* que el logo no se comprima */
    }

    #cl-sidebar .cl-logo img {
      height: 48px;
      width: auto;
      object-fit: contain;
    }

    #cl-sidebar .cl-logo-text {
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--color-azul);
      line-height: 1.2;
    }

    /* Separador entre nav items y logout */
    #cl-sidebar .cl-sep {
      margin: 8px 12px;
      border: none;
      border-top: 1px solid var(--color-borde);
      flex-shrink: 0;
    }

    /* Empuja el botón de logout al final */
    #cl-sidebar .cl-spacer {
      flex: 1;
    }

    /* Cada enlace del nav */
    #cl-sidebar a {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 20px;
      color: var(--color-texto);
      text-decoration: none;
      font-size: 0.9rem;
      border-radius: 0 var(--radio-borde) var(--radio-borde) 0;
      transition: background-color 0.2s, color 0.2s;
      margin-bottom: 2px;
      flex-shrink: 0;
    }

    #cl-sidebar a:hover {
      background-color: var(--color-muted-fondo);
      color: var(--color-azul);
    }

    /* Enlace activo (página actual) */
    #cl-sidebar a.cl-active {
      background-color: #e8f1fb;
      color: var(--color-azul);
      font-weight: 600;
      border-left: 3px solid var(--color-azul);
    }

    /* SVG dentro de los enlaces */
    #cl-sidebar a svg {
      flex-shrink: 0;
      width: 18px;
      height: 18px;
      stroke: currentColor;
      stroke-width: 2;
      fill: none;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    /* Botón cerrar sesión — rojo, al fondo */
    #cl-sidebar a.cl-logout {
      color: #e53e3e !important;  /* fuerza el rojo sobre la regla genérica de 'a' */
      margin: 0 12px 16px;
      padding: 10px 12px;
      border-radius: var(--radio-borde);
      font-weight: 600;
      font-size: 0.85rem;
      flex-shrink: 0;
    }

    #cl-sidebar a.cl-logout:hover {
      background-color: #fff5f5;
      color: #c53030 !important;
      border-color: #c53030;
    }

    /* ── Contenido principal ── */
    #cl-content {
      flex: 1;
      margin-left: 240px;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      overflow-x: hidden;
    }

    /* Ocultar el sidebar inline que ya traen las páginas */
    #desktopSidebar,
    #mobileMenuOverlay,
    #mobileHeader {
      display: none !important;
    }

    /* ── Responsive ── */
    @media (max-width: 900px) {
      #cl-sidebar { width: 180px; }
      #cl-content  { margin-left: 180px; }
    }

    @media (max-width: 600px) {
      #cl-sidebar  { display: none; }
      #cl-content  { margin-left: 0; }
    }
  `;
  document.head.appendChild(style);


  /* ── 2. Definición de la navegación ─────────────────────────────────── */
  const NAV_ITEMS = [
    {
      href: 'Dashboard.php',
      label: 'Panel Principal',
      icon: `<svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>`
    },
    {
      href: 'company_info.php',
      label: 'Información Empresa',
      icon: `<svg viewBox="0 0 24 24"><path d="M3.75 21h16.5M4.5 3h15l.75 18H3.75zM9 21V9m6 12V9M9 9h6M9 6h6"/></svg>`
    },
    {
      href: 'MyPurchases.php',
      label: 'Mis Compras',
      icon: `<svg viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>`
    },
    {
      href: 'InvoiceGeneration.php',
      label: 'Generar Factura',
      icon: `<svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>`
    },
    {
      href: 'Profile.php',
      label: 'Mi Perfil',
      icon: `<svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>`
    },
    {
      href: 'catalogo.php',
      label: 'Catálogo',
      icon: `<svg viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><circle cx="12" cy="13" r="3"/></svg>`
    },
    {
      href: '../Home/Home.html',
      label: 'Cerrar Sesión',
      className: 'cl-logout',
      icon: `<svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>`
    }
  ];

  // Detectar la página actual (case-insensitive)
  const currentFile = (window.location.pathname.split('/').pop() || 'Dashboard.php');

  /* ── 3. Construir el sidebar ─────────────────────────────────────────── */
  const sidebarEl = document.createElement('nav');
  sidebarEl.id = 'cl-sidebar';

  // Logo / cabecera
  const logoDiv = document.createElement('div');
  logoDiv.className = 'cl-logo';
  logoDiv.innerHTML = `
    <img src="../Administrador/Logo_GF_piedra .PNG"
         alt="Grupo Ferretero Piedras"
         onerror="this.style.display='none'" />
    <span class="cl-logo-text">Portal<br>Cliente</span>
  `;
  sidebarEl.appendChild(logoDiv);

  // Nav items — si el item tiene className, se aplica (ej. cl-logout para rojo)
  NAV_ITEMS.forEach(item => {
    const a = document.createElement('a');
    a.href = item.href;
    a.innerHTML = item.icon + item.label;
    if (item.className) a.classList.add(item.className);
    if (currentFile.toLowerCase() === item.href.toLowerCase()) {
      a.classList.add('cl-active');
    }
    sidebarEl.appendChild(a);
  });

  // Si ninguno quedó activo, activar Dashboard
  if (!sidebarEl.querySelector('.cl-active')) {
    const dash = sidebarEl.querySelector('a[href="Dashboard.php"]');
    if (dash) dash.classList.add('cl-active');
  }




  /* ── 4. Inyección del layout ─────────────────────────────────────────── */
  function inject() {
    if (document.getElementById('cl-layout')) return;

    const bodyChildren = Array.from(document.body.childNodes);

    const contentEl = document.createElement('div');
    contentEl.id = 'cl-content';
    bodyChildren.forEach(node => contentEl.appendChild(node));

    const layoutEl = document.createElement('div');
    layoutEl.id = 'cl-layout';
    layoutEl.appendChild(sidebarEl);
    layoutEl.appendChild(contentEl);

    document.body.appendChild(layoutEl);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', inject);
  } else {
    inject();
  }

})();