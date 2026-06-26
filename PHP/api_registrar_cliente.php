<?php
/**
 * api_registrar_cliente.php — Registrar nuevo cliente en gf_piedras_web2
 * =========================================================================
 * Método:  POST  Content-Type: application/json
 * Sesión:  Requiere $_SESSION['id_trabajador'] (empleado autenticado)
 *
 * Payload esperado:
 *   {
 *     "nombre":             "Juan Pérez",          ← OBLIGATORIO
 *     "correo_electronico": "j@example.com",       ← OBLIGATORIO (UNIQUE en BD)
 *     "rfc":                "PEGJ850101ABC",        ← opcional (UNIQUE en BD)
 *     "curp":               "PEGJ850101HDFRRN09",  ← opcional (UNIQUE en BD)
 *     "regimen":            "Persona Física",       ← opcional
 *     "telefono":           "5551234567",           ← opcional
 *     "calle_num":          "Av. Reforma 123",      ← opcional
 *     "colonia":            "Centro",               ← opcional
 *     "ciudad":             "CDMX",                 ← opcional
 *     "estado":             "Mexico",               ← opcional
 *     "cp":                 "06600"                 ← opcional
 *   }
 *
 * Respuesta éxito:
 *   { "status": "success", "message": "Cliente registrado.", "id": 6022,
 *     "nombre": "Juan Pérez" }
 *
 * Respuesta error (siempre JSON válido — Punto 1):
 *   { "status": "error", "message": "Mensaje descriptivo." }
 *
 * Hardening (Puntos 1 y 4):
 *   - ob_start() al inicio → ob_clean() antes de todo echo
 *   - display_errors = OFF → nunca HTML en la respuesta
 *   - try/catch PDOException + Throwable → siempre JSON
 *   - Sentencias preparadas PDO en todas las consultas
 *   - Campos UNIQUE (correo, rfc, curp) → mensaje específico al usuario
 *   - Sesión de trabajador independiente del cliente (Punto 5)
 */

ob_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

session_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

/* ── Helper: respuesta JSON limpia ─────────────────────────────────────── */
function jsonOut(array $data, int $code = 200): void
{
    ob_clean();
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

/* ── Log interno ────────────────────────────────────────────────────────── */
function logCliente(string $msg): void
{
    $dir = __DIR__ . '/../logs/';
    if (!is_dir($dir))
        @mkdir($dir, 0755, true);
    @file_put_contents(
        $dir . 'api_clientes_errors.log',
        '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL,
        FILE_APPEND
    );
}

/* ── Preflight CORS ─────────────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_clean();
    exit();
}

/* ── Solo POST ──────────────────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonOut(['status' => 'error', 'message' => 'Método no permitido.'], 405);
}

/* ── Verificar sesión activa de trabajador (Punto 5) ────────────────────
 * El registro de gf_clientes es independiente de la venta, pero ambos
 * comparten la misma sesión $_SESSION['id_trabajador'].
 * Un cliente no puede registrar gf_clientes — solo un empleado autenticado.
 */
if (empty($_SESSION['id_trabajador'])) {
    jsonOut([
        'status' => 'error',
        'message' => 'Sesión expirada. Por favor inicia sesión de nuevo.',
        'redirigir' => '../Home/UnifiedLogin.php',
    ], 401);
}

/* ── Leer payload JSON ──────────────────────────────────────────────────── */
$raw = file_get_contents('php://input');
$body = json_decode($raw, true);

if (json_last_error() !== JSON_ERROR_NONE || !is_array($body)) {
    jsonOut(['status' => 'error', 'message' => 'El JSON recibido no es válido.'], 400);
}

/* ── Extraer y sanear campos ────────────────────────────────────────────── */
$nombre = trim($body['nombre'] ?? '');
$email = trim($body['correo_electronico'] ?? '');
$rfc = trim($body['rfc'] ?? '') ?: null;
$curp = trim($body['curp'] ?? '') ?: null;
$regimen = trim($body['regimen'] ?? '') ?: null;
$telefono = trim($body['telefono'] ?? '') ?: null;
$calle_num = trim($body['calle_num'] ?? '') ?: null;
$colonia = trim($body['colonia'] ?? '') ?: null;
$ciudad = trim($body['ciudad'] ?? '') ?: null;
$estado = trim($body['estado'] ?? '') ?: null;
$cp = trim($body['cp'] ?? '') ?: null;

/* ── Validación de campos mínimos (Punto 4) ─────────────────────────────
 * Mensaje específico: "Por favor ingresa los datos mínimos requeridos."
 */
$faltantes = [];
if ($nombre === '')
    $faltantes[] = 'Nombre completo';
if ($email === '')
    $faltantes[] = 'Correo electrónico';

if (!empty($faltantes)) {
    jsonOut([
        'status' => 'error',
        'message' => 'Por favor ingresa los datos mínimos requeridos.',
        'faltantes' => $faltantes,
    ], 400);
}

/* Validar formato de email básico */
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonOut([
        'status' => 'error',
        'message' => 'El correo electrónico no tiene un formato válido.',
    ], 400);
}

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
 *  INSERT en tabla gf_clientes (PDO prepared statement)
 *
 *  Columnas de gf_clientes (verificado en contexto_bd.txt):
 *    id (AUTO_INCREMENT), nombre*, rfc (UNIQUE), curp (UNIQUE),
 *    correo_electronico* (UNIQUE), regimen, calle_num, colonia,
 *    ciudad, estado, cp, telefono, constancia_fiscal, password
 *
 *  * = campo obligatorio
 *  No insertamos constancia_fiscal ni password (NULL por defecto).
 * ══════════════════════════════════════════════════════════════════════════ */
