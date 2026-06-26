<?php
/**
 * login.php — Autenticación con roles (admin / empleado / cliente)
 * ================================================================
 * Busca al usuario en gf_trabajadores primero, luego en gf_clientes.
 * Responde con JSON { ok, redirect } para que el JS haga la navegación.
 *
 * Seguridad:
 *   - Prepared statements (mysqli) para prevenir SQL Injection
 *   - password_verify() para validar contraseñas con hash bcrypt
 *   - Migración automática de contraseñas antiguas (texto plano / crypt)
 *   - password_needs_rehash() para mantener hashes actualizados
 */

// IMPORTANTE: destruir sesión previa antes de autenticar

// Esto evita que una sesión de admin hijack logins de empleado/cliente
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_COOKIE['bloqueo_login'])) {
    echo json_encode([
        'ok' => false,
        'error' => 'Sesión finalizada. Debes registrarte nuevamente.'
    ]);
    exit();
}

$_SESSION = [];          // limpiar datos de sesión previa
session_destroy();       // destruir la sesión
session_start();         // iniciar sesión limpia

header('Content-Type: application/json; charset=utf-8');

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit();
}

require_once 'conex.php';

$email    = isset($_POST['email'])    ? trim($_POST['email'])    : '';
$password = isset($_POST['password']) ? $_POST['password']       : '';

// Validación básica
if (!$email || !$password) {
    echo json_encode(['ok' => false, 'error' => 'Ingresa tu correo y contraseña.']);
    exit();
}

// Validar formato de email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['ok' => false, 'error' => 'El formato del correo no es válido.']);
    exit();
}

// TAREA 4: normalizar correo para comparación insensible a mayúsculas
$email = trim(strtolower($email));

// Usar conexión admin para acceder a ambas tablas
$link = Conectarse();

// ── 1. Buscar en gf_trabajadores (LOWER para ignorar mayúsculas — TAREA 4) ───────────────
$sqlT = "SELECT id, nombre, puesto, correo_electronico, telefono, password
         FROM gf_trabajadores
         WHERE LOWER(correo_electronico) = ?
         LIMIT 1";

$stmtT = mysqli_prepare($link, $sqlT);
mysqli_stmt_bind_param($stmtT, 's', $email);
mysqli_stmt_execute($stmtT);
$resT = mysqli_stmt_get_result($stmtT);

if ($resT && mysqli_num_rows($resT) > 0) {
    $user = mysqli_fetch_assoc($resT);
    mysqli_stmt_close($stmtT);

    // Verificar contraseña con migración automática
    $authResult = verificarPasswordConMigracion(
        $link, $password, $user['password'] ?? '',
        'gf_trabajadores', (int) $user['id']
    );

    if ($authResult) {
        // puesto=Administrador → rol admin, resto → empleado
        $rol = (strtolower($user['puesto']) === 'administrador') ? 'admin' : 'empleado';

        $_SESSION['id_usuario']    = (int) $user['id'];
        $_SESSION['id_trabajador'] = (int) $user['id'];  // ← requerido por api_venta_registrar.php
        $_SESSION['nombre']        = $user['nombre'];
        $_SESSION['email']         = $user['correo_electronico'];
        $_SESSION['rol']           = $rol;
        $_SESSION['puesto']        = $user['puesto'];
        $_SESSION['telefono']      = $user['telefono'];

        mysqli_close($link);
        echo json_encode(['ok' => true, 'redirect' => urlPorRol($rol), 'rol' => $rol]);
        exit();
    }
} else {
    mysqli_stmt_close($stmtT);
}

// ── 2. Buscar en gf_clientes (LOWER para ignorar mayúsculas — TAREA 4) ────────────────
$sqlC = "SELECT id, nombre, correo_electronico, password
         FROM gf_clientes
         WHERE LOWER(correo_electronico) = ?
         LIMIT 1";

$stmtC = mysqli_prepare($link, $sqlC);
mysqli_stmt_bind_param($stmtC, 's', $email);
mysqli_stmt_execute($stmtC);
$resC = mysqli_stmt_get_result($stmtC);

if ($resC && mysqli_num_rows($resC) > 0) {
    $user = mysqli_fetch_assoc($resC);
    mysqli_stmt_close($stmtC);

    // Verificar contraseña con migración automática
    $authResult = verificarPasswordConMigracion(
        $link, $password, $user['password'] ?? '',
        'gf_clientes', (int) $user['id']
    );

    if ($authResult) {
        $_SESSION['id_usuario'] = (int) $user['id'];
        $_SESSION['id_cliente'] = (int) $user['id'];
        $_SESSION['nombre']     = $user['nombre'];
        $_SESSION['email']      = $user['correo_electronico'];
        $_SESSION['rol']        = 'cliente';

        mysqli_close($link);
        echo json_encode(['ok' => true, 'redirect' => urlPorRol('cliente'), 'rol' => 'cliente']);
        exit();
    }
} else {
    mysqli_stmt_close($stmtC);
}

