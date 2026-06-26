<?php require_once '../PHP/auth.php'; auth_require_rol(['admin', 'empleado']); ?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registrar Cliente</title>
  <link rel="apple-touch-icon" sizes="180x180" href="../Administrador/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="../Administrador/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="../Administrador/favicon-16x16.png">
  <link rel="manifest" href="../Administrador/site.webmanifest">
  <link
    href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap"
    rel="stylesheet" />
  <style>
    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    :root {
      --color-azul: #3e8cdd;
      --color-verde: #74cb3c;
      --color-texto: #1a1a1a;
      --color-muted: #717182;
      --color-muted-fondo: #ececf0;
      --color-borde: rgba(0, 0, 0, 0.1);
      --radio-borde: 0.625rem;
      --red: #e53e3e;
      --red-hover: #fff5f5;
      --green-bg: #f0faf0;
      --green-border: #74cb3c;
      --green-text: #2e7d32;
      --gray-900: #1a1a1a;
      --gray-700: #374151;
      --gray-600: #4b5563;
      --gray-500: #717182;
      --gray-400: #9ca3af;
      --gray-200: #e5e7eb;
      --gray-100: #f3f4f6;
      --gray-50: #f9fafb;
      --white: #ffffff;
      --shadow-md: 0 4px 16px rgba(0, 0, 0, .08);
    }

    body {
      font-family: 'Segoe UI', Arial, sans-serif;
      background: var(--gray-100);
      color: var(--color-texto);
      min-height: 100vh;
    }

    body::before {
      content: '';
      position: fixed;
      inset: 0;
      pointer-events: none;
      z-index: 0;
      background-image:
        radial-gradient(circle at 10% 20%, rgba(249, 115, 22, .06) 0%, transparent 40%),
        radial-gradient(circle at 90% 80%, rgba(249, 115, 22, .04) 0%, transparent 40%);
    }

    .container {
      width: 100%;
      max-width: none;
      padding: 20px;
    }

    .page-header {
      margin-bottom: 28px;
      animation: slideDown .45s cubic-bezier(.22, 1, .36, 1) both;
    }

    .page-header h1 {
      font-family: 'Segoe UI', Arial, sans-serif;
      font-size: clamp(26px, 5vw, 36px);
      color: var(--color-azul);
      letter-spacing: -.5px;
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .page-header p {
      margin-top: 8px;
      color: var(--gray-600);
      font-size: 14px;
      font-weight: 300;
    }

    /* ── Banner éxito ── */
    .success-banner {
      display: none;
      align-items: center;
      gap: 12px;
      padding: 14px 18px;
      background: var(--green-bg);
      border: 1px solid var(--green-border);
      border-radius: 12px;
      margin-bottom: 20px;
    }

    .success-banner.visible {
      display: flex;
    }

    .success-banner p {
      font-size: 14px;
      color: var(--green-text);
      font-weight: 500;
    }

    .success-banner small {
      font-size: 12px;
      font-weight: 300;
      display: block;
      margin-top: 1px;
      color: var(--green-text);
    }

    /* ── Banner error general ── */
    .error-banner {
      display: none;
      align-items: flex-start;
      gap: 12px;
      padding: 14px 18px;
      background: #fff5f5;
      border: 1px solid #feb2b2;
      border-radius: 12px;
      margin-bottom: 20px;
      animation: shake .35s cubic-bezier(.36, .07, .19, .97) both;
    }

    .error-banner.visible {
      display: flex;
    }

    .error-banner svg {
      flex-shrink: 0;
      margin-top: 1px;
    }

    .error-banner p {
      font-size: 14px;
      color: #c53030;
      font-weight: 600;
    }

    .error-banner ul {
      margin: 4px 0 0 0;
      padding-left: 16px;
      font-size: 13px;
      color: #c53030;
    }

    .error-banner ul li {
      margin-top: 2px;
    }

    /* ── Card ── */
    .card {
      background: var(--white);
      border: 1px solid var(--gray-200);
      border-top: 4px solid var(--color-azul);
      border-radius: 16px;
      box-shadow: var(--shadow-md);
      overflow: hidden;
      animation: fadeUp .5s cubic-bezier(.22, 1, .36, 1) .06s both;
    }

    .card-header {
      padding: 24px 28px 0;
    }

    .card-title {
      font-family: 'Segoe UI', Arial, sans-serif;
      font-size: 17px;
      font-weight: 700;
      color: var(--color-azul);
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .card-desc {
      margin-top: 5px;
      font-size: 13px;
      color: var(--gray-400);
      font-weight: 300;
    }

    .card-desc .req {
      color: var(--red);
    }

    .card-content {
      padding: 24px 28px 28px;
    }

    .form-section {
      margin-bottom: 24px;
    }

    .grid-3 {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 18px;
    }

    .grid-2 {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 18px;
    }

    @media(max-width:640px) {

      .grid-3,
      .grid-2 {
        grid-template-columns: 1fr;
      }
    }

    @media(min-width:641px) and (max-width:820px) {
      .grid-3 {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    .col-span-2 {
      grid-column: span 2;
    }

    @media(max-width:640px) {
      .col-span-2 {
        grid-column: span 1;
      }
    }

    .field {
      display: flex;
      flex-direction: column;
      gap: 5px;
    }

    label {
      font-size: 13px;
      font-weight: 500;
      color: var(--gray-700);
      display: flex;
      align-items: center;
      gap: 5px;
    }

    label svg {
      color: var(--gray-500);
      flex-shrink: 0;
    }

    label .req {
      color: var(--red);
      margin-left: 2px;
    }

    input,
    select {
      width: 100%;
      height: 40px;
      padding: 0 12px;
      border: 1.5px solid var(--gray-200);
      border-radius: 9px;
      font-family: 'Segoe UI', Arial, sans-serif;
      font-size: 14px;
      color: var(--gray-900);
      background: var(--white);
      outline: none;
      appearance: none;
      transition: border-color .15s, box-shadow .15s;
    }

    input::placeholder {
      color: var(--gray-400);
      font-weight: 300;
    }

    input:focus,
    select:focus {
      border-color: var(--color-azul);
      box-shadow: 0 0 0 3px rgba(62, 140, 221, .12);
    }

    input[readonly] {
      background: var(--gray-50);
      color: var(--gray-500);
      cursor: not-allowed;
      font-size: 13px;
      letter-spacing: .03em;
    }

    input.invalid {
      border-color: var(--red) !important;
      background: #fff8f8;
      box-shadow: 0 0 0 3px rgba(229, 62, 62, .1) !important;
    }

    input.valid {
      border-color: #22c55e !important;
      box-shadow: 0 0 0 3px rgba(34, 197, 94, .1) !important;
    }

    /* Mensaje de error inline por campo */
    .field-error {
      font-size: 11px;
      color: var(--red);
      display: none;
      align-items: center;
      gap: 4px;
      line-height: 1.4;
      margin-top: -1px;
      animation: fadeIn .2s ease both;
    }

    .field-error.show {
      display: flex;
    }

    .field small {
      font-size: 11px;
      color: var(--gray-400);
      font-weight: 300;
    }

    .select-wrap {
      position: relative;
    }

    .select-wrap select {
      padding-right: 32px;
      cursor: pointer;
    }

    .select-arrow {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      pointer-events: none;
      color: var(--gray-400);
      display: flex;
    }

    .section-title {
      font-size: 13px;
      font-weight: 700;
      color: var(--gray-700);
      text-transform: uppercase;
      letter-spacing: .06em;
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 16px;
      padding-top: 20px;
      border-top: 1px solid var(--gray-200);
    }

    .section-title svg {
      color: var(--color-azul);
    }

    .divider {
      height: 1px;
      background: var(--gray-200);
      margin: 24px 0 20px;
    }

    .form-actions {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 9px 22px;
      border-radius: 9px;
      font-size: 14px;
      font-family: 'Segoe UI', Arial, sans-serif;
      font-weight: 500;
      cursor: pointer;
      border: none;
      transition: background .15s, transform .1s, box-shadow .15s;
    }

    .btn:active {
      transform: scale(.97);
    }

    .btn-outline {
      background: transparent;
      border: 1.5px solid var(--gray-200);
      color: var(--gray-700);
    }

    .btn-outline:hover {
      background: var(--gray-100);
    }

    .btn-solid {
      background: var(--green-border);
      color: white;
      box-shadow: 0 2px 8px rgba(116, 203, 60, .3);
    }

    .btn-solid:hover {
      opacity: 0.85;
    }

    @keyframes fadeUp {
      from {
        opacity: 0;
        transform: translateY(14px)
      }

      to {
        opacity: 1;
        transform: translateY(0)
      }
    }

    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-10px)
      }

      to {
        opacity: 1;
        transform: translateY(0)
      }
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(-4px)
      }

      to {
        opacity: 1;
        transform: translateY(0)
      }
    }

    @keyframes shake {

      0%,
      100% {
        transform: translateX(0)
      }

      15% {
        transform: translateX(-6px)
      }

      30% {
        transform: translateX(6px)
      }

      45% {
        transform: translateX(-4px)
      }

      60% {
        transform: translateX(4px)
      }

      75% {
        transform: translateX(-2px)
      }

      90% {
        transform: translateX(2px)
      }
    }
  </style>
