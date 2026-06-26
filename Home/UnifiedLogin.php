<?php
// Forzar destrucción de sesión si el usuario llega a esta página (botón Atrás)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Iniciar Sesión – Grupo Ferretero Piedras</title>
  <link rel="apple-touch-icon" sizes="180x180" href="../Administrador/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="../Administrador/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="../Administrador/favicon-16x16.png">
  <link rel="manifest" href="../Administrador/site.webmanifest">
  <link
    href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;800&family=Barlow:wght@400;500;600&display=swap"
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
      --blue: #3e8cdd;
      --green: #74cb3c;
      --orange: #f97316;
      --red: #dc2626;
      --bg: #f3f4f6;
      --white: #ffffff;
      --gray: #6b7280;
      --gray-light: #f9fafb;
      --border: #e5e7eb;
    }

    body {
      font-family: 'Barlow', sans-serif;
      background-color: var(--bg);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .wrapper {
      width: 100%;
      max-width: 440px;
      padding: 0 16px 32px;
    }

    .back-btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: none;
      border: none;
      color: var(--blue);
      font-family: 'Barlow', sans-serif;
      font-size: .95rem;
      font-weight: 600;
      cursor: pointer;
      padding: 6px 8px;
      margin-bottom: 20px;
      text-decoration: none;
      border-radius: 6px;
      transition: background .15s;
    }

    .back-btn:hover {
      background: rgba(62, 140, 221, .09);
    }

    .back-btn svg {
      width: 16px;
      height: 16px;
      flex-shrink: 0;
    }

    .card {
      background: var(--white);
      border-radius: 16px;
      border: 2px solid var(--blue);
      box-shadow: 0 4px 24px rgba(0, 0, 0, .09);
      animation: fadeUp .4s ease both;
    }

    .card-header {
      text-align: center;
      padding: 32px 32px 18px;
    }

    .logo-wrap {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: var(--bg);
      border-radius: 10px;
      padding: 12px;
      margin-bottom: 16px;
    }

    .logo-wrap img {
      width: 64px;
      height: 64px;
      object-fit: contain;
    }

    .logo-wrap svg {
      width: 48px;
      height: 48px;
      color: var(--blue);
    }

    .card-title {
      font-family: 'Barlow Condensed', sans-serif;
      font-size: 1.6rem;
      font-weight: 800;
      color: var(--blue);
      margin-bottom: 4px;
    }

    .card-desc {
      font-size: .9rem;
      color: var(--gray);
    }

    .card-content {
      padding: 0 32px 32px;
    }

    /* ── Campos ── */
    .field {
      margin-bottom: 4px;
    }

    .field label {
      display: block;
      font-size: .875rem;
      font-weight: 600;
      margin-bottom: 6px;
      color: #374151;
    }

    .field input {
      width: 100%;
      border: 1.5px solid #d1d5db;
      border-radius: 8px;
      padding: 9px 13px;
      font-family: 'Barlow', sans-serif;
      font-size: .95rem;
      outline: none;
      transition: border-color .18s, box-shadow .18s;
      background: var(--white);
    }

    .field input:focus {
      border-color: var(--blue);
      box-shadow: 0 0 0 3px rgba(62, 140, 221, .15);
    }

    .field input:disabled {
      background: var(--bg);
      cursor: not-allowed;
      opacity: .7;
    }

    .field input::placeholder {
      color: #9ca3af;
    }

    /* Estado inválido */
    .field input.invalid {
      border-color: var(--red);
      background: #fff8f8;
      box-shadow: 0 0 0 3px rgba(220, 38, 38, .1);
    }

    /* Mensaje de error inline por campo */
    .field-error-msg {
      display: none;
      align-items: center;
      gap: 5px;
      font-size: .78rem;
      color: var(--red);
      margin-top: 5px;
      margin-bottom: 10px;
      animation: fadeIn .2s ease both;
    }

    .field-error-msg.visible {
      display: flex;
    }

    .field-error-msg svg {
      flex-shrink: 0;
    }

    /* Espacio entre campo y siguiente etiqueta cuando no hay error */
    .field-gap {
      margin-bottom: 16px;
    }

    /* ── Error general (credenciales incorrectas) ── */
    .error-msg {
      display: none;
      align-items: flex-start;
      gap: 10px;
      font-size: .875rem;
      color: var(--red);
      background: #fef2f2;
      border: 1px solid #fecaca;
      border-radius: 8px;
      padding: 10px 14px;
      margin-bottom: 16px;
    }

    .error-msg.visible {
      display: flex;
    }

    .error-msg svg {
      width: 18px;
      height: 18px;
      flex-shrink: 0;
      margin-top: 1px;
    }

    /* ── Botón ── */
    .btn-submit {
      width: 100%;
      padding: 11px;
      border: none;
      border-radius: 8px;
      background-color: var(--blue);
      color: var(--white);
      font-family: 'Barlow', sans-serif;
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: opacity .18s, transform .18s;
      margin-bottom: 14px;
    }

    .btn-submit:hover:not(:disabled) {
      opacity: .88;
      transform: translateY(-1px);
    }

    .btn-submit:disabled {
      opacity: .65;
      cursor: not-allowed;
    }

    .btn-submit svg {
      width: 18px;
      height: 18px;
    }

    .spinner {
      width: 16px;
      height: 16px;
      border: 2.5px solid rgba(255, 255, 255, .35);
      border-top-color: var(--white);
      border-radius: 50%;
      animation: spin .7s linear infinite;
      display: none;
    }

    .loading .spinner {
      display: block;
    }

    .loading .btn-icon {
      display: none;
    }

    /* ── Registro ── */
    .register-link {
      text-align: center;
      font-size: .875rem;
      color: var(--gray);
      margin-bottom: 16px;
    }

    .register-link a {
      color: var(--green);
      font-weight: 600;
      text-decoration: none;
    }

    .register-link a:hover {
      text-decoration: underline;
    }

    /* ── Hint box ── */
    .hint-box {
      background: var(--gray-light);
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: 16px;
      font-size: .82rem;
      color: var(--gray);
    }

    .hint-box .hint-title {
      font-size: .875rem;
      font-weight: 700;
      color: #374151;
      margin-bottom: 10px;
    }

    .hint-cards {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .hint-card {
      background: var(--white);
      border-radius: 6px;
      padding: 8px 10px;
      border-left: 4px solid;
    }

    .hint-card.admin {
      border-left-color: var(--blue);
    }

    .hint-card.client {
      border-left-color: var(--green);
    }

    .hint-card.emp {
      border-left-color: var(--orange);
    }

    .hint-card .role-name {
      font-weight: 700;
      font-size: .83rem;
      margin-bottom: 3px;
    }

    .hint-card.admin .role-name {
      color: var(--blue);
    }

    .hint-card.client .role-name {
      color: var(--green);
    }

    .hint-card.emp .role-name {
      color: var(--orange);
    }

    .hint-card p {
      font-size: .78rem;
      line-height: 1.5;
    }

    .hint-note {
      font-size: .78rem;
      color: #9ca3af;
      font-style: italic;
      margin-top: 10px;
    }

    @keyframes fadeUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes spin {
      to {
        transform: rotate(360deg);
      }
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(-4px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @media (max-width: 480px) {
      .card-header {
        padding: 24px 20px 14px;
      }

      .card-content {
        padding: 0 20px 24px;
      }
    }
  </style>
</head>

<body>

  <div class="wrapper">

    <a href="Home.html" class="back-btn">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
      </svg>
      Volver al inicio
    </a>

    <div class="card">

      <div class="card-header">
        <div class="logo-wrap">
          <img src="../Administrador/Logo_GF_piedra .PNG" alt="Logo Grupo Ferretero Piedras"
            style="width:80px;height:80px;"
            onerror="this.style.display='none'; this.nextElementSibling.style.display='block';" />
          <svg style="display:none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
            stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72M6.75 18h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .414.336.75.75.75z" />
          </svg>
        </div>
        <div class="card-title">Iniciar Sesión</div>
        <div class="card-desc">Ingresa tus credenciales para acceder al sistema</div>
      </div>

      <div class="card-content">

        <!-- Email -->
        <div class="field">
          <label for="email">Correo Electrónico</label>
          <input type="email" id="email" placeholder="tu.correo@ejemplo.com" required autocomplete="email" />
        </div>
        <div class="field-error-msg" id="emailError">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--red)" stroke-width="2.5"
            stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10" />
            <line x1="12" y1="8" x2="12" y2="12" />
            <line x1="12" y1="16" x2="12.01" y2="16" />
          </svg>
          <span id="emailErrorText"></span>
        </div>

        <!-- Contraseña -->
        <div class="field field-gap">
          <label for="password">Contraseña</label>
          <input type="password" id="password" placeholder="••••••••" required autocomplete="current-password" />
        </div>
        <div class="field-error-msg" id="passwordError">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--red)" stroke-width="2.5"
            stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10" />
            <line x1="12" y1="8" x2="12" y2="12" />
            <line x1="12" y1="16" x2="12.01" y2="16" />
          </svg>
          <span id="passwordErrorText"></span>
        </div>

        <!-- Error credenciales incorrectas -->
        <div class="error-msg" id="error-msg">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
          </svg>
          <span id="error-text">Credenciales incorrectas. Por favor verifica tu correo y contraseña.</span>
        </div>

        <button class="btn-submit" id="btn-submit" onclick="handleLogin()">
          <span class="spinner" id="spinner"></span>
          <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-7.5A2.25 2.25 0 003.75 5.25v13.5A2.25 2.25 0 006 21h7.5a2.25 2.25 0 002.25-2.25V15M18 15l3-3m0 0l-3-3m3 3H9" />
          </svg>
          <span id="btn-label">Iniciar Sesión</span>
        </button>

        <div class="register-link">
          ¿No tienes una cuenta?
          <a href="../Cliente/Register.php">Regístrate como cliente</a>
        </div>


      </div>
    </div>
  </div>

  <script>
    // ── Mostrar error desde URL param (redireccionado desde login.php) ────────
    (function () {
      const params = new URLSearchParams(window.location.search);
      const err = params.get('error');
      if (err) {
        document.getElementById('error-text').textContent = decodeURIComponent(err);
        document.getElementById('error-msg').classList.add('visible');
        // Limpiar URL sin recargar
        window.history.replaceState({}, document.title, window.location.pathname);
      }
    })();

    // ── Helpers ──────────────────────────────────────────────────────────────
    function showFieldError(inputId, msgId, textId, msg) {
      const input = document.getElementById(inputId);
      const msgEl = document.getElementById(msgId);
      const textEl = document.getElementById(textId);
      input.classList.add('invalid');
      textEl.textContent = msg;
      msgEl.classList.add('visible');
    }

    function clearFieldError(inputId, msgId) {
      document.getElementById(inputId).classList.remove('invalid');
      document.getElementById(msgId).classList.remove('visible');
    }

    function showGeneralError(msg) {
      const el = document.getElementById('error-msg');
      document.getElementById('error-text').textContent = msg;
      el.classList.add('visible');
    }

    function hideGeneralError() {
      document.getElementById('error-msg').classList.remove('visible');
    }

    // ── Validación email ──────────────────────────────────────────────────────
    function validateEmailField(value) {
      if (!value) return 'El correo electrónico es obligatorio.';
      if (!value.includes('@')) return 'El correo debe incluir el símbolo @.';
      const parts = value.split('@');
      if (!parts[1] || parts[1] === '') return 'Falta el dominio después del @.';
      if (!parts[1].includes('.')) return 'El dominio debe tener un punto (ej. .com).';
      if (parts[1].endsWith('.')) return 'El dominio no puede terminar en punto.';
      return null;
    }

    function validatePasswordField(value) {
      if (!value) return 'La contraseña es obligatoria.';
      return null;
    }

    // ── Limpiar errores al escribir ───────────────────────────────────────────
    document.getElementById('email').addEventListener('input', function () {
      const err = validateEmailField(this.value.trim());
      if (!err) {
        clearFieldError('email', 'emailError');
        hideGeneralError();
      } else if (this.value.length > 0) {
        showFieldError('email', 'emailError', 'emailErrorText', err);
      }
    });

    document.getElementById('password').addEventListener('input', function () {
      if (this.value) {
        clearFieldError('password', 'passwordError');
        hideGeneralError();
      }
    });

    // ── Estado de carga ───────────────────────────────────────────────────────
    function setLoading(on) {
      const btn = document.getElementById('btn-submit');
      const label = document.getElementById('btn-label');
      const inputs = document.querySelectorAll('input');
      btn.disabled = on;
      btn.classList.toggle('loading', on);
      label.textContent = on ? 'Iniciando sesión...' : 'Iniciar Sesión';
      inputs.forEach(i => i.disabled = on);
    }

    // ── Login real via fetch → PHP/login.php ──────────────────────────────────
    function handleLogin() {
      const emailVal    = document.getElementById('email').value.trim();
      const passwordVal = document.getElementById('password').value;

      // Limpiar errores previos
      clearFieldError('email', 'emailError');
      clearFieldError('password', 'passwordError');
      hideGeneralError();

      const emailErr    = validateEmailField(emailVal);
      const passwordErr = validatePasswordField(passwordVal);

      let hasError = false;
      if (emailErr) {
        showFieldError('email', 'emailError', 'emailErrorText', emailErr);
        hasError = true;
      }
      if (passwordErr) {
        showFieldError('password', 'passwordError', 'passwordErrorText', passwordErr);
        hasError = true;
      }
      if (hasError) return;

      setLoading(true);

      // Enviar credenciales al backend PHP
      const formData = new FormData();
      formData.append('email', emailVal);
      formData.append('password', passwordVal);

      fetch('../PHP/login.php', {
        method: 'POST',
        body: formData
        // Sin redirect:follow — login.php ahora devuelve JSON
      })
      .then(response => response.json())
      .then(data => {
        if (data.ok && data.redirect) {
          // Navegar explícitamente a la URL indicada por el backend
          window.location.href = data.redirect;
        } else {
          setLoading(false);
          showGeneralError(data.error || 'Error al iniciar sesión. Inténtalo de nuevo.');
        }
      })
      .catch(() => {
        setLoading(false);
        showGeneralError('Error de conexión. Verifica que el servidor esté activo.');
      });
    }

    ['email', 'password'].forEach(id => {
      document.getElementById(id).addEventListener('keydown', e => {
        if (e.key === 'Enter') handleLogin();
      });
    });
  </script>

</body>

</html>
