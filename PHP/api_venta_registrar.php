<?php
/**
 * api_venta_registrar.php — Registrar una nueva venta (PDO + Transacción)
 * =========================================================================
 * Migrado a PDO (requerimiento técnico del sistema, Punto 5).
 *
 * Método: POST  Content-Type: application/json
 * Payload esperado:
 *   {
 *     "id_clientes": 6001,
 *     "fecha": "2026-04-03",
 *     "items": [
 *       { "id_materiales": 2001, "cantidad": 5 },
 *       { "id_materiales": 2003, "cantidad": 2 }
 *     ]
 *   }
 *
 * Proceso (Transacción SQL atómica):
 *   BEGIN
 *   → Validar sesión trabajador          (HTTP 401 si no hay sesión)
 *   → Validar cliente existe             (PDO prepared)
 *   → FOR EACH ítem: validar stock       (SELECT … FOR UPDATE)
 *   → INSERT gf_ventas_ticket               (encabezado)
 *   → INSERT gf_ventas_detalle_ticket × N   (detalle por ítem)
 *   → UPDATE gf_materiales.existencias -=   (descontar stock)
 *   COMMIT / ROLLBACK
 *
 * Respuesta éxito:
 *   { "success": true, "id_venta": 3091, "message": "Venta #3091 registrada." }
 *
 * Respuesta error (siempre JSON válido):
 *   { "error": "Mensaje descriptivo del problema" }
 *
 * Hardening aplicado (Punto 1):
 *   - ob_start() al inicio → ob_clean() antes de todo echo
 *   - try/catch Throwable → SIEMPRE retorna JSON
 *   - display_errors = 0, log_errors = 1
 *   - Content-Type forzado antes de cualquier salida
 *   - id_trabajadores NO viene del POST → de $_SESSION (Punto 2/4)
 *   - Todas las consultas con sentencias preparadas PDO (Punto 5)
 */

ob_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

session_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

