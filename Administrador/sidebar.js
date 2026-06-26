/**
 * sidebar.js — Barra lateral compartida del Panel Administrador
 * Mismo sistema de diseño que ClienteSidebar.js del Portal Cliente.
 * Totalmente responsivo: hamburguesa en móvil, ancho adaptable en tablet.
 */

(function () {

  /* ── 1. Estilos ─────────────────────────────────────────────────────── */
  const style = document.createElement('style');
  style.textContent = `
    :root {
      --color-azul:        #3e8cdd;
      --color-verde:       #74cb3c;
      --color-texto:       #1a1a1a;
      --color-muted:       #717182;
      --color-muted-fondo: #ececf0;
      --color-borde:       rgba(0,0,0,0.1);
      --radio-borde:       0.625rem;
      --fuente-principal:  'Segoe UI', Arial, sans-serif;
      --adm-sidebar-w:     240px;
    }

    body {
      margin: 0;
      padding: 0 !important;
      font-family: var(--fuente-principal);
      background-color: #f3f4f6;
      color: var(--color-texto);
    }

    /* ── Layout ── */
    #adm-layout {
      display: flex;
      min-height: 100vh;
    }

    /* ── Sidebar ── */
    #adm-sidebar {
      width: var(--adm-sidebar-w);
      flex-shrink: 0;
      background: #f8f9fc;
      border-right: 1px solid var(--color-borde);
      display: flex;
      flex-direction: column;
      padding: 24px 0;
      position: fixed;
      top: 0;
      left: 0;
      height: 100vh;
      overflow-y: auto;
      z-index: 200;
      box-sizing: border-box;
      transition: transform 0.25s ease;
    }

    /* Logo */
    #adm-sidebar .adm-logo {
      padding: 0 20px 24px 20px;
      border-bottom: 1px solid var(--color-borde);
      margin-bottom: 16px;
      display: flex;
      align-items: center;
      gap: 10px;
      flex-shrink: 0;
    }
    #adm-sidebar .adm-logo img {
      height: 48px;
      width: auto;
      object-fit: contain;
    }
    #adm-sidebar .adm-logo-text {
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--color-azul);
      line-height: 1.2;
    }

   

    /* Separador */
    #adm-sidebar .adm-sep {
      margin: 8px 12px;
      border: none;
      border-top: 1px solid var(--color-borde);
      flex-shrink: 0;
    }

    /* Enlace */
    #adm-sidebar a {
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
      white-space: nowrap;
      overflow: hidden;
    }
    #adm-sidebar a:hover {
      background-color: var(--color-muted-fondo);
      color: var(--color-azul);
    }
    #adm-sidebar a.adm-active {
      background-color: #e8f1fb;
      color: var(--color-azul);
      font-weight: 600;
      border-left: 3px solid var(--color-azul);
    }

    /* SVG en enlaces */
    #adm-sidebar a svg {
      flex-shrink: 0;
      width: 18px;
      height: 18px;
      stroke: currentColor;
      stroke-width: 2;
      fill: none;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    /* Logout */
    #adm-sidebar a.adm-logout {
  color: #e53e3e !important;
  margin: 0;              /* ← sin margen extra */
  padding: 10px 20px;     /* ← igual que los demás */
  border-radius: 0;
  font-weight: 600;
  font-size: 0.9rem;
  flex-shrink: 0;
}
    #adm-sidebar a.adm-logout:hover {
      background-color: #fff5f5;
      color: #c53030 !important;
    }

    /* ── Contenido principal ── */
    #adm-content {
      flex: 1;
      margin-left: var(--adm-sidebar-w);
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      overflow-x: hidden;
      transition: margin-left 0.25s ease;
      min-width: 0;
      box-sizing: border-box;
    }

    /* Ocultar sidebars inline originales */
    #sidebar,
    #desktopSidebar,
    #mobileMenuOverlay,
    #mobileHeader {
      display: none !important;
    }

    /* Anular el margen izquierdo de las plantillas originales para evitar doble espaciado */
    .contenido-principal {
      margin-left: 0 !important;
      width: 100%;
    }

    /* ── Botón hamburguesa ── */
    #adm-hamburger {
      display: none;
      position: fixed;
      top: 12px;
      left: 12px;
      z-index: 300;
      background: #fff;
      border: 1px solid var(--color-borde);
      border-radius: 8px;
      padding: 8px 10px;
      cursor: pointer;
      box-shadow: 0 2px 6px rgba(0,0,0,0.12);
      line-height: 0;
      align-items: center;
      justify-content: center;
    }
    #adm-hamburger svg {
      width: 20px;
      height: 20px;
      stroke: var(--color-texto);
      stroke-width: 2;
      fill: none;
      stroke-linecap: round;
    }

    /* Overlay oscuro */
    #adm-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.35);
      z-index: 150;
      opacity: 0;
      transition: opacity 0.25s ease;
    }
    #adm-overlay.adm-visible {
      opacity: 1;
    }

    /* ── Tablet: sidebar más angosto ── */
    @media (max-width: 1024px) {
      :root { --adm-sidebar-w: 200px; }
      #adm-sidebar a { font-size: 0.85rem; padding: 10px 14px; }
    }

    /* ── Móvil: sidebar oculto, hamburguesa visible ── */
    @media (max-width: 768px) {
      :root { --adm-sidebar-w: 240px; }

      #adm-sidebar {
        transform: translateX(-100%);
        box-shadow: 4px 0 20px rgba(0,0,0,0.15);
      }
      #adm-sidebar.adm-open {
        transform: translateX(0);
      }

      #adm-content {
        margin-left: 0 !important;
      }

      #adm-hamburger {
        display: flex;
      }

      #adm-overlay {
        display: block;
      }
    }
  `;
  document.head.appendChild(style);


  /* ── 2. Navegación ───────────────────────────────────────────────────── */
  const NAV_ITEMS = [
    {
      href: 'Dashboard.php',
      label: 'Dashboard',
      icon: `<svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>`
    },
    {
      href: 'workers.php',
      label: 'Trabajadores',
      icon: `<svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`
    },
    {
      href: 'clientes.php',
      label: 'Clientes',
      icon: `<svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`
    },
    {
      href: 'proveedores.php',
      label: 'Proveedores',
      icon: `<svg viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 5v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>`
    },
    {
      href: 'supplies.php',
      label: 'Suministros',
      icon: `<svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>`
    },
    {
      href: 'admin_facturas.php',
      label: 'Facturas',
      icon: `<svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>`
    },
    {
      href: 'ventas.php',
      label: 'Ventas',
      icon: `<svg viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>`
    },
    {
      href: 'inventory.php',
      label: 'Materiales',
      icon: `<svg viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>`
    },
    {
      href: 'admin_home.php',
      label: 'Editar HOME',
      icon: `<svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>`
    },
    {
      href: '../Home/Home.html',
      label: 'Cerrar Sesión',
      className: 'adm-logout',
      onclick: 'if(window.cerrarSesion){ window.cerrarSesion(); return false; }',
      icon: `<svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>`
    }
  ];

  const currentFile = window.location.pathname.split('/').pop() || 'dashboard.php';


  /* ── 3. Construir sidebar ────────────────────────────────────────────── */
  const sidebarEl = document.createElement('nav');
  sidebarEl.id = 'adm-sidebar';

  const logoDiv = document.createElement('div');
  logoDiv.className = 'adm-logo';
  logoDiv.innerHTML = `
    <img src="Logo_GF_piedra .PNG"
         alt="Grupo Ferretero Piedras"
         onerror="this.style.display='none'" />
    <span class="adm-logo-text">Panel<br>Admin</span>
  `;
  sidebarEl.appendChild(logoDiv);

  NAV_ITEMS.forEach(item => {
    if (item.className === 'adm-logout') {

    }

    const a = document.createElement('a');
    a.href = item.href;
    a.innerHTML = item.icon + item.label;
    if (item.className) a.classList.add(item.className);
    if (item.onclick) a.setAttribute('onclick', item.onclick);
    if (currentFile.toLowerCase() === item.href.toLowerCase()) a.classList.add('adm-active');
    sidebarEl.appendChild(a);
  });

  if (!sidebarEl.querySelector('.adm-active')) {
    const dash = sidebarEl.querySelector('a[href="Dashboard.php"]');
    if (dash) dash.classList.add('adm-active');
  }


  /* ── 4. Hamburguesa y overlay ────────────────────────────────────────── */
  const hamburgerBtn = document.createElement('button');
  hamburgerBtn.id = 'adm-hamburger';
  hamburgerBtn.setAttribute('aria-label', 'Abrir menú');

  const overlayEl = document.createElement('div');
  overlayEl.id = 'adm-overlay';

  const iconMenu = `<svg viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>`;
  const iconClose = `<svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`;

  hamburgerBtn.innerHTML = iconMenu;

  function openSidebar() {
    sidebarEl.classList.add('adm-open');
    overlayEl.classList.add('adm-visible');
    hamburgerBtn.innerHTML = iconClose;
    hamburgerBtn.setAttribute('aria-label', 'Cerrar menú');
  }
  function closeSidebar() {
    sidebarEl.classList.remove('adm-open');
    overlayEl.classList.remove('adm-visible');
    hamburgerBtn.innerHTML = iconMenu;
    hamburgerBtn.setAttribute('aria-label', 'Abrir menú');
  }

  hamburgerBtn.addEventListener('click', () =>
    sidebarEl.classList.contains('adm-open') ? closeSidebar() : openSidebar()
  );
  overlayEl.addEventListener('click', closeSidebar);

  sidebarEl.querySelectorAll('a').forEach(a =>
    a.addEventListener('click', () => { if (window.innerWidth <= 768) closeSidebar(); })
  );


  /* ── 5. cerrarSesion global ──────────────────────────────────────────── */
  if (typeof window.cerrarSesion === 'undefined') {
    window.cerrarSesion = function () {
      window.location.href = '../Home/Home.html';
    };
  }


  /* ── 6. Inyección del layout ─────────────────────────────────────────── */
  function inject() {
    if (document.getElementById('adm-layout')) return;

    const bodyChildren = Array.from(document.body.childNodes);
    const contentEl = document.createElement('div');
    contentEl.id = 'adm-content';
    bodyChildren.forEach(node => contentEl.appendChild(node));

    const layoutEl = document.createElement('div');
    layoutEl.id = 'adm-layout';
    layoutEl.appendChild(sidebarEl);
    layoutEl.appendChild(contentEl);

    document.body.appendChild(layoutEl);
    document.body.appendChild(hamburgerBtn);
    document.body.appendChild(overlayEl);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', inject);
  } else {
    inject();
  }

})();