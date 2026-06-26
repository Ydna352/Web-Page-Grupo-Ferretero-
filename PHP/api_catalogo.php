<?php
/**
 * 12
 * api_catalogo.php — API pública del catálogo de gf_materiales
 *
 * Ruta:    /PHP/api_catalogo.php
 * Métodos: GET
 *
 * Usa el usuario `cliente` (solo SELECT) para exponer los gf_materiales
 * con su imagen al portal de cliente en tiempo real.
 *
 * Sincronización de rutas:
 *   La columna `imagen` contiene únicamente el nombre del archivo
 *   (ej: "2042.png"). El HTML del cliente construye la URL completa
 *   concatenando: IMG_BASE + imagen
 *   donde IMG_BASE = '../imagenes_materiales/'
 *
 *   Esta carpeta es la MISMA en la que api_materiales.php guarda
 *   los archivos (DIR_IMAGENES), garantizando consistencia.
 *
 * Parámetros GET opcionales:
 *   ?area=Carpinteria   → filtra por área (sentencia preparada)
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/conex.php';
$link = Conectarse();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit();
}

// 1. Obtener parámetros GET
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$area = isset($_GET['area']) ? trim($_GET['area']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sortBy = isset($_GET['sortBy']) ? trim($_GET['sortBy']) : 'nombre';

// 2. Construir condiciones WHERE
$whereClauses = [];
$params = [];
$types = '';

if ($area !== '') {
    $whereClauses[] = "area = ?";
    $params[] = $area;
    $types .= 's';
}

if ($search !== '') {
    $whereClauses[] = "(nombre LIKE ? OR area LIKE ? OR descripcion LIKE ?)";
    $searchWildcard = '%' . $search . '%';
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
    $types .= 'sss';
}

$whereSql = '';
if (!empty($whereClauses)) {
    $whereSql = "WHERE " . implode(' AND ', $whereClauses);
}

// 3. Contar total de productos (para la paginación)
$countSql = "SELECT COUNT(*) as total FROM gf_materiales $whereSql";
$countStmt = mysqli_prepare($link, $countSql);
if ($countStmt) {
    if (!empty($params)) {
        mysqli_stmt_bind_param($countStmt, $types, ...$params);
    }
    mysqli_stmt_execute($countStmt);
    $countResult = mysqli_stmt_get_result($countStmt);
    $total = mysqli_fetch_assoc($countResult)['total'];
    mysqli_stmt_close($countStmt);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al contar productos.']);
    exit();
}

// 4. Determinar orden
$orderSql = "ORDER BY nombre ASC";
if ($sortBy === 'precio') {
    $orderSql = "ORDER BY precio_unitario ASC";
} elseif ($sortBy === 'precio_desc') {
    $orderSql = "ORDER BY precio_unitario DESC";
} elseif ($sortBy === 'stock') {
    $orderSql = "ORDER BY existencias DESC";
}

// 5. Consultar productos paginados
$querySql = "SELECT id, nombre, area, descripcion, precio_unitario, existencias, imagen 
             FROM gf_materiales $whereSql $orderSql LIMIT ? OFFSET ?";

$queryStmt = mysqli_prepare($link, $querySql);
if ($queryStmt) {
    // Añadir limit y offset a los parámetros
    $queryParams = $params;
    $queryTypes = $types . 'ii';
    $queryParams[] = $limit;
    $queryParams[] = $offset;

    mysqli_stmt_bind_param($queryStmt, $queryTypes, ...$queryParams);
    mysqli_stmt_execute($queryStmt);
    $result = mysqli_stmt_get_result($queryStmt);
    mysqli_stmt_close($queryStmt);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno al consultar.']);
    exit();
}

$rows = [];
$dirImg = __DIR__ . '/../imagenes_materiales/';
while ($row = mysqli_fetch_assoc($result)) {
    $row['precio_unitario'] = (float) $row['precio_unitario'];
    $row['existencias'] = (int) $row['existencias'];

    $rutaFisica = $dirImg . ($row['imagen'] ?? '');
    if ($row['imagen'] && !file_exists($rutaFisica)) {
        $lower = strtolower($row['imagen']);
        if ($lower !== $row['imagen'] && file_exists($dirImg . $lower)) {
            $row['imagen'] = $lower;
            $rutaFisica = $dirImg . $lower;
        }
    }
    $row['imagen_ts'] = ($row['imagen'] && file_exists($rutaFisica))
        ? filemtime($rutaFisica)
        : time();

    $rows[] = $row;
}

echo json_encode([
    'productos' => $rows,
    'total' => $total,
    'pagina_actual' => $page
], JSON_UNESCAPED_UNICODE);

mysqli_close($link);
exit();