mysqli_close($link);

// ── No encontrado ──────────────────────────────────────────────────────────
echo json_encode(['ok' => false, 'error' => 'Correo o contraseña incorrectos. Inténtalo de nuevo.']);
exit();


// ── Funciones auxiliares ───────────────────────────────────────────────────

/**
 * Verifica la contraseña ingresada contra el hash almacenado en BD.
 * Soporta migración automática de contraseñas antiguas:
 *   1. Contraseñas con hash moderno (bcrypt/argon2) → password_verify()
 *   2. Contraseñas en texto plano → comparación directa + migración
 *   3. Contraseñas con crypt() viejo (13 chars) → crypt() + migración
 *
 * @param mysqli $link      Conexión a BD
 * @param string $passInput Contraseña ingresada por el usuario
 * @param string $hashBD    Hash/password almacenado en BD
 * @param string $tabla     'gf_clientes' o 'gf_trabajadores'
 * @param int    $userId    ID del registro
 * @return bool             true si la contraseña es correcta
 */
function verificarPasswordConMigracion($link, $passInput, $hashBD, $tabla, $userId)
{
    // Si no hay password almacenado, no permitir acceso
    if ($hashBD === '' || $hashBD === null) {
        return false;
    }

    // ── Caso 1: Hash moderno (bcrypt / argon2) ─────────────────────────
    // Detectar por prefijo del string — más fiable que password_get_info()
    // entre versiones de PHP (en algunas, algo devuelve null en vez de 0).
    $isModernHash = (bool) preg_match('/^\$2[ayb]\$|\$argon2/', $hashBD);

    if ($isModernHash) {
        if (password_verify($passInput, $hashBD)) {
            if (password_needs_rehash($hashBD, PASSWORD_DEFAULT)) {
                $nuevoHash = password_hash($passInput, PASSWORD_DEFAULT);
                actualizarPassword($link, $tabla, $userId, $nuevoHash);
            }
            return true;
        }
        return false;
    }

    // ── Caso 2: Contraseña antigua (texto plano o crypt viejo) ─────────
    $esValida = false;

    // 2a. Contraseña en texto plano (comparación directa)
    if ($passInput === $hashBD) {
        $esValida = true;
    }
    // 2b. Contraseña con crypt() viejo (DES = 13 chars, MD5 = $1$...)
    elseif (strlen($hashBD) === 13 && crypt($passInput, $hashBD) === $hashBD) {
        $esValida = true;
    }
    // 2c. Contraseña con crypt() MD5 ($1$...)
    elseif (substr($hashBD, 0, 3) === '$1$' && crypt($passInput, $hashBD) === $hashBD) {
        $esValida = true;
    }
    // 2d. SHA2-256 (64 hex), SHA2-512 (128 hex) o SHA1 (40 hex)
    elseif (preg_match('/^[0-9a-f]+$/', $hashBD)) {
        $len = strlen($hashBD);
        if ($len === 64 && hash('sha256', $passInput) === $hashBD) {
            $esValida = true;
        } elseif ($len === 128 && hash('sha512', $passInput) === $hashBD) {
            $esValida = true;
        } elseif ($len === 40 && sha1($passInput) === $hashBD) {
            $esValida = true;
        }
    }

    // Si la contraseña antigua es válida, migrar a password_hash()
    if ($esValida) {
        $nuevoHash = password_hash($passInput, PASSWORD_DEFAULT);
        actualizarPassword($link, $tabla, $userId, $nuevoHash);
        return true;
    }

    return false;
}

/**
 * Actualiza el password en la BD usando prepared statement.
 */
function actualizarPassword($link, $tabla, $userId, $nuevoHash)
{
    // Solo permitir tablas válidas (prevenir inyección en nombre de tabla)
    $tablasPermitidas = ['gf_clientes', 'gf_trabajadores'];
    if (!in_array($tabla, $tablasPermitidas, true)) {
        return;
    }

    $sql = "UPDATE {$tabla} SET password = ? WHERE id = ?";
    $stmt = mysqli_prepare($link, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'si', $nuevoHash, $userId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

function urlPorRol($rol)
{
    switch ($rol) {
        case 'admin':    return '../Administrador/Dashboard.php';
        case 'empleado': return '../Empleado/Dashboard.php';
        case 'cliente':  return '../PHP/cliente_inicio.php';
        default:         return '../Home/UnifiedLogin.php';
    }
}
?>
