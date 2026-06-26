<?php require_once '../PHP/auth.php'; auth_require_rol(['admin', 'empleado']); ?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registrar Venta</title>
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
      --green: #74cb3c;
      --shadow-md: 0 4px 12px rgba(0, 0, 0, .08);
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
      background-image: radial-gradient(circle at 15% 25%, rgba(249, 115, 22, .06) 0%, transparent 40%),
        radial-gradient(circle at 85% 75%, rgba(249, 115, 22, .04) 0%, transparent 40%);
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

    /* ── Banner de éxito ── */
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

    .success-banner svg {
      color: var(--green-text);
      flex-shrink: 0;
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
    }

    /* ── Banner de error general (resumen) ── */
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
      font-weight: 400;
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

    .card+.card {
      margin-top: 20px;
      animation-delay: .12s;
    }

    .card-header {
      padding: 24px 28px 0;
    }

    .card-title {
      font-family: 'Georgia', sans-serif;
      font-size: 17px;
      font-weight: 700;
      color: var(--color-azul);
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .card-title.green {
      color: var(--green);
    }

    .card-desc {
      margin-top: 5px;
      font-size: 13px;
      color: var(--gray-400);
      font-weight: 300;
    }

    .card-content {
      padding: 24px 28px 28px;
    }

    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }

    @media(max-width:560px) {
      .form-grid {
        grid-template-columns: 1fr;
      }
    }

    .field {
      display: flex;
      flex-direction: column;
      gap: 7px;
    }

    label {
      font-size: 13px;
      font-weight: 500;
      color: var(--gray-700);
      display: flex;
      align-items: center;
      gap: 6px;
    }

    label svg {
      color: var(--gray-500);
      flex-shrink: 0;
    }

    input,
    select {
      width: 100%;
      height: 40px;
      padding: 0 12px;
      border: 1.5px solid var(--gray-200);
      border-radius: 9px;
      font-family: 'Georgia', sans-serif;
      font-size: 14px;
      color: var(--gray-900);
      background: var(--white);
      outline: none;
      appearance: none;
      transition: border-color .15s, box-shadow .15s;
    }

    input:focus,
    select:focus {
      border-color: var(--color-azul);
      box-shadow: 0 0 0 3px rgba(62, 140, 221, .12);
    }

    /* Estado de error en inputs/selects */
    input.field-error,
    select.field-error {
      border-color: var(--red);
      background: #fff8f8;
      box-shadow: 0 0 0 3px rgba(229, 62, 62, .1);
    }

    input[readonly] {
      background: var(--gray-50);
      color: var(--gray-600);
      cursor: not-allowed;
    }

    .field small {
      font-size: 11px;
      color: var(--gray-400);
      font-weight: 300;
    }

    /* Mensaje de error inline por campo */
    .field-error-msg {
      display: none;
      font-size: 12px;
      color: var(--red);
      font-weight: 500;
      align-items: center;
      gap: 4px;
      animation: fadeIn .2s ease both;
    }

    .field-error-msg.visible {
      display: flex;
    }

    .input-wrap {
      position: relative;
    }

    .input-prefix {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 14px;
      color: var(--gray-500);
      pointer-events: none;
      z-index: 1;
    }

    .input-wrap input {
      padding-left: 22px;
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
      padding: 9px 20px;
      border-radius: 9px;
      font-size: 14px;
      font-family: 'Segoe UI', Arial, serif;
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
      background: var(--green);
      color: var(--white);
      box-shadow: 0 2px 8px rgba(116, 203, 60, .3);
    }

    .btn-solid:hover {
      opacity: 0.85;
    }

    /* ── Productos: zona de agregar ── */
    .add-product-row {
      display: grid;
      grid-template-columns: 1fr 120px auto;
      gap: 12px;
      align-items: end;
    }

    @media(max-width:560px) {
      .add-product-row {
        grid-template-columns: 1fr;
      }
    }

    .btn-add {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 9px 18px;
      border-radius: 9px;
      font-size: 14px;
      font-family: 'Segoe UI', Arial, serif;
      font-weight: 600;
      cursor: pointer;
      border: 2px solid var(--color-azul);
      background: white;
      color: var(--color-azul);
      transition: background .15s, transform .1s;
      height: 40px;
    }

    .btn-add:hover {
      background: #eff6ff;
    }

    .btn-add:active {
      transform: scale(.97);
    }

    .btn-add:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }

    .btn-add svg {
      width: 16px;
      height: 16px;
    }

    .stock-info {
      font-size: 11px;
      color: var(--gray-400);
      margin-top: 2px;
    }

    .stock-info.warn {
      color: #d97706;
      font-weight: 500;
    }

    /* ── Tabla de productos agregados ── */
    .products-table-wrap {
      margin-top: 20px;
      border: 1.5px solid var(--gray-200);
      border-radius: 12px;
      overflow: hidden;
    }

    .products-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
    }

    .products-table thead {
      background: var(--gray-50);
    }

    .products-table th {
      padding: 10px 14px;
      text-align: left;
      font-weight: 600;
      font-size: 12px;
      color: var(--gray-500);
      text-transform: uppercase;
      letter-spacing: 0.3px;
      border-bottom: 1.5px solid var(--gray-200);
    }

    .products-table td {
      padding: 12px 14px;
      border-bottom: 1px solid var(--gray-100);
      vertical-align: middle;
    }

    .products-table tr:last-child td {
      border-bottom: none;
    }

    .products-table .col-id {
      color: var(--color-azul);
      font-weight: 600;
      font-size: 12px;
    }

    .products-table .col-name {
      font-weight: 500;
      color: var(--gray-900);
    }

    .products-table .col-qty {
      text-align: center;
    }

    .products-table .col-price,
    .products-table .col-subtotal {
      text-align: right;
      font-family: 'Georgia', sans-serif;
    }

    .products-table .col-subtotal {
      color: var(--green);
      font-weight: 700;
    }

    .products-table .col-action {
      text-align: center;
      width: 50px;
    }

    .btn-remove {
      background: none;
      border: none;
      cursor: pointer;
      color: var(--red);
      padding: 4px;
      border-radius: 6px;
      transition: background .15s;
      display: inline-flex;
      align-items: center;
    }

    .btn-remove:hover {
      background: #fff5f5;
    }

    .btn-remove svg {
      width: 16px;
      height: 16px;
    }

    /* ── Fila de total ── */
    .total-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 16px 20px;
      background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
      border: 2px solid var(--green);
      border-radius: 12px;
      margin-top: 16px;
    }

    .total-row .total-label {
      font-weight: 700;
      font-size: 15px;
      color: var(--gray-700);
    }

    .total-row .total-value {
      font-family: 'Georgia', sans-serif;
      font-weight: 700;
      font-size: 24px;
      color: var(--green);
    }

    /* ── Empty state productos ── */
    .empty-products {
      text-align: center;
      padding: 32px 20px;
      color: var(--gray-400);
    }

    .empty-products svg {
      margin-bottom: 8px;
      color: var(--gray-200);
    }

    .empty-products p {
      font-size: 14px;
      font-weight: 400;
    }

    .empty-products small {
      font-size: 12px;
      font-weight: 300;
    }

    /* ── Badge de cantidad ── */
    .product-count-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: var(--color-azul);
      color: white;
      font-size: 11px;
      font-weight: 700;
      min-width: 22px;
      height: 22px;
      border-radius: 99px;
      padding: 0 6px;
      margin-left: 8px;
    }

    /* Responsive table */
    @media(max-width:640px) {

      .products-table th:nth-child(1),
      .products-table td:nth-child(1) {
        display: none;
      }

      .products-table th,
      .products-table td {
        padding: 10px 8px;
        font-size: 12px;
      }
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

    @keyframes rowIn {
      from {
        opacity: 0;
        transform: translateX(-12px);
      }

      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    .products-table tbody tr {
      animation: rowIn .3s ease both;
    }
  </style>
</head>

<body>
  <div class="container">

    <div class="page-header">
      <h1>
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--color-azul)" stroke-width="2"
          stroke-linecap="round" stroke-linejoin="round">
          <circle cx="9" cy="21" r="1" />
          <circle cx="20" cy="21" r="1" />
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
        </svg>
        Registrar Venta
      </h1>
      <p>Ingresa los detalles para registrar una nueva venta en el sistema</p>
    </div>

    <!-- Banner éxito -->
    <div class="success-banner" id="successBanner">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--green-text)" stroke-width="2"
        stroke-linecap="round" stroke-linejoin="round">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
        <polyline points="22 4 12 14.01 9 11.01" />
      </svg>
      <div>
        <p>¡Venta registrada con éxito!</p>
        <small>La venta ha sido guardada en el sistema.</small>
      </div>
    </div>

    <!-- Banner de error general -->
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

    <!-- ═══════════════════════════════════ -->
    <!-- CARD 1: Datos Generales de la Venta -->
    <!-- ═══════════════════════════════════ -->
    <div class="card">
      <div class="card-header">
        <div class="card-title">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-azul)" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <polyline points="9 11 12 14 22 4" />
            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
          </svg>
          Datos Generales
        </div>
        <p class="card-desc">Información general de la venta</p>
      </div>
      <div class="card-content">
        <div class="form-grid" style="margin-bottom:20px">
          <!-- ID Venta -->
          <div class="field">
            <label for="saleId">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--color-azul)" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <line x1="4" y1="9" x2="20" y2="9" />
                <line x1="4" y1="15" x2="20" y2="15" />
                <line x1="10" y1="3" x2="8" y2="21" />
                <line x1="16" y1="3" x2="14" y2="21" />
              </svg>
              ID de la Venta (Autogenerado)
            </label>
            <input id="saleId" type="text" readonly />
          </div>

          <!-- Fecha -->
          <div class="field">
            <label for="date">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--color-azul)" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                <line x1="16" y1="2" x2="16" y2="6" />
                <line x1="8" y1="2" x2="8" y2="6" />
                <line x1="3" y1="10" x2="21" y2="10" />
              </svg>
              Fecha <span style="color:var(--red);margin-left:2px">*</span>
            </label>
            <input id="date" type="date" required />
            <span class="field-error-msg" id="dateError">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--red)" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <line x1="12" y1="16" x2="12.01" y2="16" />
              </svg>
              Selecciona una fecha válida
            </span>
          </div>
        </div>

        <div class="form-grid">
          <!-- Cliente — buscador tipo datalist (Punto 3) -->
          <div class="field">
            <label for="clientSearch">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--color-azul)" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                <circle cx="12" cy="7" r="4" />
              </svg>
              Cliente <span style="color:var(--red);margin-left:2px">*</span>
            </label>
            <!--
              INPUT + DATALIST:
              Permite escribir el nombre y ver sugerencias en tiempo real.
              El id numérico real se captura en el hidden input #clientIdHidden.
              El backend solo usa clientIdHidden para el INSERT en gf_ventas_ticket.
            -->
            <input
              type="text"
              id="clientSearch"
              list="clientDatalist"
              placeholder="Escribe el nombre del cliente…"
              autocomplete="off"
              required
            />
            <datalist id="clientDatalist"></datalist>
            <!-- Hidden: almacena el id numérico del cliente seleccionado -->
            <input type="hidden" id="clientIdHidden" value="" />

            <span class="field-error-msg" id="clientError">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--red)" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <line x1="12" y1="16" x2="12.01" y2="16" />
              </svg>
              Debes seleccionar un cliente válido de la lista
            </span>
          </div>

          <!-- Trabajador (auto desde sesión PHP) -->
          <div class="field">
            <label for="workerId">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--color-azul)" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                <circle cx="12" cy="7" r="4" />
              </svg>
              Vendedor (automático)
            </label>
            <!--
              Este campo se llena desde $_SESSION['nombre'] al cargar la página.
              El id real del trabajador lo usa el backend desde $_SESSION['id_trabajador'].
              No es editable: garantiza que la venta quede asignada al usuario correcto.
            -->
            <input id="workerId" type="text" value="Cargando…" readonly />
            <small>Asignado automáticamente desde tu sesión activa</small>
          </div>
        </div>
      </div>
    </div>

    <!-- ═══════════════════════════════════ -->
    <!-- CARD 2: Productos de la Venta      -->
    <!-- ═══════════════════════════════════ -->
    <div class="card">
      <div class="card-header">
        <div class="card-title green">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z" />
            <line x1="7" y1="7" x2="7.01" y2="7" />
          </svg>
          Materiales de la Venta
          <span class="product-count-badge" id="productCountBadge" style="display:none;">0</span>
        </div>
        <p class="card-desc">Selecciona los gf_materiales y cantidades para esta venta</p>
      </div>
      <div class="card-content">

        <!-- Fila para agregar producto -->
        <div class="add-product-row">
          <div class="field">
            <label for="materialSelect">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z" />
                <line x1="7" y1="7" x2="7.01" y2="7" />
              </svg>
              Material <span style="color:var(--red);margin-left:2px">*</span>
            </label>
            <div class="select-wrap">
              <select id="materialSelect">
                <option value="" disabled selected>Seleccionar material...</option>
              </select>
              <span class="select-arrow">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2.5"
                  stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="6 9 12 15 18 9" />
                </svg>
              </span>
            </div>
            <span class="stock-info" id="stockInfo"></span>
          </div>

          <div class="field">
            <label for="materialQty">
              Cantidad <span style="color:var(--red);margin-left:2px">*</span>
            </label>
            <input id="materialQty" type="number" min="1" placeholder="0" />
            <span class="field-error-msg" id="qtyError">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--red)" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <line x1="12" y1="16" x2="12.01" y2="16" />
              </svg>
              <span id="qtyErrorText">Ingresa una cantidad válida</span>
            </span>
          </div>

          <button type="button" class="btn-add" id="btnAddProduct" onclick="addProduct()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
              stroke-linejoin="round">
              <line x1="12" y1="5" x2="12" y2="19" />
              <line x1="5" y1="12" x2="19" y2="12" />
            </svg>
            Agregar
          </button>
        </div>

        <!-- Tabla de productos agregados -->
        <div id="productsTableWrap" class="products-table-wrap" style="display:none;">
          <table class="products-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Material</th>
                <th style="text-align:center;">Cant.</th>
                <th style="text-align:right;">P. Unit.</th>
                <th style="text-align:right;">Subtotal</th>
                <th style="text-align:center;">Quitar</th>
              </tr>
            </thead>
            <tbody id="productsBody"></tbody>
          </table>
        </div>

        <!-- Empty state -->
        <div class="empty-products" id="emptyProducts">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z" />
            <line x1="7" y1="7" x2="7.01" y2="7" />
          </svg>
          <p>No hay gf_materiales agregados</p>
          <small>Selecciona un material y cantidad para comenzar</small>
        </div>

        <!-- Error: sin productos -->
        <span class="field-error-msg" id="productsError" style="margin-top:8px;">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--red)" stroke-width="2.5"
            stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10" />
            <line x1="12" y1="8" x2="12" y2="12" />
            <line x1="12" y1="16" x2="12.01" y2="16" />
          </svg>
          Debes agregar al menos un material a la venta
        </span>

        <!-- Total -->
        <div class="total-row" id="totalRow" style="display:none;">
          <span class="total-label">Total de la Venta:</span>
          <span class="total-value" id="totalValue">$0.00</span>
        </div>

        <div class="divider"></div>
        <div class="form-actions">
          <button type="button" class="btn btn-outline" id="clearBtn">Limpiar</button>
          <button type="button" class="btn btn-solid" id="submitBtn">Guardar Venta</button>
        </div>

      </div>
    </div>

  </div>

  <script>
    /* ==================================================================
       DATOS DESDE LA BD — API REAL
       ==================================================================
       Sustituye los datos mock anteriores.
       api_venta_datos.php retorna:
         { gf_clientes: [...], gf_materiales: [...], trabajador: { id, nombre } }

       Los gf_materiales vienen ya filtrados con existencias > 0 del servidor.
    ================================================================== */
    const API_DATOS      = '../PHP/api_venta_datos.php';
    const API_REGISTRAR  = '../PHP/api_venta_registrar.php';

    // Cópia local de gf_materiales (pobla al cargar)
    let MATERIALES = [];

    // Estado de la venta
    let saleProducts = []; // { id, nombre, cantidad, precio_unitario, subtotal }

    // Stock local (se actualiza con los datos reales del API)
    let localStock = {};

    // ── Cargar datos desde la BD ──────────────────────────────────────────
    // clienteMap: { "Nombre (ID: 6001)": 6001, ... } → para resolver el id al seleccionar
    let clienteMap = {};

    async function cargarDatos() {
      try {
        const res  = await fetch(API_DATOS);

        // Guardia contra respuesta vacía ("Unexpected end of JSON input")
        const texto = await res.text();
        if (!texto || texto.trim() === '') {
          throw new Error('El servidor devolvió una respuesta vacía. Verifica la configuración PHP.');
        }

        let data;
        try { data = JSON.parse(texto); }
        catch (parseErr) {
          console.error('[cargarDatos] Respuesta no-JSON del servidor:', texto.substring(0, 300));
          throw new Error('El servidor envó una respuesta inválida. Detalle: ' + texto.substring(0, 120));
        }

        if (data.error) throw new Error(data.error);

        // Poblar array MATERIALES con datos reales
        MATERIALES = data.materiales || [];
        MATERIALES.forEach(m => { localStock[m.id] = m.existencias; });

        /*
         * DATALIST de gf_clientes (Punto 3):
         * Usa <datalist> + <input type="text"> en lugar de <select>.
         * El usuario escribe el nombre y el browser muestra sugerencias.
         * Al seleccionar, el evento 'input' resuelve el id numérico
         * guardado en clienteMap y lo deposita en #clientIdHidden.
         */
        const datalist = document.getElementById('clientDatalist');
        datalist.innerHTML = '';
        clienteMap = {};

        (data.clientes || []).forEach(c => {
          const etiqueta = `${c.nombre} (ID: ${c.id})`;
          clienteMap[etiqueta] = c.id;

          const opt = document.createElement('option');
          opt.value = etiqueta;
          datalist.appendChild(opt);
        });

        // Mostrar nombre del trabajador desde la sesión
        const tr = data.trabajador;
        document.getElementById('workerId').value =
          (tr && tr.nombre) ? tr.nombre : 'Sin sesión — inicia sesión';

        // Inicializar el dropdown de gf_materiales
        populateMaterialSelect();

      } catch (err) {
        const banner = document.getElementById('errorBanner');
        const list   = document.getElementById('errorList');
        list.innerHTML = `<li>No se pudo conectar con el servidor: ${err.message}</li>`;
        banner.classList.add('visible');
        console.error('[VentaDatos]', err);
      }
    }

    // Resolver id numérico al escribir/seleccionar en el datalist
    document.addEventListener('DOMContentLoaded', function () {
      const searchInput = document.getElementById('clientSearch');
      const hiddenId    = document.getElementById('clientIdHidden');
      if (searchInput) {
        searchInput.addEventListener('input', function () {
          const val = this.value.trim();
          hiddenId.value = clienteMap[val] !== undefined ? clienteMap[val] : '';
          // Limpiar error si el id se resolvió
          if (hiddenId.value) {
            searchInput.classList.remove('field-error');
            document.getElementById('clientError').classList.remove('visible');
          }
        });
      }
    });

    // ── Helpers ──
    function fmtMXN(n) {
      return '$' + n.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function generateId() {
      return 'VNT-' + Math.floor(Math.random() * 10000).toString().padStart(4, '0');
    }

    // ── Poblar selector de gf_materiales (solo con existencias > 0) ──
    function populateMaterialSelect() {
      const select = document.getElementById('materialSelect');
      // Keep the first placeholder option
      select.innerHTML = '<option value="" disabled selected>Seleccionar material...</option>';

      MATERIALES.forEach(mat => {
        const stock = localStock[mat.id];
        if (stock > 0) {
          const opt = document.createElement('option');
          opt.value = mat.id;
          opt.textContent = `${mat.id} — ${mat.nombre}`;
          select.appendChild(opt);
        }
      });
    }

    // ── Mostrar info de stock al seleccionar material ──
    document.getElementById('materialSelect').addEventListener('change', function () {
      const matId = parseInt(this.value);
      const mat = MATERIALES.find(m => m.id === matId);
      const stockInfo = document.getElementById('stockInfo');
      const qtyInput = document.getElementById('materialQty');

      if (mat) {
        const stock = localStock[mat.id];
        stockInfo.textContent = `Precio: ${fmtMXN(mat.precio_unitario)} · Disponibles: ${stock} unidades`;
        stockInfo.className = stock <= 10 ? 'stock-info warn' : 'stock-info';
        qtyInput.max = stock;
        qtyInput.value = '';
        qtyInput.focus();
      } else {
        stockInfo.textContent = '';
        stockInfo.className = 'stock-info';
      }
    });

    // ── Agregar producto a la lista ──
    function addProduct() {
      const select = document.getElementById('materialSelect');
      const qtyInput = document.getElementById('materialQty');
      const qtyError = document.getElementById('qtyError');
      const qtyErrorText = document.getElementById('qtyErrorText');

      // Validar material seleccionado
      if (!select.value) {
        select.classList.add('field-error');
        return;
      }
      select.classList.remove('field-error');

      const matId = parseInt(select.value);
      const mat = MATERIALES.find(m => m.id === matId);
      const qty = parseInt(qtyInput.value);
      const availableStock = localStock[mat.id];

      // Validar cantidad
      if (!qty || qty <= 0) {
        qtyInput.classList.add('field-error');
        qtyErrorText.textContent = 'Ingresa una cantidad mayor a 0';
        qtyError.classList.add('visible');
        return;
      }

      if (qty > availableStock) {
        qtyInput.classList.add('field-error');
        qtyErrorText.textContent = `Stock insuficiente. Máximo disponible: ${availableStock}`;
        qtyError.classList.add('visible');
        return;
      }

      // Limpiar errores
      qtyInput.classList.remove('field-error');
      qtyError.classList.remove('visible');
      document.getElementById('productsError').classList.remove('visible');

      // Verificar si ya existe en la lista
      const existingIdx = saleProducts.findIndex(p => p.id === matId);
      if (existingIdx >= 0) {
        // Verificar stock total incluyendo lo ya agregado
        const newTotal = saleProducts[existingIdx].cantidad + qty;
        const originalStock = MATERIALES.find(m => m.id === matId).existencias;
        if (newTotal > originalStock) {
          qtyInput.classList.add('field-error');
          qtyErrorText.textContent = `Ya tienes ${saleProducts[existingIdx].cantidad} en la lista. Máximo adicional: ${availableStock}`;
          qtyError.classList.add('visible');
          return;
        }
        saleProducts[existingIdx].cantidad = newTotal;
        saleProducts[existingIdx].subtotal = newTotal * mat.precio_unitario;
      } else {
        saleProducts.push({
          id: mat.id,
          nombre: mat.nombre,
          cantidad: qty,
          precio_unitario: mat.precio_unitario,
          subtotal: qty * mat.precio_unitario
        });
      }

      // Descontar del stock local
      localStock[mat.id] -= qty;

      // Reset campos
      select.value = '';
      qtyInput.value = '';
      document.getElementById('stockInfo').textContent = '';

      // Re-poblar dropdown (para actualizar stock disponible)
      populateMaterialSelect();

      // Renderizar tabla
      renderProductsTable();
    }

    // ── Quitar producto de la lista ──
    function removeProduct(matId) {
      const idx = saleProducts.findIndex(p => p.id === matId);
      if (idx < 0) return;

      // Devolver stock
      localStock[matId] += saleProducts[idx].cantidad;

      // Remover de la lista
      saleProducts.splice(idx, 1);

      // Re-poblar dropdown
      populateMaterialSelect();

      // Renderizar tabla
      renderProductsTable();
    }

    // ── Renderizar tabla de productos ──
    function renderProductsTable() {
      const wrap = document.getElementById('productsTableWrap');
      const body = document.getElementById('productsBody');
      const empty = document.getElementById('emptyProducts');
      const totalRow = document.getElementById('totalRow');
      const badge = document.getElementById('productCountBadge');

      if (saleProducts.length === 0) {
        wrap.style.display = 'none';
        empty.style.display = 'block';
        totalRow.style.display = 'none';
        badge.style.display = 'none';
        return;
      }

      wrap.style.display = 'block';
      empty.style.display = 'none';
      totalRow.style.display = 'flex';
      badge.style.display = 'inline-flex';
      badge.textContent = saleProducts.length;

      body.innerHTML = saleProducts.map(p => `
        <tr>
          <td class="col-id">${p.id}</td>
          <td class="col-name">${p.nombre}</td>
          <td class="col-qty" style="text-align:center;">${p.cantidad}</td>
          <td class="col-price" style="text-align:right;">${fmtMXN(p.precio_unitario)}</td>
          <td class="col-subtotal" style="text-align:right;">${fmtMXN(p.subtotal)}</td>
          <td class="col-action" style="text-align:center;">
            <button type="button" class="btn-remove" onclick="removeProduct(${p.id})" title="Quitar producto">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="3 6 5 6 21 6" />
                <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
              </svg>
            </button>
          </td>
        </tr>
      `).join('');

      // Calcular total
      const total = saleProducts.reduce((sum, p) => sum + p.subtotal, 0);
      document.getElementById('totalValue').textContent = fmtMXN(total);
    }

    // ── Errores helpers ──
    function clearFieldError(inputEl, msgEl) {
      inputEl.classList.remove('field-error');
      if (msgEl) msgEl.classList.remove('visible');
    }

    function showFieldError(inputEl, msgEl) {
      inputEl.classList.add('field-error');
      if (msgEl) msgEl.classList.add('visible');
    }

    function clearAllErrors() {
      ['date', 'clientSearch'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.remove('field-error');
      });
      document.getElementById('dateError').classList.remove('visible');
      document.getElementById('clientError').classList.remove('visible');
      document.getElementById('productsError').classList.remove('visible');
      document.getElementById('errorBanner').classList.remove('visible');
      document.getElementById('materialSelect').classList.remove('field-error');
      document.getElementById('materialQty').classList.remove('field-error');
      document.getElementById('qtyError').classList.remove('visible');
    }

    // Limpiar errores al corregir
    document.getElementById('date').addEventListener('change', function () {
      if (this.value) clearFieldError(this, document.getElementById('dateError'));
    });
    // clientSearch: el resolver del datalist ya limpia el error (ver arriba)

    // Reset completo
    function resetForm() {
      // Limpiar buscador de cliente y id oculto
      const cs = document.getElementById('clientSearch');
      const ch = document.getElementById('clientIdHidden');
      if (cs) cs.value = '';
      if (ch) ch.value = '';

      document.getElementById('saleId').value   = generateId();
      saleProducts = [];

      // Restaurar stock local volviendo a los valores actuales de MATERIALES
      MATERIALES.forEach(m => { localStock[m.id] = m.existencias; });

      populateMaterialSelect();
      renderProductsTable();
      clearAllErrors();

      document.getElementById('materialSelect').value = '';
      document.getElementById('materialQty').value    = '';
      document.getElementById('stockInfo').textContent = '';
    }

    document.getElementById('clearBtn').addEventListener('click', resetForm);

    // ── Submit ──
    document.getElementById('submitBtn').addEventListener('click', async function () {
      clearAllErrors();

      const date        = document.getElementById('date');
      const clientSearch = document.getElementById('clientSearch');
      const clientHidden = document.getElementById('clientIdHidden');

      let errors = [];
      let hasError = false;

      if (!date.value) {
        showFieldError(date, document.getElementById('dateError'));
        errors.push('Fecha de la venta');
        hasError = true;
      }

      // Validar cliente: el id numérico debe estar resuelto en el hidden input
      if (!clientHidden.value || parseInt(clientHidden.value) <= 0) {
        showFieldError(clientSearch, document.getElementById('clientError'));
        errors.push('Cliente (selecciona uno de la lista de sugerencias)');
        hasError = true;
      }

      if (saleProducts.length === 0) {
        document.getElementById('productsError').classList.add('visible');
        errors.push('Materiales (agrega al menos un producto)');
        hasError = true;
      }

      if (hasError) {
        const banner = document.getElementById('errorBanner');
        const list = document.getElementById('errorList');
        list.innerHTML = errors.map(e => `<li>${e}</li>`).join('');
        banner.classList.remove('visible');
        void banner.offsetWidth;
        banner.style.animation = 'none';
        banner.classList.add('visible');
        banner.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        return;
      }

      // Todo OK → enviar al servidor
      const submitBtn = document.getElementById('submitBtn');
      submitBtn.disabled    = true;
      submitBtn.textContent = 'Guardando…';

      const payload = {
        id_clientes: parseInt(clientHidden.value),   // id numérico del datalist
        fecha: document.getElementById('date').value,
        items: saleProducts.map(p => ({
          id_materiales: p.id,
          cantidad:      p.cantidad
        }))
      };

      try {
        const res    = await fetch(API_REGISTRAR, {
          method:  'POST',
          headers: { 'Content-Type': 'application/json' },
          body:    JSON.stringify(payload)
        });

        // Leer como texto primero para atrapar "Unexpected end of JSON input"
        const texto = await res.text();
        if (!texto || texto.trim() === '') {
          throw new Error('El servidor devolvió una respuesta vacía.');
        }

        let data;
        try { data = JSON.parse(texto); }
        catch (pe) {
          console.error('[submit] Respuesta no-JSON:', texto.substring(0, 300));
          throw new Error('Respuesta inválida del servidor. Detalle: ' + texto.substring(0, 100));
        }

        submitBtn.disabled    = false;
        submitBtn.textContent = 'Guardar Venta';

        // Si la sesión expiró, redirigir al login
        if (res.status === 401) {
          alert('Tu sesión ha expirado. Por favor inicia sesión de nuevo.');
          if (data.redirigir) window.location.href = data.redirigir;
          return;
        }

        if (data.error) {
          const banner = document.getElementById('errorBanner');
          const list   = document.getElementById('errorList');
          list.innerHTML  = `<li>${data.error}</li>`;
          if (data.detalle) {
            data.detalle.forEach(d => {
              list.innerHTML += `<li>${d}</li>`;
            });
          }
          banner.classList.remove('visible');
          void banner.offsetWidth;
          banner.style.animation = 'none';
          banner.classList.add('visible');
          banner.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
          return;
        }

        // Éxito
        const b = document.getElementById('successBanner');
        b.querySelector('small').textContent =
          `Venta #${data.id_venta} guardada. El inventario ha sido actualizado.`;
        b.classList.add('visible');
        b.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        setTimeout(() => {
          b.classList.remove('visible');
          resetForm();
          cargarDatos();
        }, 3000);

      } catch (err) {
        submitBtn.disabled    = false;
        submitBtn.textContent = 'Guardar Venta';
        const banner = document.getElementById('errorBanner');
        const list   = document.getElementById('errorList');
        list.innerHTML = `<li>Error de red: ${err.message}</li>`;
        banner.classList.add('visible');
      }
    });

    // ── Allow Enter to add product ──
    document.getElementById('materialQty').addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        addProduct();
      }
    });

    // ── Init ──
    function init() {
      document.getElementById('saleId').value  = generateId();
      document.getElementById('date').value    = new Date().toISOString().split('T')[0];
      renderProductsTable();
      // Cargar datos y luego consumir cliente recién registrado (Punto 2)
      cargarDatos().then(() => {
        consumirClienteRecienRegistrado();
      });
    }

    /* ── Consumir cliente recién registrado desde localStorage (Punto 2) ──
     *
     *  RegisterClient.html escribe en localStorage:
     *    { id: 6022, nombre: "Nuevo Cliente", ts: 1712345678900 }
     *
     *  Esta función lo lee tras cargarDatos() (que ya pobló clienteMap)
     *  y pre-selecciona automáticamente el cliente en el datalist.
     *  Limita a 5 minutos para evitar selecciones fantasma.
     */
    function consumirClienteRecienRegistrado() {
      try {
        const raw = localStorage.getItem('clienteRecienRegistrado');
        if (!raw) return;

        const info = JSON.parse(raw);
        const antiguedad = Date.now() - (info.ts || 0);

        // Solo consumir si tiene menos de 5 minutos
        if (antiguedad > 5 * 60 * 1000) {
          localStorage.removeItem('clienteRecienRegistrado');
          return;
        }

        // Construir la etiqueta que usa el datalist
        const etiqueta = `${info.nombre} (ID: ${info.id})`;

        // Llenar el input de búsqueda
        const searchInput = document.getElementById('clientSearch');
        const hiddenId    = document.getElementById('clientIdHidden');

        if (searchInput && hiddenId) {
          searchInput.value = etiqueta;
          // Resolver el id desde el mapa (o usar el id directo si el mapa ya lo tiene)
          hiddenId.value = clienteMap[etiqueta] !== undefined
            ? clienteMap[etiqueta]
            : info.id;

          console.log('[RegisterSale] Cliente pre-seleccionado desde localStorage:', etiqueta);

          // Limpiar localStorage para no volver a seleccionarlo
          localStorage.removeItem('clienteRecienRegistrado');

          // Limpiar error visual si había
          searchInput.classList.remove('field-error');
          const clientErr = document.getElementById('clientError');
          if (clientErr) clientErr.classList.remove('visible');
        }
      } catch (err) {
        console.warn('[consumirClienteRecienRegistrado] Error:', err.message);
      }
    }

    // Escuchar foco de ventana → re-verificar si llegó un cliente nuevo
    window.addEventListener('focus', () => {
      const raw = localStorage.getItem('clienteRecienRegistrado');
      if (raw) consumirClienteRecienRegistrado();
    });

    init();
  </script>

  <script src="sidebar.js"></script>
</body>

</html>