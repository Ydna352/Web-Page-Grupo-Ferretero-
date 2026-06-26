<?php
/**
 * api_registro_password.php — API para registro de gf_clientes desde la web
 * =======================================================================
 * Flujo de negocio:
 *   1. Empleado registra venta → guarda correo del cliente (sin contraseña)
 *   2. Cliente entra a "Registrarse" → verifica que su correo exista en BD
 *   3. Si existe → crea su contraseña con password_hash()
 *   4. Si no existe → error "correo no registrado por ningún empleado"
 *
 * Acciones disponibles (POST con JSON):
 *   - accion: "verificar"       → verifica correo + nombre en tabla gf_clientes
 *   - accion: "crear_password"  → asigna contraseña al cliente existente
 *
 * Seguridad:
 *   - Prepared statements (PDO)
 *   - password_hash() para almacenar
 *   - Validación de inputs (trim, filter_var)
 *   - JSON limpio (ob_start + ob_clean)
 */

ob_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

/* ── Helper: respuesta JSON limpia ─────────────────────────────────────── */
function jsonResp(array $data, int $code = 200): void
{
    ob_clean();
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

/* ── Log interno ────────────────────────────────────────────────────────── */
function logRegistro(string $msg): void
{
    $dir = __DIR__ . '/../logs/';
    if (!is_dir($dir))
        @mkdir($dir, 0755, true);
    @file_put_contents(
        $dir . 'registro_password.log',
        '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL,
        FILE_APPEND
    );
}

/**
 * Normaliza un nombre para comparación flexible:
 * - Convierte a minúsculas
 * - Elimina acentos sin depender de la extensión intl
 * - Elimina espacios extra
 */
function normalizarNombre(string $str): string
{
    $str = mb_strtolower(trim($str), 'UTF-8');

    // Tabla de reemplazo de caracteres acentuados → sin acento
    $from = [
        'á',
        'é',
        'í',
        'ó',
        'ú',
        'à',
        'è',
        'ì',
        'ò',
        'ù',
        'â',
        'ê',
        'î',
        'ô',
        'û',
        'ä',
        'ë',
        'ï',
        'ö',
        'ü',
        'ã',
        'õ',
        'ñ',
        'ç',
        'ý',
        'ÿ'
    ];
    $to = [
        'a',
        'e',
        'i',
        'o',
        'u',
        'a',
        'e',
        'i',
        'o',
        'u',
        'a',
        'e',
        'i',
        'o',
        'u',
        'a',
        'e',
        'i',
        'o',
        'u',
        'a',
        'o',
        'n',
        'c',
        'y',
        'y'
    ];

    $str = str_replace($from, $to, $str);

    // Eliminar espacios múltiples internos
    $str = preg_replace('/\s+/', ' ', $str);

    return $str;
}

/* ── Preflight CORS ─────────────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_clean();
    exit();
}

/* ── Solo POST ──────────────────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResp(['status' => 'error', 'message' => 'Método no permitido.'], 405);
}

/* ── Leer payload JSON ──────────────────────────────────────────────────── */
$raw = file_get_contents('php://input');
$body = json_decode($raw, true);

if (json_last_error() !== JSON_ERROR_NONE || !is_array($body)) {
    jsonResp(['status' => 'error', 'message' => 'El JSON recibido no es válido.'], 400);
}

$accion = trim($body['accion'] ?? '');

/* ── Conexión PDO ───────────────────────────────────────────────────────── */
require_once __DIR__ . '/config.php';

function crearPDO(): PDO
{
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $opt = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    return new PDO($dsn, DB_USER, DB_PASS, $opt);
}

/* ══════════════════════════════════════════════════════════════════════════
 *  ACCIÓN: verificar — buscar cliente por nombre + correo en BD
 * ══════════════════════════════════════════════════════════════════════════ */
if ($accion === 'verificar') {
    $nombre = trim($body['nombre'] ?? '');
    $email = trim($body['correo'] ?? '');

    if ($nombre === '' || $email === '') {
        jsonResp(['status' => 'error', 'message' => 'Nombre y correo son obligatorios.'], 400);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResp(['status' => 'error', 'message' => 'El correo no tiene un formato válido.'], 400);
    }

    try {
        $pdo = crearPDO();

        $correo = strtolower(trim($email));
        $stmt = $pdo->prepare("SELECT id, nombre, correo_electronico, password
                               FROM gf_clientes
                               WHERE LOWER(correo_electronico) = ?
                               LIMIT 1");
        $stmt->execute([$correo]);
        $cliente = $stmt->fetch();

        if (!$cliente) {
            if (isset($_COOKIE['bloqueo_login'])) {
                setcookie("bloqueo_login", "", time() - 3600, "/");
            }

            jsonResp([
                'status' => 'success',
                'message' => 'Registro completado correctamente, ya puedes iniciar sesión',
            ]);
        }

        // Comparación de nombres sin depender de extensión intl
        $nombreBD = normalizarNombre($cliente['nombre']);
        $nombreForm = normalizarNombre($nombre);

        if ($nombreBD !== $nombreForm) {
            logRegistro("Nombre no coincide — BD: '$nombreBD' | Form: '$nombreForm'");
            jsonResp([
                'status' => 'error',
                'message' => 'El nombre o correo electrónico proporcionado no coincide con nuestros registros.',
            ], 404);
        }

        // Verificar si ya tiene contraseña
        $yaRegistrado = ($cliente['password'] !== null && $cliente['password'] !== '');

        jsonResp([
            'status' => 'success',
            'message' => 'Cliente encontrado.',
            'id' => (int) $cliente['id'],
            'nombre' => $cliente['nombre'],
            'email' => $cliente['correo_electronico'],
            'ya_registrado' => $yaRegistrado,
        ]);

    } catch (PDOException $e) {
        logRegistro("PDOException en verificar: " . $e->getMessage());
        jsonResp(['status' => 'error', 'message' => 'Error de base de datos.'], 500);
    } catch (Throwable $e) {
        logRegistro("Error en verificar: " . $e->getMessage());
        jsonResp(['status' => 'error', 'message' => 'Error interno del servidor.'], 500);
    }
}

/* ══════════════════════════════════════════════════════════════════════════
 *  ACCIÓN: crear_password — asignar contraseña a cliente existente
 * ══════════════════════════════════════════════════════════════════════════ */
if ($accion === 'crear_password') {
    $email = trim($body['correo'] ?? '');
    $password = $body['password'] ?? '';

    if ($email === '' || $password === '') {
        jsonResp(['status' => 'error', 'message' => 'Correo y contraseña son obligatorios.'], 400);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResp(['status' => 'error', 'message' => 'El correo no tiene un formato válido.'], 400);
    }

    // Validar requisitos de contraseña server-side
    if (strlen($password) < 8) {
        jsonResp(['status' => 'error', 'message' => 'La contraseña debe tener al menos 8 caracteres.'], 400);
    }
    if (!preg_match('/[A-Z]/', $password)) {
        jsonResp(['status' => 'error', 'message' => 'La contraseña debe incluir al menos una mayúscula.'], 400);
    }
    if (!preg_match('/[a-z]/', $password)) {
        jsonResp(['status' => 'error', 'message' => 'La contraseña debe incluir al menos una minúscula.'], 400);
    }
    if (!preg_match('/[0-9]/', $password)) {
        jsonResp(['status' => 'error', 'message' => 'La contraseña debe incluir al menos un número.'], 400);
    }
    if (!preg_match('/[!@#$%^&*]/', $password)) {
        jsonResp(['status' => 'error', 'message' => 'La contraseña debe incluir al menos un carácter especial (!@#$%^&*).'], 400);
    }

    try {
        $pdo = crearPDO();

        $correo = strtolower(trim($email));
        $stmt = $pdo->prepare("SELECT id, password FROM gf_clientes WHERE LOWER(correo_electronico) = ? LIMIT 1");
        $stmt->execute([$correo]);
        $cliente = $stmt->fetch();

        if (!$cliente) {
            jsonResp([
                'status' => 'error',
                'message' => 'Este correo no ha sido autorizado por un empleado',
            ], 404);
        }

        if (!empty($cliente['password'])) {
            jsonResp([
                'status' => 'error',
                'message' => 'Este correo ya se encuentra registrado',
            ], 400);
        }

        // Hashear y guardar contraseña
        $passHash = password_hash($password, PASSWORD_DEFAULT);

        $stmtUp = $pdo->prepare("UPDATE gf_clientes SET password = ? WHERE id = ?");
        $stmtUp->execute([$passHash, $cliente['id']]);

        logRegistro("Contraseña creada para cliente ID={$cliente['id']}, email={$email}");

        jsonResp([
            'status' => 'success',
            'message' => 'Registro completado correctamente, ya puedes iniciar sesión',
        ]);

    } catch (PDOException $e) {
        logRegistro("PDOException en crear_password: " . $e->getMessage());
        jsonResp(['status' => 'error', 'message' => 'Error de base de datos.'], 500);
    } catch (Throwable $e) {
        logRegistro("Error en crear_password: " . $e->getMessage());
        jsonResp(['status' => 'error', 'message' => 'Error interno del servidor.'], 500);
    }
}

/* ── Acción no reconocida ───────────────────────────────────────────────── */
jsonResp(['status' => 'error', 'message' => 'Acción no reconocida. Usa "verificar" o "crear_password".'], 400);