/* ── Helper: salida JSON limpia ─────────────────────────────────────────── */
function jsonOut(array $data, int $httpCode = 200): void
{
    ob_clean();
    http_response_code($httpCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

/* ── Log interno ────────────────────────────────────────────────────────── */
function logVenta(string $msg): void
{
    $dir = __DIR__ . '/../logs/';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    @file_put_contents($dir . 'api_ventas_errors.log',
        '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL,
        FILE_APPEND
    );
}

/* ── Preflight CORS ─────────────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { ob_clean(); exit(); }

/* ── Solo POST ──────────────────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonOut(['error' => 'Método no permitido.'], 405);
}

/* ── Validar sesión del trabajador (Punto 4) ────────────────────────────
 * El id_trabajadores se extrae de $_SESSION['id_trabajador'], que se fija
 * en login.php al autenticar al empleado. No es manipulable por el cliente.
 * Si la sesión expiró → HTTP 401 con mensaje de re-logueo.
 */
if (empty($_SESSION['id_trabajador'])) {
    jsonOut([
        'error'  => 'Sesión expirada o no autenticado. Por favor vuelve a iniciar sesión.',
        'redirigir' => '../Home/UnifiedLogin.php',
    ], 401);
}
$id_trabajadores = (int) $_SESSION['id_trabajador'];

/* ── Leer payload JSON ──────────────────────────────────────────────────── */
$raw  = file_get_contents('php://input');
$body = json_decode($raw, true);

if (json_last_error() !== JSON_ERROR_NONE || !is_array($body)) {
    jsonOut(['error' => 'El JSON recibido no es válido.'], 400);
}

$id_clientes = (int)   ($body['id_clientes'] ?? 0);
$fecha       = trim(    $body['fecha']        ?? '');
$items       = is_array($body['items'] ?? null) ? $body['items'] : [];

/* ── Validaciones de campos (Punto 1 — "datos mínimos requeridos") ──────── */
$errores = [];

if ($id_clientes <= 0) {
    $errores[] = 'Debes seleccionar un cliente válido.';
}
if (!$fecha || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    $errores[] = 'La fecha no tiene un formato válido (YYYY-MM-DD).';
}
if (empty($items)) {
    $errores[] = 'Debes agregar al menos un material a la venta.';
}

if (!empty($errores)) {
    jsonOut([
        'error'   => 'Por favor ingresa los datos mínimos requeridos.',
        'detalle' => $errores,
    ], 400);
}

/* ── Validar estructura de ítems ────────────────────────────────────────── */
foreach ($items as $i => $item) {
    if ((int)($item['id_materiales'] ?? 0) <= 0 || (int)($item['cantidad'] ?? 0) <= 0) {
        jsonOut(['error' => sprintf('Ítem #%d tiene id o cantidad inválidos.', $i + 1)], 400);
    }
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
 *  TRANSACCIÓN SQL
 * ══════════════════════════════════════════════════════════════════════════ */
try {

    $pdo = crearPDO();
    $pdo->beginTransaction();

    /* ── PASO 0: Verificar que el cliente existe ──────────────────────── */
    $stmtCli = $pdo->prepare(
        "SELECT id FROM gf_clientes WHERE id = ? LIMIT 1"
    );
    $stmtCli->execute([$id_clientes]);
    if (!$stmtCli->fetch()) {
        $pdo->rollBack();
        jsonOut(['error' => "El cliente con ID {$id_clientes} no existe en el sistema."], 400);
    }

    /* ── PASO 1: Validar stock por ítem (FOR UPDATE → lock de fila) ────── */
    $stmtStock = $pdo->prepare(
        "SELECT id, nombre, existencias FROM gf_materiales WHERE id = ? FOR UPDATE"
    );

    foreach ($items as $item) {
        $idMat = (int) $item['id_materiales'];
        $cant  = (int) $item['cantidad'];

        $stmtStock->execute([$idMat]);
        $mat = $stmtStock->fetch();

        if (!$mat) {
            $pdo->rollBack();
            jsonOut(['error' => "El material con ID {$idMat} no existe en el catálogo."], 400);
        }
        if ((int)$mat['existencias'] < $cant) {
            $pdo->rollBack();
            jsonOut([
                'error' => sprintf(
                    'Stock insuficiente para «%s»: solicitado %d, disponible %d.',
                    $mat['nombre'], $cant, $mat['existencias']
                )
            ], 400);
        }
    }

    /* ── PASO 2: INSERT gf_ventas_ticket (encabezado) ───────────────────────
     * NOTA: la columna `fecha` en gf_ventas_ticket es TIMESTAMP y tiene
     * DEFAULT CURRENT_TIMESTAMP. Aun así pasamos la fecha del formulario
     * para que el ticket refleje la fecha de venta seleccionada por el empleado.
     */
    $stmtTicket = $pdo->prepare(
        "INSERT INTO gf_ventas_ticket (id_clientes, id_trabajadores, fecha)
         VALUES (?, ?, ?)"
    );
    $stmtTicket->execute([$id_clientes, $id_trabajadores, $fecha]);
    $id_venta = (int) $pdo->lastInsertId();

    /* ── PASO 3: INSERT gf_ventas_detalle_ticket por cada ítem ─────────────── */
    $stmtDet = $pdo->prepare(
        "INSERT INTO gf_ventas_detalle_ticket (id_ventas, id_materiales, cantidad)
         VALUES (?, ?, ?)"
    );
    foreach ($items as $item) {
        $idMat = (int) $item['id_materiales'];
        $cant  = (int) $item['cantidad'];
        $stmtDet->execute([$id_venta, $idMat, $cant]);
    }

    /* ── PASO 4: UPDATE existencias (REGLA CRÍTICA) ──────────────────────
     * Se resta la cantidad vendida de gf_materiales.existencias.
     * El stock ya fue validado con FOR UPDATE en el PASO 1,
     * por lo que no puede haber race conditions dentro de esta transacción.
     */
    $stmtUpd = $pdo->prepare(
        "UPDATE gf_materiales SET existencias = existencias - ? WHERE id = ?"
    );
    foreach ($items as $item) {
        $stmtUpd->execute([(int)$item['cantidad'], (int)$item['id_materiales']]);
    }

    /* ── COMMIT ──────────────────────────────────────────────────────────── */
    $pdo->commit();

    jsonOut([
        'success'  => true,
        'id_venta' => $id_venta,
        'message'  => "Venta #{$id_venta} registrada correctamente.",
    ]);

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    logVenta("PDOException ROLLBACK: " . $e->getMessage());
    jsonOut(['error' => 'Error de base de datos al registrar la venta: ' . $e->getMessage()], 500);

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    logVenta("Error general ROLLBACK: " . $e->getMessage());
    jsonOut(['error' => 'Error interno del servidor.'], 500);
}
