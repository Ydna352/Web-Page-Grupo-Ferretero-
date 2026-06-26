<?php
// ELIMINAR esta línea:
// require_once '../PHP/auth.php'; auth_require_rol('cliente');

// REEMPLAZAR por esto (solo iniciar sesión sin requerir rol):
if (session_status() === PHP_SESSION_NONE) session_start();

// Si ya tiene sesión activa de cliente, redirigir al inicio
if (!empty($_SESSION['rol']) && $_SESSION['rol'] === 'cliente') {
    header('Location: ../PHP/cliente_inicio.php');
    exit();
} 

$bloqueado = isset($_COOKIE['bloqueo_login']);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro – Portal Cliente</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --green:        #74cb3c;
            --green-dark:   #5aaa2a;
            --green-light:  #eaf5dd;
            --green-ring:   rgba(116,203,60,.18);
            --blue:         #3a7fd5;
            --red:          #dc2626;
            --red-bg:       #fef2f2;
            --red-border:   #fecaca;
            --gray-50:      #f7f8fa;
            --gray-100:     #eef0f3;
            --gray-200:     #dde1e8;
            --gray-400:     #9099a8;
            --gray-600:     #505a6a;
            --gray-800:     #1e2533;
            --radius:       14px;
            --radius-sm:    8px;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--gray-50);
            background-image:
                radial-gradient(ellipse 80% 50% at 20% -10%, rgba(116,203,60,.08) 0%, transparent 60%),
                radial-gradient(ellipse 60% 40% at 80% 110%, rgba(58,127,213,.06) 0%, transparent 60%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }

        .wrapper {
            width: 100%;
            max-width: 420px;
        }

        /* ── Back link ─────────────────────────────────── */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: var(--gray-400);
            font-size: .85rem;
            font-weight: 500;
            text-decoration: none;
            margin-bottom: 24px;
            padding: 5px 0;
            transition: color .18s;
        }
        .back-link:hover { color: var(--green-dark); }
        .back-link svg { width: 15px; height: 15px; }

        /* ── Card ──────────────────────────────────────── */
        .card {
            background: #fff;
            border-radius: var(--radius);
            border: 1.5px solid var(--gray-200);
            box-shadow: 0 2px 12px rgba(0,0,0,.06), 0 1px 3px rgba(0,0,0,.04);
            overflow: hidden;
            animation: slideUp .45s cubic-bezier(.22,.68,0,1.1) both;
        }

        .card-top {
            background: linear-gradient(160deg, var(--green-light) 0%, #f0f8e7 100%);
            padding: 32px 32px 24px;
            text-align: center;
            border-bottom: 1px solid #daefc8;
            position: relative;
        }

        .steps {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            margin-bottom: 28px;
        }
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }
        .step-dot {
            width: 28px; height: 28px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: .75rem; font-weight: 600;
            transition: all .3s;
        }
        .step.active .step-dot {
            background: var(--green);
            color: #fff;
            box-shadow: 0 0 0 4px var(--green-ring);
        }
        .step.inactive .step-dot {
            background: var(--gray-100);
            color: var(--gray-400);
            border: 1.5px solid var(--gray-200);
        }
        .step-label {
            font-size: .7rem;
            font-weight: 600;
            letter-spacing: .03em;
            text-transform: uppercase;
        }
        .step.active .step-label { color: var(--green-dark); }
        .step.inactive .step-label { color: var(--gray-400); }
        .step-line {
            width: 48px; height: 1.5px;
            background: var(--gray-200);
            margin: 0 4px;
            margin-bottom: 24px;
        }

        .avatar {
            width: 56px; height: 56px;
            border-radius: 50%;
            background: var(--green);
            display: inline-flex; align-items: center; justify-content: center;
            margin-bottom: 14px;
            box-shadow: 0 4px 12px rgba(116,203,60,.3);
        }
        .avatar svg { width: 26px; height: 26px; color: #fff; }

        .card-title {
            font-family: 'DM Serif Display', serif;
            font-size: 1.45rem;
            color: var(--gray-800);
            margin-bottom: 6px;
            line-height: 1.2;
        }
        .card-desc {
            font-size: .82rem;
            color: var(--gray-600);
            line-height: 1.6;
            max-width: 300px;
            margin: 0 auto;
        }

        /* ── Form body ─────────────────────────────────── */
        .card-body { padding: 28px 32px 32px; }

        .field { margin-bottom: 18px; }
        .field label {
            display: block;
            font-size: .8rem;
            font-weight: 600;
            color: var(--gray-600);
            margin-bottom: 7px;
            letter-spacing: .02em;
            text-transform: uppercase;
        }
        .input-wrap { position: relative; }
        .input-wrap svg.input-icon {
            position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
            width: 16px; height: 16px; color: var(--gray-400); pointer-events: none;
        }
        .field input {
            width: 100%;
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-sm);
            padding: 10px 13px 10px 38px;
            font-family: 'DM Sans', sans-serif;
            font-size: .92rem;
            color: var(--gray-800);
            outline: none;
            background: var(--gray-50);
            transition: border-color .18s, box-shadow .18s, background .18s;
        }
        .field input::placeholder { color: var(--gray-400); }
        .field input:focus {
            border-color: var(--green);
            background: #fff;
            box-shadow: 0 0 0 3px var(--green-ring);
        }
        .field input:disabled { opacity: .55; cursor: not-allowed; }

        /* ── Alert ─────────────────────────────────────── */
        .alert {
            display: none;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            border-radius: var(--radius-sm);
            font-size: .83rem;
            margin-bottom: 18px;
            animation: fadeIn .25s ease;
        }
        .alert.visible { display: flex; }
        .alert-error {
            color: var(--red);
            background: var(--red-bg);
            border: 1px solid var(--red-border);
        }
        .alert svg { width: 16px; height: 16px; flex-shrink: 0; margin-top: 1px; }
        .alert-title { font-weight: 600; margin-bottom: 2px; }
        .alert-sub { font-size: .78rem; opacity: .85; }

        /* ── Submit ────────────────────────────────────── */
        .btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: var(--radius-sm);
            background: var(--green);
            color: #fff;
            font-family: 'DM Sans', sans-serif;
            font-size: .95rem;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 16px;
            transition: opacity .18s, transform .15s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn:hover:not(:disabled) { opacity: .9; }
        .btn:active:not(:disabled) { transform: scale(.99); }
        .btn:disabled { opacity: .6; cursor: not-allowed; }

        .spinner {
            display: none;
            width: 16px; height: 16px;
            border: 2px solid rgba(255,255,255,.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }
        .btn.loading .spinner { display: block; }
        .btn.loading .btn-text { display: none; }

        .foot-link {
            text-align: center;
            font-size: .83rem;
            color: var(--gray-400);
        }
        .foot-link a { color: var(--blue); text-decoration: none; font-weight: 500; }
        .foot-link a:hover { text-decoration: underline; }

        /* ── Keyframes ──────────────────────────────────── */
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(22px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-4px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
<div class="wrapper">

    <a href="../Home/UnifiedLogin.php" class="back-link">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
        </svg>
        Volver al inicio de sesión
    </a>

    <div class="card">
        <div class="card-top">
            <!-- Indicador de pasos -->
            <div class="steps">
                <div class="step active">
                    <div class="step-dot">1</div>
                    <span class="step-label">Verificar</span>
                </div>
                <div class="step-line"></div>
                <div class="step inactive">
                    <div class="step-dot">2</div>
                    <span class="step-label">Contraseña</span>
                </div>
            </div>

            <div class="avatar">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                </svg>
            </div>
            <div class="card-title">Registrar cuenta</div>
            <p class="card-desc">Ingresa los datos exactos que proporcionaste al momento de tu compra en tienda</p>
        </div>

        <div class="card-body">
            <!-- Campo nombre -->
            <div class="field">
                <label for="fullName">Nombre completo</label>
                <div class="input-wrap">
                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0"/>
                    </svg>
                    <input type="text" id="fullName" placeholder="Ej. Juan Pérez García" autocomplete="name" required />
                </div>
            </div>

            <!-- Campo correo -->
            <div class="field">
                <label for="email">Correo electrónico</label>
                <div class="input-wrap">
                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                    </svg>
                    <input type="email" id="email" placeholder="juan.perez@correo.com" autocomplete="email" required />
                </div>
            </div>

            <!-- Caja de error -->
            <div class="alert alert-error" id="errorBox" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                </svg>
                <div>
                    <p class="alert-title" id="errorMsg"></p>
                    <p class="alert-sub">Verifica que tus datos coincidan exactamente con los registrados en tienda.</p>
                </div>
            </div>

            <!-- Botón principal -->
            <button class="btn" id="btnSubmit" onclick="handleSubmit()">
                <span class="spinner"></span>
                <span class="btn-text">Continuar</span>
            </button>

            <p class="foot-link">¿Ya tienes cuenta? <a href="../Home/UnifiedLogin.php">Inicia sesión</a></p>
        </div>
    </div>
</div>

<script>
    /**
     * Registro Paso 1 — Verificación de cliente en BD
     * El cliente SOLO puede continuar si su correo + nombre
     * ya fueron registrados previamente por un empleado.
     */

    let loading = false;

    const btn       = document.getElementById('btnSubmit');
    const nameInput = document.getElementById('fullName');
    const emailInput= document.getElementById('email');
    const errorBox  = document.getElementById('errorBox');
    const errorMsg  = document.getElementById('errorMsg');

    function setLoading(on) {
        loading = on;
        btn.disabled = on;
        btn.classList.toggle('loading', on);
        nameInput.disabled = on;
        emailInput.disabled = on;
    }

    function showError(msg) {
        errorMsg.textContent = msg;
        errorBox.classList.add('visible');
    }

    function hideError() {
        errorBox.classList.remove('visible');
    }

    function validate() {
        const name  = nameInput.value.trim();
        const email = emailInput.value.trim();
        if (!name)  { showError('Por favor ingresa tu nombre completo.'); nameInput.focus(); return false; }
        if (!email) { showError('Por favor ingresa tu correo electrónico.'); emailInput.focus(); return false; }
        const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRe.test(email)) { showError('El correo electrónico no tiene un formato válido.'); emailInput.focus(); return false; }
        return true;
    }

    function handleSubmit() {
        if (loading) return;
        hideError();
        if (!validate()) return;

        const name  = nameInput.value.trim();
        const email = emailInput.value.trim();

        setLoading(true);

        fetch('../PHP/api_registro_password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ accion: 'verificar', nombre: name, correo: email })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                if (data.ya_registrado) {
                    showError('Este correo ya se encuentra registrado.');
                    setLoading(false);
                    return;
                }
                // ✅ Cliente encontrado y sin contraseña → guardar y avanzar al paso 2
                sessionStorage.setItem('registrationData', JSON.stringify({
                    fullName : data.nombre,
                    email    : data.email,
                    id       : data.id
                }));
                location.href = '../Home/confirm_email1.html';
            } else {
                showError(data.message || 'No pudimos verificar tus datos.');
                setLoading(false);
            }
        })
        .catch(() => {
            showError('Error de conexión. Verifica que el servidor esté activo.');
            setLoading(false);
        });
    }

    // Enter → enviar
    [nameInput, emailInput].forEach(el => {
        el.addEventListener('keydown', e => { if (e.key === 'Enter') handleSubmit(); });
        el.addEventListener('input',   hideError);
    });
</script>
</body>
</html>