<?php require_once '../PHP/auth.php'; auth_require_rol('cliente'); ?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Información corporativa de Grupo Ferretero Piedras: misión, visión, contacto y ubicación.">
  <title>Información de la Empresa – Portal Cliente</title>
  <link rel="apple-touch-icon" sizes="180x180" href="../Administrador/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="../Administrador/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="../Administrador/favicon-16x16.png">
  <link rel="manifest" href="../Administrador/site.webmanifest">
  <style>
    /* ── Reset ─────────────────────────────────────────────────────────── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { width: 100%; height: 100%; }

    body {
      font-family: sans-serif;
      background-color: #f3f4f6;
    }

    /* ── Layout ─────────────────────────────────────────────────────────── */
    .root-layout { display: flex; min-height: 100vh; width: 100%; }

    #desktopSidebar {
      display: none;
      width: 16rem;
      min-width: 16rem;
      height: 100vh;
      position: sticky;
      top: 0;
      border-right: 1px solid #e5e7eb;
      background-color: white;
      flex-direction: column;
      flex-shrink: 0;
    }
    @media (min-width: 1024px) {
      #desktopSidebar { display: flex; }
      #mobileHeader   { display: none !important; }
    }

    .nav-btn {
      width: 100%; display: flex; align-items: center;
      padding: .5rem 1rem; border-radius: .375rem; border: none;
      background: transparent; cursor: pointer; text-decoration: none;
      color: #374151; font-family: inherit; font-size: .875rem; margin-bottom: .25rem;
    }
    .nav-btn:hover { background-color: #f3f4f6; }
    .nav-btn.active { background-color: #74cb3c !important; color: white !important; }

    .main-area { flex: 1; display: flex; flex-direction: column; min-width: 0; width: 100%; }

    #mobileHeader {
      display: flex; align-items: center; justify-content: space-between;
      padding: 1rem; background-color: white; border-bottom: 1px solid #e5e7eb;
      position: sticky; top: 0; z-index: 40;
    }

    main { flex: 1; padding: 1.5rem; overflow-x: hidden; width: 100%; }

    .page-inner {
      background-color: #fff;
      border-radius: .5rem;
      padding: 2rem;
      width: 100%;
    }

    /* ── Título de página ────────────────────────────────────────────────── */
    .page-title {
      font-size: 1.875rem;
      font-weight: bold;
      color: #3e8cdd;
    }
    .page-subtitle {
      color: #4b5563;
      margin-top: .375rem;
      font-size: .95rem;
    }

    /* ── Sección con logo + nombre ──────────────────────────────────────── */
    .empresa-header {
      display: flex;
      align-items: center;
      gap: 1.5rem;
      padding: 1.25rem 1.5rem;
      background: linear-gradient(135deg, #3e8cdd 0%, #2e6cbd 100%);
      border-radius: .75rem;
      margin: 1.25rem 0;
      box-shadow: 0 4px 12px rgba(62,140,221,.2);
      color: white;
    }
    .empresa-header img {
      width: 80px;
      height: 80px;
      object-fit: contain;
      border-radius: .5rem;
      background: white;
      padding: 6px;
    }
    .empresa-header h2 {
      font-size: 1.5rem;
      font-weight: 700;
    }
    .empresa-header p {
      font-size: .9rem;
      opacity: .85;
      margin-top: .25rem;
    }

    /* ── Columna de cards ────────────────────────────────────────────────── */
    .cards-col { display: flex; flex-direction: column; gap: 1.5rem; margin-top: 1.5rem; }
    .two-col   { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.5rem; }

    /* ── Card genérica ───────────────────────────────────────────────────── */
    .card {
      border: 2px solid;
      border-radius: .75rem;
      background: white;
      box-shadow: 0 1px 4px rgba(0,0,0,.07);
    }
    .card--blue  { border-color: #3e8cdd; }
    .card--green { border-color: #74cb3c; }

    .card-hdr {
      display: flex; align-items: center; gap: .5rem;
      font-weight: 700; font-size: 1.05rem;
      padding: 1.25rem 1.5rem;
      border-bottom: 1px solid #e5e7eb;
    }
    .card-hdr svg    { width: 20px; height: 20px; flex-shrink: 0; }
    .card-hdr.blue   { color: #3e8cdd; }
    .card-hdr.green  { color: #74cb3c; }

    .card-body { padding: 1.25rem 1.5rem; }

    /* ── Campo dato ──────────────────────────────────────────────────────── */
    .fields2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
    .fl      { margin-bottom: 0; }
    .fl-lbl  { font-size: .78rem; color: #6b7280; margin-bottom: .15rem; text-transform: uppercase; letter-spacing: .04em; }
    .fl-val  { font-weight: 600; color: #111827; }

    /* ── Texto largo (misión/visión) ──────────────────────────────────── */
    .texto-largo { line-height: 1.75; color: #374151; font-size: .97rem; }

    /* ── Contacto ─────────────────────────────────────────────────────── */
    .contact-row { display: flex; align-items: flex-start; gap: .75rem; }
    .contact-row + .contact-row { margin-top: 1rem; }
    .contact-row svg { width: 18px; height: 18px; color: #3e8cdd; flex-shrink: 0; margin-top: 2px; }

    /* ── Horarios (estáticos) ─────────────────────────────────────────── */
    .sched + .sched { margin-top: .75rem; }

    /* ── Loading placeholder ──────────────────────────────────────────── */
    .skeleton {
      background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
      background-size: 200% 100%;
      animation: shimmer 1.4s infinite;
      border-radius: 4px;
      height: 1em;
      display: inline-block;
      width: 100%;
    }
    @keyframes shimmer {
      from { background-position: 200% 0; }
      to   { background-position: -200% 0; }
    }
  </style>
</head>

<body>
  <div class="root-layout">

    <!-- ── Sidebar escritorio ──────────────────────────────────────────── -->
    <aside id="desktopSidebar">
      <div style="padding:1.5rem;border-bottom:1px solid #e5e7eb;background-color:#74cb3c;">
        <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem;">
          <span style="font-size:1.5rem;background:white;border-radius:.5rem;padding:.25rem;">🏢</span>
          <h1 style="font-size:1.25rem;font-weight:bold;color:white;margin:0;">Portal Cliente</h1>
        </div>
        <p id="sb-nombre-cliente" style="font-size:.875rem;color:#dcfce7;margin:0;">Cargando…</p>
      </div>
      <nav style="flex:1;padding:1rem;overflow-y:auto;" id="desktopNav"></nav>
      <div style="padding:1rem;border-top:1px solid #e5e7eb;margin-top:auto;">
        <button onclick="handleLogout()"
          style="width:100%;display:flex;align-items:center;padding:.5rem 1rem;color:#dc2626;border:1px solid #fca5a5;background:transparent;border-radius:.375rem;cursor:pointer;">
          <span style="margin-right:.75rem;">🚪</span> Cerrar Sesión
        </button>
      </div>
    </aside>

    <!-- ── Overlay menú móvil ──────────────────────────────────────────── -->
    <div id="mobileMenuOverlay"
      style="display:none;position:fixed;inset:0;z-index:50;background-color:rgba(0,0,0,.5);">
      <aside style="width:16rem;height:100%;background-color:white;display:flex;flex-direction:column;">
        <div style="padding:1.5rem;border-bottom:1px solid #e5e7eb;background-color:#74cb3c;display:flex;justify-content:space-between;align-items:center;">
          <div style="display:flex;align-items:center;gap:.5rem;">
            <span style="font-size:1.25rem;background:white;border-radius:.5rem;padding:.25rem;">🏢</span>
            <div>
              <h1 style="font-size:1.125rem;font-weight:bold;color:white;margin:0;">Cliente</h1>
              <p id="sb-nombre-cliente-mob" style="font-size:.75rem;color:#dcfce7;margin:0;">Cargando…</p>
            </div>
          </div>
          <button onclick="toggleMobileMenu(false)"
            style="color:white;background:none;border:none;font-size:1.5rem;cursor:pointer;">&times;</button>
        </div>
        <nav style="flex:1;padding:1rem;overflow-y:auto;" id="mobileNav"></nav>
        <div style="padding:1rem;border-top:1px solid #e5e7eb;margin-top:auto;">
          <button onclick="handleLogout()"
            style="width:100%;display:flex;align-items:center;padding:.5rem 1rem;color:#dc2626;border:1px solid #fca5a5;background:transparent;border-radius:.375rem;cursor:pointer;">
            <span style="margin-right:.75rem;">🚪</span> Cerrar Sesión
          </button>
        </div>
      </aside>
    </div>

    <!-- ── Contenido principal ────────────────────────────────────────── -->
    <div class="main-area">
      <header id="mobileHeader">
        <button onclick="toggleMobileMenu(true)"
          style="color:#74cb3c;background:none;border:none;font-size:1.5rem;cursor:pointer;">☰</button>
        <div style="display:flex;align-items:center;gap:.5rem;">
          <span style="font-size:1.25rem;">🏢</span>
          <h1 style="font-weight:bold;color:#74cb3c;margin:0;font-size:1.125rem;">Portal Cliente</h1>
        </div>
        <div style="width:1.5rem;"></div>
      </header>

      <main>
        <div class="page-inner">
          <!-- Título -->
          <h1 class="page-title" id="ci-page-title">Información de la Empresa</h1>
          <p class="page-subtitle">Datos corporativos, misión, visión y contacto actualizados en tiempo real.</p>

          <!-- Cabecera empresa (logo + nombre) -->
          <div class="empresa-header" id="empresa-header-block">
            <img id="ci-logo"
                 src="../imagenes_empresa/logo.png"
                 alt="Logo de la empresa"
                 onerror="this.style.display='none'">
            <div>
              <h2 id="ci-nombre-empresa">
                <span class="skeleton" style="width:240px;">&nbsp;</span>
              </h2>
              <p id="ci-giro-tag">
                <span class="skeleton" style="width:180px;">&nbsp;</span>
              </p>
            </div>
          </div>

          <div class="cards-col">

            <!-- ── Datos Generales ─────────────────────────────────────── -->
            <div class="card card--blue">
              <div class="card-hdr blue">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3.75 21h16.5M4.5 3h15l.75 18H3.75L4.5 3zM9 21V9m6 12V9M9 9h6M9 6h6" />
                </svg>
                Datos Generales
              </div>
              <div class="card-body fields2">
                <div class="fl">
                  <p class="fl-lbl">Nombre Legal</p>
                  <p class="fl-val" id="ci_nombre_legal">—</p>
                </div>
                <div class="fl">
                  <p class="fl-lbl">Año de Fundación</p>
                  <p class="fl-val" id="ci_ano_fundacion">—</p>
                </div>
                <div class="fl" style="grid-column:1/-1;">
                  <p class="fl-lbl">Giro Comercial</p>
                  <p class="fl-val" id="ci_giro_comercial">—</p>
                </div>
              </div>
            </div>

            <!-- ── Misión y Visión (prioridad corporativa) ─────────────── -->
            <div class="two-col">
              <!-- Misión -->
              <div class="card card--blue">
                <div class="card-hdr blue">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <circle cx="12" cy="12" r="10"/>
                    <circle cx="12" cy="12" r="6"/>
                    <circle cx="12" cy="12" r="2"/>
                  </svg>
                  Misión
                </div>
                <div class="card-body">
                  <p class="texto-largo" id="ci_mision">
                    <span class="skeleton">&nbsp;</span>
                  </p>
                </div>
              </div>
              <!-- Visión -->
              <div class="card card--green">
                <div class="card-hdr green">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  </svg>
                  Visión
                </div>
                <div class="card-body">
                  <p class="texto-largo" id="ci_vision">
                    <span class="skeleton">&nbsp;</span>
                  </p>
                </div>
              </div>
            </div>

            <!-- ── Contacto ────────────────────────────────────────────── -->
            <div class="card card--green">
              <div class="card-hdr green">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
                </svg>
                Información de Contacto
              </div>
              <div class="card-body">
                <!-- Email -->
                <div class="contact-row">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25H4.5a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5H4.5a2.25 2.25 0 00-2.25 2.25m19.5 0l-9.75 6.75L2.25 6.75"/>
                  </svg>
                  <div>
                    <p class="fl-lbl">Email</p>
                    <p class="fl-val" id="ci_email">—</p>
                  </div>
                </div>
                <!-- Teléfonos -->
                <div class="contact-row">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
                  </svg>
                  <div>
                    <p class="fl-lbl">Teléfonos</p>
                    <p class="fl-val" id="ci_telefonos">—</p>
                  </div>
                </div>
                <!-- Dirección -->
                <div class="contact-row">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                  </svg>
                  <div>
                    <p class="fl-lbl">Dirección</p>
                    <p class="fl-val" id="ci_direccion">—</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- ── Horarios (estáticos, sin campo en BD) ───────────────── -->
            <div class="two-col">
              <div class="card card--green">
                <div class="card-hdr green">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                  </svg>
                  Horarios de Atención
                </div>
                <div class="card-body">
                  <div class="sched">
                    <p class="fl-lbl">Lunes a Viernes</p>
                    <p class="fl-val">9:00 AM – 6:00 PM</p>
                  </div>
                  <div class="sched">
                    <p class="fl-lbl">Sábados</p>
                    <p class="fl-val">9:00 AM – 2:00 PM</p>
                  </div>
                </div>
              </div>
            </div>

          </div><!-- /cards-col -->
        </div><!-- /page-inner -->
      </main>
    </div><!-- /main-area -->

  </div><!-- /root-layout -->

  <script>
    /* ══════════════════════════════════════════════════════════════════════
     *  Navegación del sidebar (sin cambios respecto al original)
     * ═════════════════════════════════════════════════════════════════════ */
    const NAV_ITEMS = [
      { path: 'Dashboard.php',          label: 'Panel Principal',     icon: '📊' },
      { path: 'company_info.php',       label: 'Información Empresa', icon: '🏢' },
      { path: 'MyPurchases.php',        label: 'Mis Compras',         icon: '🛍️' },
      { path: 'InvoiceGeneration.php',  label: 'Generar Factura',     icon: '📄' },
      { path: 'Profile.php',            label: 'Perfil',              icon: '👤' },
    ];

    const currentPage = location.pathname.split('/').pop() || 'Dashboard.php';

    function renderNav(id) {
      const c = document.getElementById(id);
      if (!c) return;
      c.innerHTML = '';
      NAV_ITEMS.forEach(item => {
        const a = document.createElement('a');
        a.href = item.path;
        a.className = 'nav-btn' + (currentPage === item.path ? ' active' : '');
        a.innerHTML = '<span style="margin-right:.75rem;">' + item.icon + '</span>' + item.label;
        if (id === 'mobileNav') a.onclick = () => toggleMobileMenu(false);
        c.appendChild(a);
      });
    }

    function toggleMobileMenu(show) {
      document.getElementById('mobileMenuOverlay').style.display = show ? 'block' : 'none';
    }

    function handleLogout() { location.href = '../Home/Home.html'; }

    /* ══════════════════════════════════════════════════════════════════════
     *  Carga de datos corporativos desde la BD (sincronización automática)
     *
     *  Diseño — Vista Cliente:
     *    Prioridad 1: Misión y Visión (información institucional).
     *    Prioridad 2: Datos de contacto y dirección.
     *    Prioridad 3: Datos generales (nombre, giro, año).
     *
     *  Se usa cache:'no-store' para garantizar datos siempre frescos.
     * ═════════════════════════════════════════════════════════════════════ */
    document.addEventListener('DOMContentLoaded', () => {
      renderNav('desktopNav');
      renderNav('mobileNav');

      fetch('../PHP/api_home.php', { cache: 'no-store' })
        .then(res => res.json())
        .then(resp => {
          if (!resp.success || !resp.data) return;
          const d = resp.data;

          /* ── Cabecera empresa ── */
          if (d.logo_imagen) {
            const logo = document.getElementById('ci-logo');
            if (logo) logo.src = '../imagenes_empresa/' + d.logo_imagen;
          }
          setText('ci-nombre-empresa', d.nombre_empresa);
          setText('ci-giro-tag', d.giro_comercial);

          /* ── Datos generales ── */
          setText('ci_nombre_legal',  d.nombre_empresa);
          setText('ci_ano_fundacion', d.ano_fundacion);
          setText('ci_giro_comercial', d.giro_comercial);

          /* ── Misión y Visión (PRIORIDAD CORPORATIVA) ── */
          setText('ci_mision', d.mision);
          setText('ci_vision', d.vision);

          /* ── Contacto ── */
          setText('ci_email', d.email);

          // Combinar teléfonos
          let tels = d.telefono1 || '';
          if (d.telefono2 && d.telefono2.trim() !== '') {
            tels += '\n' + d.telefono2;
          }
          const telEl = document.getElementById('ci_telefonos');
          if (telEl) telEl.innerHTML = tels.replace(/\n/g, '<br>');

          // Dirección compuesta
          const partesDireccion = [
            d.calle_num,
            d.colonia,
            [d.ciudad, d.estado].filter(Boolean).join(', '),
            d.cp ? 'C.P. ' + d.cp : '',
          ].filter(Boolean);
          const dirEl = document.getElementById('ci_direccion');
          if (dirEl) dirEl.innerHTML = partesDireccion.join('<br>');
        })
        .catch(err => console.error('Error al cargar información de la empresa:', err));
    });

    /** Asigna texto a un elemento por id, solo si existe y el valor no es vacío. */
    function setText(id, valor) {
      const el = document.getElementById(id);
      if (el && valor !== null && valor !== undefined && valor !== '') {
        el.textContent = valor;
      }
    }
  </script>

  <script src="ClienteSidebar.js"></script>
</body>

</html>