</head>

<body>
  <div class="container">

    <div class="page-header">
      <h1>
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--color-azul)" stroke-width="2"
          stroke-linecap="round" stroke-linejoin="round">
          <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
          <circle cx="9" cy="7" r="4" />
          <line x1="19" y1="8" x2="19" y2="14" />
          <line x1="22" y1="11" x2="16" y2="11" />
        </svg>
        Registrar Cliente
      </h1>
      <p>Ingresa los datos para registrar un nuevo cliente en la base de datos</p>
    </div>

    <!-- Banner éxito -->
    <div class="success-banner" id="successBanner">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--green-text)" stroke-width="2"
        stroke-linecap="round" stroke-linejoin="round">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
        <polyline points="22 4 12 14.01 9 11.01" />
      </svg>
      <div>
        <p>¡Cliente registrado con éxito!</p>
        <small>El cliente ha sido guardado en el sistema.</small>
      </div>
    </div>

    <!-- Banner error general -->
    <div class="error-banner" id="errorBanner">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#c53030" stroke-width="2"
        stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10" />
        <line x1="12" y1="8" x2="12" y2="12" />
        <line x1="12" y1="16" x2="12.01" y2="16" />
      </svg>
      <div>
        <p>Por favor ingresa los datos mínimos requeridos:</p>
        <ul id="errorList"></ul>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <div class="card-title">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-azul)" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
            <polyline points="14 2 14 8 20 8" />
            <line x1="16" y1="13" x2="8" y2="13" />
            <line x1="16" y1="17" x2="8" y2="17" />
            <polyline points="10 9 9 9 8 9" />
          </svg>
          Datos del Cliente
        </div>
        <p class="card-desc">Los campos marcados con <span class="req">*</span> son obligatorios</p>
      </div>

      <div class="card-content">
        <form id="clientForm" novalidate>

          <!-- Fila 1: ID, Nombre, Razón Social -->
          <div class="form-section grid-3">
            <div class="field">
              <label for="clientId">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--color-azul)" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round">
                  <line x1="4" y1="9" x2="20" y2="9" />
                  <line x1="4" y1="15" x2="20" y2="15" />
                  <line x1="10" y1="3" x2="8" y2="21" />
                  <line x1="16" y1="3" x2="14" y2="21" />
                </svg>
                ID de Cliente
              </label>
              <input id="clientId" type="text" readonly />
              <small>Autogenerado por el sistema</small>
            </div>

            <div class="field">
              <label for="nombre">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--color-azul)" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                  <circle cx="12" cy="7" r="4" />
                </svg>
                Nombre Completo <span class="req">*</span>
              </label>
              <input id="nombre" type="text" placeholder="Ej. Juan Pérez" required />
              <span class="field-error" id="nombreError">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="var(--red)" stroke-width="2.5"
                  stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10" />
                  <line x1="12" y1="8" x2="12" y2="12" />
                  <line x1="12" y1="16" x2="12.01" y2="16" />
                </svg>
                El nombre completo es obligatorio
              </span>
            </div>

            <div class="field">
              <label for="razonSocial">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--color-azul)" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                  <polyline points="14 2 14 8 20 8" />
                </svg>
                Razón Social
              </label>
              <input id="razonSocial" type="text" placeholder="Ej. Empresa S.A. de C.V." />
            </div>
          </div>

          <!-- Fila 2: RFC, CURP, Régimen -->
          <div class="form-section grid-3">
            <div class="field">
              <label for="rfc">RFC</label>
              <input id="rfc" type="text" placeholder="Opcional" maxlength="13" />
              <span class="field-error" id="rfcError"></span>
            </div>

            <div class="field">
              <label for="curp">CURP</label>
              <input id="curp" type="text" placeholder="Opcional" maxlength="18" />
              <span class="field-error" id="curpError"></span>
            </div>

            <div class="field">
              <label for="regimen">Régimen Fiscal</label>
              <div class="select-wrap">
                <select id="regimen">
                  <option value="">Seleccionar...</option>
                  <option value="fisica">Persona Física</option>
                  <option value="moral">Persona Moral</option>
                  <option value="resico">RESICO</option>
                  <option value="sin_obligaciones">Sin Obligaciones Fiscales</option>
                </select>
                <span class="select-arrow">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--color-azul)"
                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9" />
                  </svg>
                </span>
              </div>
            </div>
          </div>

          <!-- Fila 3: Email, Teléfono -->
          <div class="form-section grid-2">
            <div class="field">
              <label for="email">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--color-azul)" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round">
                  <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                  <polyline points="22,6 12,13 2,6" />
                </svg>
                Correo Electrónico <span class="req">*</span>
              </label>
              <input id="email" type="email" placeholder="ejemplo@correo.com" required />
              <span class="field-error" id="emailError"></span>
            </div>

            <div class="field">
              <label for="telefono">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--color-azul)" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round">
                  <path
                    d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.56 1.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.97a16 16 0 0 0 6 6l.85-.85a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21.73 16.92z" />
                </svg>
                Teléfono
              </label>
              <input id="telefono" type="tel" placeholder="10 dígitos" maxlength="10" />
              <span class="field-error" id="telError"></span>
            </div>
          </div>

          <!-- Dirección -->
          <div class="section-title">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--color-azul)" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
              <circle cx="12" cy="10" r="3" />
            </svg>
            Dirección
          </div>

          <div class="form-section grid-2">
            <div class="field col-span-2">
              <label for="direccion">Calle y Número</label>
              <input id="direccion" type="text" placeholder="Ej. Av. Principal #123" />
            </div>

            <div class="field">
              <label for="colonia">Colonia</label>
              <input id="colonia" type="text" />
            </div>

            <div class="field">
              <label for="cp">Código Postal (CP)</label>
              <input id="cp" type="text" maxlength="5" placeholder="5 dígitos" />
              <span class="field-error" id="cpError"></span>
            </div>

            <div class="field">
              <label for="ciudad">Ciudad</label>
              <input id="ciudad" type="text" />
            </div>

            <div class="field">
              <label for="estado">Estado</label>
              <input id="estado" type="text" />
            </div>
          </div>

          <div class="divider"></div>
          <div class="form-actions">
            <button type="button" class="btn btn-outline" id="clearBtn">Limpiar</button>
            <button type="submit" class="btn btn-solid">Guardar Cliente</button>
          </div>

        </form>
      </div>
    </div>
  </div>

  <script>
    // ── Icono de error reutilizable ────────────────────────────────────────────
    const errorIcon = `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="var(--red)" stroke-width="2.5"
      stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0">
      <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>
      <line x1="12" y1="16" x2="12.01" y2="16"/>
    </svg>`;

    // ── ID autogenerado ────────────────────────────────────────────────────────
    function generateId() {
      return 'CLI-' + Math.floor(Math.random() * 10000).toString().padStart(4, '0');
    }
    document.getElementById('clientId').value = generateId();

    // ── Helpers de estado ──────────────────────────────────────────────────────
    function setValid(input, errorId) {
      input.classList.remove('invalid');
      input.classList.add('valid');
      const el = document.getElementById(errorId);
      el.innerHTML = '';
      el.classList.remove('show');
    }

    function setInvalid(input, errorId, msg) {
      input.classList.add('invalid');
      input.classList.remove('valid');
      const el = document.getElementById(errorId);
      el.innerHTML = errorIcon + ' ' + msg;
      el.classList.add('show');
    }

    function clearState(input, errorId) {
      input.classList.remove('invalid', 'valid');
      const el = document.getElementById(errorId);
      el.innerHTML = '';
      el.classList.remove('show');
    }

    // ── Limpiar error al escribir ──────────────────────────────────────────────
    document.getElementById('nombre').addEventListener('input', function () {
      if (this.value.trim()) {
        this.classList.remove('invalid');
        document.getElementById('nombreError').classList.remove('show');
      }
    });

    document.getElementById('email').addEventListener('input', function () {
      validateEmail(this);
    });

    document.getElementById('rfc').addEventListener('input', function () {
      this.value = this.value.toUpperCase().replace(/[^A-ZÑ&0-9]/g, '');
      validateRFC(this);
    });

    document.getElementById('curp').addEventListener('input', function () {
      this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
      validateCURP(this);
    });

    document.getElementById('telefono').addEventListener('input', function () {
      this.value = this.value.replace(/\D/g, '');
      validateTelefono(this);
    });

    document.getElementById('cp').addEventListener('input', function () {
      this.value = this.value.replace(/\D/g, '');
      validateCP(this);
    });

    // ── Validación RFC ─────────────────────────────────────────────────────────
    function validateRFC(input) {
      const val = input.value;
      if (val === '') { clearState(input, 'rfcError'); return true; }

      const rfcFisica = /^[A-ZÑ&]{4}\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])[A-Z0-9]{3}$/i;
      const rfcMoral = /^[A-ZÑ&]{3}\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])[A-Z0-9]{3}$/i;
      const valid = rfcFisica.test(val) || rfcMoral.test(val);

      if (!valid) {
        let msg = val.length < 12
          ? 'RFC incompleto. Persona Física: 13 chars · Moral: 12 chars.'
          : 'RFC inválido. Ej. Física: RODR850101ABC · Moral: GFP850101AB3.';
        setInvalid(input, 'rfcError', msg);
      } else {
        setValid(input, 'rfcError');
      }
      return valid;
    }

    // ── Validación CURP ────────────────────────────────────────────────────────
    function validateCURP(input) {
      const val = input.value;
      if (val === '') { clearState(input, 'curpError'); return true; }

      const curpRegex = /^[A-Z]{4}\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])[HM](AS|BC|BS|CC|CL|CM|CS|CH|DF|DG|GT|GR|HG|JC|MC|MN|MS|NT|NL|OC|PL|QT|QR|SP|SL|SR|TC|TS|TL|VZ|YN|ZS|NE)[B-DF-HJ-NP-TV-Z]{3}[A-Z0-9]\d$/i;
      const valid = curpRegex.test(val);

      if (!valid) {
        const msg = val.length < 18
          ? `CURP incompleta. Faltan ${18 - val.length} carácter(es).`
          : 'CURP inválida. Ej: PEGJ850101HDFRNN09.';
        setInvalid(input, 'curpError', msg);
      } else {
        setValid(input, 'curpError');
      }
      return valid;
    }

    // ── Validación Email ───────────────────────────────────────────────────────
    function validateEmail(input) {
      const val = input.value.trim();
      if (val === '') { clearState(input, 'emailError'); return false; }

      const emailRegex = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/;
      const valid = emailRegex.test(val);

      if (!valid) {
        let msg = !val.includes('@') ? 'El correo debe incluir el símbolo @.'
          : !val.includes('.') ? 'El dominio debe incluir un punto (ej. .com).'
            : 'Correo inválido. Ej: usuario@dominio.com.';
        setInvalid(input, 'emailError', msg);
      } else {
        setValid(input, 'emailError');
      }
      return valid;
    }

    // ── Validación Teléfono ────────────────────────────────────────────────────
    function validateTelefono(input) {
      const val = input.value;
      if (val === '') { clearState(input, 'telError'); return true; }

      const valid = /^\d{10}$/.test(val);
      if (!valid) {
        const msg = val.length < 10
          ? `Teléfono incompleto. Faltan ${10 - val.length} dígito(s).`
          : 'El teléfono debe tener exactamente 10 dígitos.';
        setInvalid(input, 'telError', msg);
      } else {
        setValid(input, 'telError');
      }
      return valid;
    }

    // ── Validación Código Postal ───────────────────────────────────────────────
    function validateCP(input) {
      const val = input.value;
      if (val === '') { clearState(input, 'cpError'); return true; }

      const valid = /^\d{5}$/.test(val) && val !== '00000' && parseInt(val) >= 1000;
      if (!valid) {
        const msg = val.length < 5
          ? 'El CP debe tener exactamente 5 dígitos.'
          : 'CP inválido. Rango válido en México: 01000 – 99999.';
        setInvalid(input, 'cpError', msg);
      } else {
        setValid(input, 'cpError');
      }
      return valid;
    }

    // ── Limpiar formulario ─────────────────────────────────────────────────────
    const fieldIds = ['nombre', 'razonSocial', 'rfc', 'curp', 'email', 'regimen',
      'direccion', 'colonia', 'ciudad', 'estado', 'cp', 'telefono'];

    function clearAllErrors() {
      // Limpiar banner
      document.getElementById('errorBanner').classList.remove('visible');
      // Limpiar campo nombre
      document.getElementById('nombre').classList.remove('invalid', 'valid');
      document.getElementById('nombreError').classList.remove('show');
      // Limpiar campos con validación
      ['rfc', 'curp', 'email', 'telefono', 'cp'].forEach(id => {
        const el = document.getElementById(id);
        if (el) { el.classList.remove('invalid', 'valid'); }
      });
      ['rfcError', 'curpError', 'emailError', 'telError', 'cpError'].forEach(id => {
        const el = document.getElementById(id);
        if (el) { el.innerHTML = ''; el.classList.remove('show'); }
      });
    }

    function clearForm() {
      fieldIds.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
      });
      clearAllErrors();
    }

    function resetAll() {
      clearForm();
      document.getElementById('clientId').value = generateId();
    }

    document.getElementById('clearBtn').addEventListener('click', clearForm);

    // ── Submit ─────────────────────────────────────────────────────────────────
    document.getElementById('clientForm').addEventListener('submit', function (e) {
      e.preventDefault();
      clearAllErrors();

      const nombreEl = document.getElementById('nombre');
      const emailEl = document.getElementById('email');
      const rfcEl = document.getElementById('rfc');
      const curpEl = document.getElementById('curp');
      const telEl = document.getElementById('telefono');
      const cpEl = document.getElementById('cp');

      let errors = [];
      let hasError = false;
      let firstErrorEl = null;

      // Nombre (obligatorio)
      if (!nombreEl.value.trim()) {
        nombreEl.classList.add('invalid');
        document.getElementById('nombreError').classList.add('show');
        errors.push('Nombre Completo');
        hasError = true;
        if (!firstErrorEl) firstErrorEl = nombreEl;
      }

      // Email (obligatorio)
      if (!emailEl.value.trim()) {
        setInvalid(emailEl, 'emailError', 'El correo electrónico es obligatorio');
        errors.push('Correo Electrónico');
        hasError = true;
        if (!firstErrorEl) firstErrorEl = emailEl;
      } else if (!validateEmail(emailEl)) {
        errors.push('Correo Electrónico (formato inválido)');
        hasError = true;
        if (!firstErrorEl) firstErrorEl = emailEl;
      }

      // Opcionales: validar solo si tienen valor
      if (rfcEl.value && !validateRFC(rfcEl)) {
        errors.push('RFC (formato inválido)');
        hasError = true;
        if (!firstErrorEl) firstErrorEl = rfcEl;
      }

      if (curpEl.value && !validateCURP(curpEl)) {
        errors.push('CURP (formato inválido)');
        hasError = true;
        if (!firstErrorEl) firstErrorEl = curpEl;
      }

      if (telEl.value && !validateTelefono(telEl)) {
        errors.push('Teléfono (formato inválido)');
        hasError = true;
        if (!firstErrorEl) firstErrorEl = telEl;
      }

      if (cpEl.value && !validateCP(cpEl)) {
        errors.push('Código Postal (formato inválido)');
        hasError = true;
        if (!firstErrorEl) firstErrorEl = cpEl;
      }

      if (hasError) {
        const banner = document.getElementById('errorBanner');
        const list = document.getElementById('errorList');
        list.innerHTML = errors.map(e => `<li>${e}</li>`).join('');
        banner.classList.remove('visible');
        void banner.offsetWidth;
        banner.style.animation = 'none';
        banner.classList.add('visible');
        requestAnimationFrame(() => { banner.style.animation = ''; });
        banner.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        if (firstErrorEl) firstErrorEl.focus();
        return;
      }

      /* ════════════════════════════════════════════════════════════════════
       *  SUBMIT REAL: Enviar datos al backend PHP (Punto 1)
       *
       *  Flujo:
       *    1. Deshabilitar botón de submit
       *    2. Construir payload con TODOS los campos del formulario
       *       (names exactos coinciden con las columnas de la tabla gf_clientes)
       *    3. fetch POST a api_registrar_cliente.php
       *    4. Leer respuesta como texto primero → JSON.parse con try/catch
       *       → previene "Unexpected end of JSON input"
       *    5. console.log DEBUG de la respuesta completa del servidor (Punto 4)
       *    6. En SUCCESS: llamar actualizarListaClientes() → re-fetch del datalist
       *       y selección automática del nuevo cliente (Punto 2)
       *    7. En ERROR: mostrar mensaje específico en el banner
       * ════════════════════════════════════════════════════════════════════ */
      const submitBtn = e.submitter || document.querySelector('#clientForm [type="submit"]');
      const submitBtnText = submitBtn ? submitBtn.textContent : '';
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Guardando…';
      }

      const payload = {
        nombre:             document.getElementById('nombre').value.trim(),
        correo_electronico: document.getElementById('email').value.trim(),
        rfc:                document.getElementById('rfc').value.trim()        || null,
        curp:               document.getElementById('curp').value.trim()       || null,
        regimen:            document.getElementById('regimen').value           || null,
        telefono:           document.getElementById('telefono').value.trim()   || null,
        calle_num:          document.getElementById('direccion').value.trim()  || null,
        colonia:            document.getElementById('colonia').value.trim()    || null,
        ciudad:             document.getElementById('ciudad').value.trim()     || null,
        estado:             document.getElementById('estado').value.trim()     || null,
        cp:                 document.getElementById('cp').value.trim()         || null,
      };

      fetch('../PHP/api_registrar_cliente.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify(payload),
      })
        .then(async res => {

          // Leer como texto primero → atrapa "Unexpected end of JSON input"
          const texto = await res.text();

          // ── DEBUG: imprimir respuesta completa del servidor (Punto 4) ──
          console.log('[RegisterClient] HTTP', res.status);
          console.log('[RegisterClient] Respuesta del servidor:', texto);

          // Parsear con guardia
          let data;
          try {
            data = JSON.parse(texto);
          } catch (parseErr) {
            console.error('[RegisterClient] JSON inválido recibido:', texto.substring(0, 500));
            throw new Error(
              'El servidor devolvió una respuesta no válida. ' +
              'Detalle técnico: ' + texto.substring(0, 120)
            );
          }

          if (submitBtn) {
            submitBtn.disabled    = false;
            submitBtn.textContent = submitBtnText;
          }

          // ── Sesión expirada (Punto 5) ───────────────────────────────────
          if (res.status === 401) {
            alert('Tu sesión ha expirado. Por favor inicia sesión de nuevo.');
            if (data.redirigir) window.location.href = data.redirigir;
            return;
          }

          // ── Error del servidor ──────────────────────────────────────────
          if (data.status !== 'success') {
            const banner = document.getElementById('errorBanner');
            const list   = document.getElementById('errorList');

            let msg = data.message || 'Error desconocido al registrar el cliente.';

            // Si faltan campos mínimos → mensaje del Punto 4
            if (data.faltantes && data.faltantes.length > 0) {
              list.innerHTML = data.faltantes.map(f => `<li>${f}</li>`).join('');
            } else {
              list.innerHTML = `<li>${msg}</li>`;
            }

            banner.classList.remove('visible');
            void banner.offsetWidth;
            banner.style.animation = 'none';
            banner.classList.add('visible');
            requestAnimationFrame(() => { banner.style.animation = ''; });
            banner.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            return;
          }

          /* ── ÉXITO ────────────────────────────────────────────────────────
           *  1. Mostrar banner de éxito con nombre e ID real de la BD
           *  2. Actualizar la lista de gf_clientes en esta misma ventana
           *     y comunicar al módulo de ventas (RegisterSale) via localStorage
           * ───────────────────────────────────────────────────────────────── */
          const successBanner = document.getElementById('successBanner');
          const successSmall  = successBanner.querySelector('small');
          if (successSmall) {
            successSmall.textContent =
              `«${data.nombre}» guardado con ID #${data.id}. Disponible en el buscador de ventas.`;
          }
          successBanner.classList.add('visible');
          successBanner.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

          // Actualizar listado y pre-seleccionar el nuevo cliente (Punto 2)
          actualizarListaClientes(data.id, data.nombre);

          setTimeout(() => {
            successBanner.classList.remove('visible');
            resetAll();
          }, 4000);
        })
        .catch(err => {
          if (submitBtn) {
            submitBtn.disabled    = false;
            submitBtn.textContent = submitBtnText;
          }
          console.error('[RegisterClient] Error de red:', err);
          const banner = document.getElementById('errorBanner');
          const list   = document.getElementById('errorList');
          list.innerHTML = `<li>Error de conexión: ${err.message}</li>`;
          banner.classList.remove('visible');
          void banner.offsetWidth;
          banner.style.animation = 'none';
          banner.classList.add('visible');
          requestAnimationFrame(() => { banner.style.animation = ''; });
        });
    });

    /* ════════════════════════════════════════════════════════════════════════
     *  actualizarListaClientes(nuevoId, nuevoNombre)
     *  ─────────────────────────────────────────────
     *  Punto 2: Tras el registro exitoso, re-obtiene la lista de gf_clientes
     *  desde la BD (mismo endpoint que RegisterSale usa) y:
     *    • Actualiza el datalist local (si existe en esta vista)
     *    • Guarda en localStorage el nuevo cliente para que RegisterSale
     *      lo seleccione automáticamente al cargar o al volver al foco
     *
     *  Punto 3: El datalist ya implementado en RegisterSale filtra por
     *  nombre conforme el empleado escribe — sin LIMIT en la consulta SQL.
     * ════════════════════════════════════════════════════════════════════════ */
    async function actualizarListaClientes(nuevoId, nuevoNombre) {
      try {
        const res   = await fetch('../PHP/api_venta_datos.php');
        const texto = await res.text();

        console.log('[actualizarListaClientes] HTTP', res.status);
        console.log('[actualizarListaClientes] Respuesta:', texto.substring(0, 200));

        let data;
        try { data = JSON.parse(texto); } catch (e) {
          console.warn('[actualizarListaClientes] No se pudo parsear la respuesta del datalist.');
          data = null;
        }

        if (data && !data.error && Array.isArray(data.clientes)) {
          // Guardar en localStorage para que RegisterSale lo consuma
          // al recibir el foco o en la próxima carga
          const info = {
            id:     nuevoId,
            nombre: nuevoNombre,
            ts:     Date.now(),        // timestamp para evitar consumo duplicado
          };
          localStorage.setItem('clienteRecienRegistrado', JSON.stringify(info));
          console.log('[actualizarListaClientes] Nuevo cliente guardado en localStorage:', info);

          // Si hay un datalist en esta misma página (compatibilidad futura)
          const dl = document.getElementById('clientDatalist');
          if (dl) {
            dl.innerHTML = '';
            data.clientes.forEach(c => {
              const opt = document.createElement('option');
              opt.value = `${c.nombre} (ID: ${c.id})`;
              dl.appendChild(opt);
            });
          }
        }
      } catch (err) {
        console.warn('[actualizarListaClientes] Error al actualizar lista:', err.message);
        // No crítico — el cliente ya fue guardado en la BD
      }
    }
  </script>

  <script src="sidebar.js"></script>
</body>

</html>