<?php
/**
 * cliente_inicio.php — Página puente PHP → JS
 * ============================================
 * Lee la sesión PHP del cliente y emite un script que:
 *  1. Guarda id_cliente en localStorage (para que las páginas SPA funcionen)
 *  2. Redirige inmediatamente al portal del cliente
 *
 * Esta página no es visible para el usuario (redirect transparente de <1s).
 */

session_start();

// Si no hay sesión o no es cliente, mandar al login
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'cliente') {
    header('Location: ../Home/UnifiedLogin.php?error=' . urlencode('Acceso no válido.'));
    exit();
}

$idCliente = (int) ($_SESSION['id_cliente'] ?? $_SESSION['id_usuario']);
$nombre    = htmlspecialchars($_SESSION['nombre']  ?? '', ENT_QUOTES, 'UTF-8');
$email     = htmlspecialchars($_SESSION['email']   ?? '', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciando sesión…</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .msg {
            text-align: center;
            color: #74cb3c;
        }
        .spinner {
            width: 40px; height: 40px;
            border: 4px solid #e5e7eb;
            border-top-color: #74cb3c;
            border-radius: 50%;
            animation: spin .7s linear infinite;
            margin: 0 auto 16px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        p { color: #6b7280; font-size: .9rem; margin-top: 8px; }
    </style>
</head>
<body>
    <div class="msg">
        <div class="spinner"></div>
        <strong>Bienvenido, <?= $nombre ?>…</strong>
        <p>Redirigiendo al portal cliente…</p>
    </div>
    <script>
        // Guardar datos en localStorage para que las páginas SPA del cliente los lean
        try {
            localStorage.setItem('id_cliente', '<?= $idCliente ?>');
            localStorage.setItem('nombre_cliente', <?= json_encode($_SESSION['nombre'] ?? '') ?>);
            localStorage.setItem('email_cliente',  <?= json_encode($_SESSION['email']  ?? '') ?>);
            localStorage.setItem('rol_sesion', 'cliente');
        } catch(e) {
            // Ignorar si localStorage no está disponible
        }
        // Redirigir inmediatamente al portal de cliente
        window.location.replace('../Cliente/Dashboard.php');
    </script>
</body>
</html>
