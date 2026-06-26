<?php
/**
 * api_ventas.php — API REST para el módulo de Ventas
 * Esquema real:
 *   gf_ventas_ticket:         id, id_clientes, id_trabajadores, fecha
 *   gf_ventas_detalle_ticket: id_ventas, id_materiales, cantidad
 *   gf_clientes:              id, nombre (campo único, no apellidos separados)
 *   gf_trabajadores:          id, nombre
 *   gf_materiales:            id, precio_unitario, area
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

// Paginación
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
if ($page < 1) $page = 1;
if ($limit < 1) $limit = 10;
$offset = ($page - 1) * $limit;

// Filtros opcionales
$filtro_desde = isset($_GET['desde']) ? $_GET['desde'] : '';
$filtro_hasta = isset($_GET['hasta']) ? $_GET['hasta'] : '';
$filtro_cliente = isset($_GET['cliente']) ? trim($_GET['cliente']) : '';
$filtro_material = isset($_GET['material']) ? trim($_GET['material']) : '';

$whereClauses = [];
// Replicar el comportamiento de 11.5: ocultar tickets sin detalles
$whereClauses[] = "id IN (SELECT DISTINCT id_ventas FROM gf_ventas_detalle_ticket)";
if ($filtro_desde !== '') {
    $whereClauses[] = "fecha >= '" . mysqli_real_escape_string($link, $filtro_desde . " 00:00:00") . "'";
}
if ($filtro_hasta !== '') {
    $whereClauses[] = "fecha <= '" . mysqli_real_escape_string($link, $filtro_hasta . " 23:59:59") . "'";
}
if ($filtro_cliente !== '') {
    $whereClauses[] = "id_clientes = '" . mysqli_real_escape_string($link, $filtro_cliente) . "'";
}
if ($filtro_material !== '') {
    $whereClauses[] = "id IN (SELECT id_ventas FROM gf_ventas_detalle_ticket WHERE id_materiales = '" . mysqli_real_escape_string($link, $filtro_material) . "')";
}
$where = count($whereClauses) > 0 ? " WHERE " . implode(" AND ", $whereClauses) : "";

$sqlCount = "SELECT COUNT(*) as total FROM gf_ventas_ticket" . $where;
$resCount = mysqli_query($link, $sqlCount);
$total = 0;
if ($resCount && $row = mysqli_fetch_assoc($resCount)) {
    $total = intval($row['total']);
}

// ── Tickets de ventas ─────────────────────────────────────────────────
$resT = mysqli_query($link, "SELECT id, id_clientes, id_trabajadores, DATE_FORMAT(fecha, '%Y-%m-%d') AS fecha FROM gf_ventas_ticket" . $where . " ORDER BY id DESC LIMIT $limit OFFSET $offset");
if (!$resT) {
    http_response_code(500);
    echo json_encode(['error' => mysqli_error($link)]);
    exit();
}
$tickets = [];
while ($r = mysqli_fetch_assoc($resT))
    $tickets[] = $r;

// ── Tickets ALL (Para gráficas y KPIs sin paginación) ─────────────────
$resAll = mysqli_query($link, "SELECT id, DATE_FORMAT(fecha, '%Y-%m-%d') AS fecha FROM gf_ventas_ticket" . $where);
$tickets_all = [];
if ($resAll) {
    while ($r = mysqli_fetch_assoc($resAll)) {
        $tickets_all[] = $r;
    }
}

// ── Detalle de tickets ────────────────────────────────────────────────
$detalles = [];
if (count($tickets_all) > 0) {
    $ids = array_map(function($t) { return $t['id']; }, $tickets_all);
    $idsStr = implode(',', $ids);
    $resD = mysqli_query($link, "SELECT id_ventas, id_materiales, cantidad FROM gf_ventas_detalle_ticket WHERE id_ventas IN ($idsStr)");
    if (!$resD) {
        http_response_code(500);
        echo json_encode(['error' => mysqli_error($link)]);
        exit();
    }
    while ($r = mysqli_fetch_assoc($resD)) {
        $detalles[] = $r;
    }
}

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

// ── Trabajadores: mapa id → nombre ───────────────────────────────────
$resTr = mysqli_query($link, "SELECT id, nombre FROM gf_trabajadores");
if (!$resTr) {
    http_response_code(500);
    echo json_encode(['error' => mysqli_error($link)]);
    exit();
}
$gf_trabajadores = [];
while ($r = mysqli_fetch_assoc($resTr))
    $gf_trabajadores[$r['id']] = $r['nombre'];

// ── Materiales: precios y área ────────────────────────────────────────
$resM = mysqli_query($link, "SELECT id, nombre, precio_unitario, area FROM gf_materiales");
if (!$resM) {
    http_response_code(500);
    echo json_encode(['error' => mysqli_error($link)]);
    exit();
}
$precios = [];
$areaMaterial = [];
$nombreMaterial = [];
while ($r = mysqli_fetch_assoc($resM)) {
    $precios[$r['id']]        = floatval($r['precio_unitario']);
    $areaMaterial[$r['id']]   = $r['area'];
    $nombreMaterial[$r['id']] = $r['nombre'];
}

mysqli_close($link);

echo json_encode([
    'data' => [
        'tickets'        => $tickets,
        'tickets_all'    => $tickets_all,
        'detalles'       => $detalles,
        'clientes'       => $gf_clientes,
        'trabajadores'   => $gf_trabajadores,
        'precios'        => $precios,
        'areaMaterial'   => $areaMaterial,
        'nombreMaterial' => $nombreMaterial
    ],
    'total' => $total,
    'page' => $page,
    'limit' => $limit
]);
?>