<?php
/**
 * api_trabajadores.php — API REST para el módulo de Trabajadores
 * Esquema real:
 *   gf_trabajadores: id, nombre, puesto (varchar FK), telefono, correo_electronico
 *   gf_puestos:      nombre_puesto (PK), salario_mensual
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
    $accion = isset($_GET['accion']) ? $_GET['accion'] : 'lista';

    if ($accion === 'gf_puestos') {
        $sql = "SELECT nombre_puesto, salario_mensual FROM gf_puestos ORDER BY nombre_puesto";
        $result = mysqli_query($link, $sql);
        if (!$result) {
            http_response_code(500);
            echo json_encode(['error' => mysqli_error($link)]);
            exit();
        }
        $rows = [];
        while ($row = mysqli_fetch_assoc($result))
            $rows[] = $row;
        echo json_encode($rows);

    } else {
        // Paginación
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
        if ($page < 1) $page = 1;
        if ($limit < 1) $limit = 10;
        $offset = ($page - 1) * $limit;

        // Filtro opcional por búsqueda
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $where = '';
        if ($search !== '') {
            $s = mysqli_real_escape_string($link, $search);
            $where = " WHERE t.nombre LIKE '%$s%' OR t.puesto LIKE '%$s%'";
        }

        $sqlCount = "SELECT COUNT(*) as total FROM gf_trabajadores t" . $where;
        $resCount = mysqli_query($link, $sqlCount);
        $total = 0;
        if ($resCount && $row = mysqli_fetch_assoc($resCount)) {
            $total = intval($row['total']);
        }

        // Obtener la nómina total sumando los salarios de todos los que cumplen el filtro
        $sqlStats = "SELECT SUM(p.salario_mensual) as nomina_total FROM gf_trabajadores t JOIN gf_puestos p ON t.puesto = p.nombre_puesto" . $where;
        $resStats = mysqli_query($link, $sqlStats);
        $nomina_total = 0;
        if ($resStats && $row = mysqli_fetch_assoc($resStats)) {
            $nomina_total = floatval($row['nomina_total']);
        }

        // puesto es un varchar que también actúa como FK (nombre_puesto)
        $sql = "SELECT t.id, t.nombre,
                       t.puesto,
                       p.salario_mensual AS salario,
                       t.telefono,
                       t.correo_electronico
                FROM gf_trabajadores t
                JOIN gf_puestos p ON t.puesto = p.nombre_puesto"
                . $where .
                " ORDER BY t.id LIMIT $limit OFFSET $offset";
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
            'nomina_total' => $nomina_total,
            'page' => $page,
            'limit' => $limit
        ]);
    }

    mysqli_close($link);
    exit();
}

// ── POST ─────────────────────────────────────────────────────────────
if ($metodo === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $accion = isset($body['accion']) ? $body['accion'] : '';

    if ($accion === 'insertar') {
        $nombre = mysqli_real_escape_string($link, trim($body['nombre'] ?? ''));
        $puesto = mysqli_real_escape_string($link, trim($body['puesto'] ?? ''));
        $tel = mysqli_real_escape_string($link, trim($body['telefono'] ?? ''));
        $correo = mysqli_real_escape_string($link, trim($body['correo_electronico'] ?? ''));

        if (!$nombre || !$puesto) {
            http_response_code(400);
            echo json_encode(['error' => 'Nombre y puesto son requeridos']);
            exit();
        }

        // Verificar que el puesto existe
        $resPuesto = mysqli_query($link, "SELECT nombre_puesto FROM gf_puestos WHERE nombre_puesto='$puesto'");
        if (!$resPuesto || mysqli_num_rows($resPuesto) === 0) {
            http_response_code(400);
            echo json_encode(['error' => "Puesto no encontrado: $puesto"]);
            exit();
        }

        $sql = "INSERT INTO gf_trabajadores (nombre, puesto, telefono, correo_electronico)
                VALUES ('$nombre','$puesto','$tel','$correo')";
        if (mysqli_query($link, $sql)) {
            echo json_encode(['success' => true, 'id' => mysqli_insert_id($link)]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => mysqli_error($link)]);
        }

    } elseif ($accion === 'actualizar') {
        $id = intval($body['id'] ?? 0);
        $nombre = mysqli_real_escape_string($link, trim($body['nombre'] ?? ''));
        $puesto = mysqli_real_escape_string($link, trim($body['puesto'] ?? ''));
        $tel = mysqli_real_escape_string($link, trim($body['telefono'] ?? ''));
        $correo = mysqli_real_escape_string($link, trim($body['correo_electronico'] ?? ''));

        if ($id <= 0 || !$nombre || !$puesto) {
            http_response_code(400);
            echo json_encode(['error' => 'ID, nombre y puesto requeridos']);
            exit();
        }

        $sql = "UPDATE gf_trabajadores SET nombre='$nombre', puesto='$puesto', telefono='$tel', correo_electronico='$correo' WHERE id=$id";
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
        $sql = "DELETE FROM gf_trabajadores WHERE id=$id";
        if (mysqli_query($link, $sql)) {
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