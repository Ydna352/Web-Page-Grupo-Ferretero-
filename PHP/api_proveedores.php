<?php
/**
 * api_proveedores.php — API REST para el módulo de Proveedores
 * Esquema real:
 *   gf_proveedores: id, nombre, correo_electronico, telefono, persona_de_contacto
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'conex.php';
$link = Conectarse();
$metodo = $_SERVER['REQUEST_METHOD'];

// ── GET ──────────────────────────────────────────────────────────────
if ($metodo === 'GET') {
    $searchNombre = isset($_GET['search_nombre']) ? trim($_GET['search_nombre']) : '';
    $searchContacto = isset($_GET['search_contacto']) ? trim($_GET['search_contacto']) : '';
    
    // Paginación
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    if ($page < 1) $page = 1;
    if ($limit < 1) $limit = 10;
    $offset = ($page - 1) * $limit;

    $whereClauses = [];
    if ($searchNombre !== '') {
        $searchNombreEscaped = mysqli_real_escape_string($link, $searchNombre);
        $whereClauses[] = "nombre LIKE '%$searchNombreEscaped%'";
    }
    if ($searchContacto !== '') {
        $searchContactoEscaped = mysqli_real_escape_string($link, $searchContacto);
        $whereClauses[] = "persona_de_contacto LIKE '%$searchContactoEscaped%'";
    }

    $where = '';
    if (count($whereClauses) > 0) {
        $where = " WHERE " . implode(' AND ', $whereClauses);
    }

    // Total
    $sqlCount = "SELECT COUNT(*) as total FROM gf_proveedores" . $where;
    $resCount = mysqli_query($link, $sqlCount);
    $total = 0;
    if ($resCount && $row = mysqli_fetch_assoc($resCount)) {
        $total = intval($row['total']);
    }

    $sql = "SELECT id, nombre, correo_electronico, telefono, persona_de_contacto
               FROM gf_proveedores" . $where . " ORDER BY id LIMIT $limit OFFSET $offset";
    $result = mysqli_query($link, $sql);
    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => mysqli_error($link)]);
        exit();
    }
    $rows = [];
    while ($row = mysqli_fetch_assoc($result))
        $rows[] = $row;
        
    echo json_encode([
        'data' => $rows,
        'total' => $total,
        'page' => $page,
        'limit' => $limit
    ]);
    mysqli_close($link);
    exit();
}

// ── POST ─────────────────────────────────────────────────────────────
if ($metodo === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $accion = isset($body['accion']) ? $body['accion'] : '';

    if ($accion === 'insertar') {
        $nombre = mysqli_real_escape_string($link, trim($body['nombre'] ?? ''));
        $correo = mysqli_real_escape_string($link, trim($body['correo_electronico'] ?? ''));
        $telefono = mysqli_real_escape_string($link, trim($body['telefono'] ?? ''));
        $contacto = mysqli_real_escape_string($link, trim($body['persona_de_contacto'] ?? ''));

        if (!$nombre) {
            http_response_code(400);
            echo json_encode(['error' => 'Nombre requerido']);
            exit();
        }

        $sql = "INSERT INTO gf_proveedores (nombre, correo_electronico, telefono, persona_de_contacto)
                VALUES ('$nombre','$correo','$telefono','$contacto')";
        if (mysqli_query($link, $sql)) {
            echo json_encode(['success' => true, 'id' => mysqli_insert_id($link)]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => mysqli_error($link)]);
        }

    } elseif ($accion === 'actualizar') {
        $id = intval($body['id'] ?? 0);
        $nombre = mysqli_real_escape_string($link, trim($body['nombre'] ?? ''));
        $correo = mysqli_real_escape_string($link, trim($body['correo_electronico'] ?? ''));
        $telefono = mysqli_real_escape_string($link, trim($body['telefono'] ?? ''));
        $contacto = mysqli_real_escape_string($link, trim($body['persona_de_contacto'] ?? ''));

        if ($id <= 0 || !$nombre) {
            http_response_code(400);
            echo json_encode(['error' => 'ID y nombre requeridos']);
            exit();
        }

        $sql = "UPDATE gf_proveedores SET nombre='$nombre', correo_electronico='$correo',
                    telefono='$telefono', persona_de_contacto='$contacto'
                WHERE id=$id";
        if (mysqli_query($link, $sql)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => mysqli_error($link)]);
        }

    } elseif ($accion === 'eliminar') {
        $id = intval($body['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID requerido']);
            exit();
        }
        if (mysqli_query($link, "DELETE FROM gf_proveedores WHERE id=$id")) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => mysqli_error($link)]);
        }

    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Acción no reconocida']);
    }
    mysqli_close($link);
    exit();
}

http_response_code(405);
echo json_encode(['error' => 'Método no permitido']);
?>