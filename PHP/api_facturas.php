<?php
/**
 * api_facturas.php — API REST para el módulo de Facturas
 * Esquema real:
 *   gf_facturas:              folio, id_ventas, id_clientes, fecha, uso_cfdi
 *   gf_ventas_detalle_ticket: id_ventas, id_materiales, cantidad
 *   gf_clientes:              id, nombre
 *   gf_materiales:            id, precio_unitario
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit();
}

require_once 'conex.php';
$link = Conectarse();

// Autocomplete de clientes
if (isset($_GET['q'])) {
    $q = mysqli_real_escape_string($link, $_GET['q']);
    $resC = mysqli_query($link, "SELECT id, nombre FROM gf_clientes WHERE nombre LIKE '%$q%' LIMIT 10");
    $list = [];
    while($r = mysqli_fetch_assoc($resC)) {
        $list[] = $r;
    }
    echo json_encode($list);
    exit();
}

// Paginación
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
if ($page < 1) $page = 1;
if ($limit < 1) $limit = 10;
$offset = ($page - 1) * $limit;

// Filtros opcionales (por si se ocupan a futuro)
$filtro_desde = isset($_GET['desde']) ? $_GET['desde'] : '';
$filtro_hasta = isset($_GET['hasta']) ? $_GET['hasta'] : '';
$filtro_cliente = isset($_GET['cliente']) ? $_GET['cliente'] : '';

$whereClauses = [];
if ($filtro_desde !== '') {
    $whereClauses[] = "fecha >= '" . mysqli_real_escape_string($link, $filtro_desde . " 00:00:00") . "'";
}
if ($filtro_hasta !== '') {
    $whereClauses[] = "fecha <= '" . mysqli_real_escape_string($link, $filtro_hasta . " 23:59:59") . "'";
}
if ($filtro_cliente !== '') {
    $filtro_cliente_esc = mysqli_real_escape_string($link, $filtro_cliente);
    $whereClauses[] = "(id_clientes IN (SELECT id FROM gf_clientes WHERE nombre LIKE '%" . $filtro_cliente_esc . "%') OR id_clientes = '" . $filtro_cliente_esc . "')";
}

$where = count($whereClauses) > 0 ? " WHERE " . implode(" AND ", $whereClauses) : "";

$sqlCount = "SELECT COUNT(*) as total FROM gf_facturas" . $where;
$resCount = mysqli_query($link, $sqlCount);
$total = 0;
if ($resCount && $row = mysqli_fetch_assoc($resCount)) {
    $total = intval($row['total']);
}

// ── Facturas paginadas ──────────────────────────────────────────────────────────
$resF = mysqli_query($link, "SELECT folio, id_ventas, id_clientes, DATE_FORMAT(fecha, '%Y-%m-%d') AS fecha, uso_cfdi, CAST(pdf AS CHAR) AS pdf FROM gf_facturas" . $where . " ORDER BY folio DESC LIMIT $limit OFFSET $offset");
if (!$resF) {
    http_response_code(500);
    echo json_encode(['error' => mysqli_error($link)]);
    exit();
}
$gf_facturas = [];
$ventaIds = [];
while ($r = mysqli_fetch_assoc($resF)) {
    $gf_facturas[] = $r;
    $ventaIds[] = $r['id_ventas'];
}

// ── Facturas ALL (Para estadísticas sin paginación) ─────────────────────────
$resAll = mysqli_query($link, "SELECT folio, id_ventas, DATE_FORMAT(fecha, '%Y-%m-%d') AS fecha FROM gf_facturas" . $where);
$facturas_all = [];
if ($resAll) {
    while ($r = mysqli_fetch_assoc($resAll)) {
        $facturas_all[] = $r;
    }
}

// ── Detalle de ventas ─────────────────────────────────────────────────
$resD = mysqli_query($link, "SELECT id_ventas, id_materiales, cantidad FROM gf_ventas_detalle_ticket");
if (!$resD) {
    http_response_code(500);
    echo json_encode(['error' => mysqli_error($link)]);
    exit();
}
$detalles = [];
while ($r = mysqli_fetch_assoc($resD))
    $detalles[] = $r;

// ── Clientes: mapa id → nombre ────────────────────────────────────────
$resC = mysqli_query($link, "SELECT id, nombre FROM gf_clientes");
if (!$resC) {
    http_response_code(500);
    echo json_encode(['error' => mysqli_error($link)]);
    exit();
}
$gf_clientes = [];
while ($r = mysqli_fetch_assoc($resC))
    $gf_clientes[$r['id']] = $r['nombre'];

// ── Materiales: precios ───────────────────────────────────────────────
$resM = mysqli_query($link, "SELECT id, precio_unitario FROM gf_materiales");
if (!$resM) {
    http_response_code(500);
    echo json_encode(['error' => mysqli_error($link)]);
    exit();
}
$precios = [];
while ($r = mysqli_fetch_assoc($resM))
    $precios[$r['id']] = floatval($r['precio_unitario']);

mysqli_close($link);

echo json_encode([
    'data' => [
        'facturas'     => $gf_facturas,
        'facturas_all' => $facturas_all,
        'detalles'     => $detalles,
        'clientes'     => $gf_clientes,
        'precios'      => $precios
    ],
    'total' => $total,
    'page' => $page,
    'limit' => $limit
]);
?>