try {

    $pdo = crearPDO();

    $sql = "INSERT INTO gf_clientes
                (nombre, rfc, curp, correo_electronico, regimen,
                 calle_num, colonia, ciudad, estado, cp, telefono)
            VALUES
                (:nombre, :rfc, :curp, :correo_electronico, :regimen,
                 :calle_num, :colonia, :ciudad, :estado, :cp, :telefono)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nombre' => $nombre,
        ':rfc' => $rfc,
        ':curp' => $curp,
        ':correo_electronico' => $email,
        ':regimen' => $regimen,
        ':calle_num' => $calle_num,
        ':colonia' => $colonia,
        ':ciudad' => $ciudad,
        ':estado' => $estado,
        ':cp' => $cp,
        ':telefono' => $telefono,
    ]);

    $nuevoId = (int) $pdo->lastInsertId();

    jsonOut([
        'status' => 'success',
        'message' => "Cliente «{$nombre}» registrado correctamente.",
        'id' => $nuevoId,
        'nombre' => $nombre,
    ]);

} catch (PDOException $e) {

    logCliente("PDOException: " . $e->getMessage());

    /* ── Traducir errores de integridad (UNIQUE / FK) ──────────────────── */
    $msg = $e->getMessage();
    $code = (int) $e->getCode();

    if ($code === 23000 || str_contains($msg, '1062')) {
        // Duplicate entry — identificar qué campo
        if (str_contains($msg, 'correo_electronico')) {
            $friendly = "El correo electrónico «{$email}» ya está registrado en el sistema.";
        } elseif (str_contains($msg, 'rfc')) {
            $friendly = "El RFC «{$rfc}» ya está registrado en el sistema.";
        } elseif (str_contains($msg, 'curp')) {
            $friendly = "La CURP «{$curp}» ya está registrada en el sistema.";
        } else {
            $friendly = "Ya existe un cliente con uno de los datos únicos ingresados (correo, RFC o CURP).";
        }
        jsonOut(['status' => 'error', 'message' => $friendly], 409);
    }

    jsonOut([
        'status' => 'error',
        'message' => 'Error de base de datos al registrar el cliente: ' . $e->getMessage(),
    ], 500);

} catch (Throwable $e) {
    logCliente("Error general: " . $e->getMessage());
    jsonOut([
        'status' => 'error',
        'message' => 'Error interno del servidor al registrar el cliente.',
    ], 500);
}
