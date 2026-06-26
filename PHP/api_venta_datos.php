<?php
/**
 * api_venta_datos.php — Datos de apoyo para el formulario de nueva venta
 * =======================================================================
 * Migrado a PDO (requerimiento técnico del sistema).
 *
 * GET → retorna:
 *   {
 *     gf_clientes:   [ { id, nombre }, … ]          ← SIN LIMIT, todos los registros
 *     gf_materiales: [ { id, nombre, area, precio_unitario, existencias }, … ]
 *     trabajador: { id, nombre }                 ← desde $_SESSION
 *   }
 *
 * Hardening:
 *   - ob_start() + ob_clean() antes de echo → nunca "Unexpected end of JSON"
 *   - try/catch PDO → siempre devuelve JSON, incluso en error de conexión
 *   - Content-Type forzado a application/json
 *   - display_errors OFF + log_errors ON
 *   - Sin LIMIT en gf_clientes → todos los registros disponibles
 */

ob_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

/* ── Función helper: respuesta JSON limpia ──────────────────────────────── */
function jsonOut(array $data, int $httpCode = 200): void
{
    ob_clean();
    http_response_code($httpCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

/* ── Solo GET ────────────────────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { ob_clean(); exit(); }

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonOut(['error' => 'Método no permitido'], 405);
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
 *  Proceso principal dentro de try/catch para garantizar JSON siempre
 * ══════════════════════════════════════════════════════════════════════════ */
try {

    $pdo = crearPDO();

    /* ── Clientes: TODOS los registros, SIN LIMIT ──────────────────────────
     * Solo id y nombre — suficiente para el buscador de sugerencias.
     * Ordenados por nombre para mejorar UX del datalist.
     */
    $stmtC = $pdo->query(
        "SELECT id, nombre FROM gf_clientes ORDER BY nombre"
    );
    $gf_clientes = $stmtC->fetchAll();

    /* ── Materiales con stock > 0 ──────────────────────────────────────────
     * Se incluye solo lo necesario para el carrito de ventas.
     */
    $stmtM = $pdo->query(
        "SELECT id, nombre, area, precio_unitario, existencias
         FROM gf_materiales
         WHERE existencias > 0
         ORDER BY area, nombre"
    );
    $gf_materiales = array_map(function ($row) {
        return [
            'id'              => (int)   $row['id'],
            'nombre'          =>         $row['nombre'],
            'area'            =>         $row['area'],
            'precio_unitario' => (float) $row['precio_unitario'],
            'existencias'     => (int)   $row['existencias'],
        ];
    }, $stmtM->fetchAll());

    /* ── Trabajador desde la sesión ───────────────────────────────────────
     * No se consulta la BD: el id y nombre vienen de $_SESSION (se fijan
     * en login.php al autenticar al empleado).
     */
    $trabajador = [
        'id'     => $_SESSION['id_trabajador'] ?? null,
        'nombre' => $_SESSION['nombre']         ?? null,
    ];

    jsonOut([
        'clientes'   => $gf_clientes,
        'materiales' => $gf_materiales,
        'trabajador' => $trabajador,
    ]);

} catch (PDOException $e) {
    error_log('[api_venta_datos] PDOException: ' . $e->getMessage());
    jsonOut(['error' => 'Error de base de datos. Intenta de nuevo más tarde.'], 500);
} catch (Throwable $e) {
    error_log('[api_venta_datos] Error: ' . $e->getMessage());
    jsonOut(['error' => 'Error interno del servidor.'], 500);
}
