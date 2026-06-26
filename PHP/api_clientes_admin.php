<?php
/**
 * api_clientes_admin.php — API REST para el módulo de Clientes (Administrador)
 */

ob_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/conex.php';
$link = Conectarse();

$metodo = $_SERVER['REQUEST_METHOD'];

// ── GET ──────────────────────────────────────────────────────────────
if ($metodo === 'GET') {
    $accion = isset($_GET['accion']) ? trim($_GET['accion']) : 'lista';

    if ($accion === 'total_clientes') {
        $sqlCount = "SELECT COUNT(*) as total FROM gf_clientes";
        $resCount = mysqli_query($link, $sqlCount);
        $total = 0;
        if ($resCount && $row = mysqli_fetch_assoc($resCount)) {
            $total = intval($row['total']);
        }
        echo json_encode(['total' => $total]);
        mysqli_close($link);
        exit();
    }

    if ($accion === 'lista') {
        // Paginación
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
        if ($page < 1) $page = 1;
        if ($limit < 1) $limit = 10;
        $offset = ($page - 1) * $limit;

        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $condiciones = [];
        $params = [];
        $types = '';

        if ($search !== '') {
            $condiciones[] = "nombre LIKE ?";
            $params[] = '%' . $search . '%';
            $types .= 's';
        }

        $where = '';
        if (count($condiciones) > 0) {
            $where = " WHERE " . implode(' AND ', $condiciones);
        }

        // Total
        $sqlCount = "SELECT COUNT(*) as total FROM gf_clientes" . $where;
        if (count($params) > 0) {
            $stmtCount = mysqli_prepare($link, $sqlCount);
            mysqli_stmt_bind_param($stmtCount, $types, ...$params);
            mysqli_stmt_execute($stmtCount);
            $resCount = mysqli_stmt_get_result($stmtCount);
        } else {
            $resCount = mysqli_query($link, $sqlCount);
        }
        
        $total = 0;
        if ($resCount && $row = mysqli_fetch_assoc($resCount)) {
            $total = intval($row['total']);
        }

        // Seleccionamos los campos requeridos, constancia_fiscal devuelve booleano para indicar si existe
        $sql = "SELECT id, nombre, correo_electronico, telefono, 
                       IF(constancia_fiscal IS NOT NULL AND constancia_fiscal != '', 1, 0) as tiene_constancia
                FROM gf_clientes" . $where . "
                ORDER BY id LIMIT ? OFFSET ?";
             
        $stmt = mysqli_prepare($link, $sql);
        if (!$stmt) {
            http_response_code(500);
            ob_clean();
            echo json_encode(['error' => 'Error interno al consultar gf_clientes.']);
            exit();
        }
        
        if (count($params) > 0) {
            $paramsLimit = $params;
            $paramsLimit[] = $limit;
            $paramsLimit[] = $offset;
            mysqli_stmt_bind_param($stmt, $types . 'ii', ...$paramsLimit);
        } else {
            mysqli_stmt_bind_param($stmt, 'ii', $limit, $offset);
        }
        
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);

        echo json_encode([
            'data' => $rows,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ]);
        mysqli_close($link);
        exit();
    }
    
    // Obtener información de un PDF específico (Factura) - Read-only logic from dompdf logic if needed
    if ($accion === 'ver_pdf') {
        $folio = isset($_GET['folio']) ? intval($_GET['folio']) : 0;
        if ($folio <= 0) {
            http_response_code(400);
            echo "Folio inválido";
            exit();
        }
        $sql = "SELECT pdf FROM gf_facturas WHERE folio = ?";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $folio);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($res)) {
            if ($row['pdf']) {
                $filePath = __DIR__ . '/../facturas_pdf/' . trim($row['pdf']);
                if (file_exists($filePath)) {
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
                    readfile($filePath);
                    exit();
                } else {
                    echo "El archivo PDF no existe en el servidor.";
                }
            } else {
                echo "La factura no contiene PDF.";
            }
        } else {
            echo "Factura no encontrada.";
        }
        mysqli_close($link);
        exit();
    }

    if ($accion === 'ver_constancia') {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo "ID inválido";
            exit();
        }
        $sql = "SELECT constancia_fiscal FROM gf_clientes WHERE id = ?";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($res)) {
            if ($row['constancia_fiscal']) {
                $filePath = __DIR__ . '/../PHP/' . trim($row['constancia_fiscal']); 
                // Note: constancia_fiscal is usually relative like '../uploads/constancias/...'
                // Since api_clientes_admin.php is in PHP/, we prepend __DIR__ . '/' to resolve it correctly.
                if (file_exists($filePath)) {
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
                    readfile($filePath);
                    exit();
                } else {
                    echo "El archivo de constancia no existe en el servidor.";
                }
            } else {
                echo "El cliente no tiene constancia fiscal.";
            }
        } else {
            echo "Cliente no encontrado.";
        }
        mysqli_close($link);
        exit();
    }
}

// ── POST ─────────────────────────────────────────────────────────────
if ($metodo === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $accion = isset($body['accion']) ? $body['accion'] : '';

    if ($accion === 'eliminar') {
        $id = intval($body['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID requerido']);
            exit();
        }
        $sql = "DELETE FROM gf_clientes WHERE id=?";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => mysqli_error($link)]);
        }
        mysqli_stmt_close($stmt);

    } elseif ($accion === 'compras_facturas') {
        $id = intval($body['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID requerido']);
            exit();
        }
        
        $resultado = [
            "tickets"    => [],
            "facturas"   => []
        ];

        // 1. Obtener los tickets de compra del cliente (agrupados)
        $sql_tickets = "SELECT t.id as id_ticket, DATE_FORMAT(t.fecha, '%Y-%m-%d %H:%i:%s') AS fecha
                        FROM gf_ventas_ticket t
                        WHERE t.id_clientes = ?
                        ORDER BY t.fecha DESC, t.id DESC";
        $stmt = mysqli_prepare($link, $sql_tickets);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);

        $tickets_temp = [];
        $ticket_ids = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $row['total'] = 0;
            $row['detalles'] = [];
            $tickets_temp[$row['id_ticket']] = $row;
            $ticket_ids[] = $row['id_ticket'];
        }
        mysqli_stmt_close($stmt);

        if (count($ticket_ids) > 0) {
            $in_clause = implode(',', $ticket_ids);
            $sql_detalles = "SELECT dt.id_ventas as id_ticket, m.nombre as material, m.area, 
                                    dt.cantidad, m.precio_unitario as precio,
                                    (dt.cantidad * m.precio_unitario) as subtotal
                             FROM gf_ventas_detalle_ticket dt
                             JOIN gf_materiales m ON dt.id_materiales = m.id
                             WHERE dt.id_ventas IN ($in_clause)";
            $res_det = mysqli_query($link, $sql_detalles);
            while ($det = mysqli_fetch_assoc($res_det)) {
                $id_t = $det['id_ticket'];
                $tickets_temp[$id_t]['detalles'][] = $det;
                $tickets_temp[$id_t]['total'] += $det['subtotal'];
            }
        }

        $resultado["tickets"] = array_values($tickets_temp);

        // 2. Obtener las facturas del cliente
        $sql_facturas = "SELECT folio, id_ventas, 
                                DATE_FORMAT(fecha, '%Y-%m-%d %H:%i:%s') AS fecha,
                                uso_cfdi,
                                IF(pdf IS NOT NULL AND pdf != '', 1, 0) as tiene_pdf
                         FROM gf_facturas
                         WHERE id_clientes = ?
                         ORDER BY fecha DESC";
        $stmt_f = mysqli_prepare($link, $sql_facturas);
        mysqli_stmt_bind_param($stmt_f, 'i', $id);
        mysqli_stmt_execute($stmt_f);
        $res_f = mysqli_stmt_get_result($stmt_f);

        while ($row = mysqli_fetch_assoc($res_f)) {
            $resultado["facturas"][] = $row;
        }
        mysqli_stmt_close($stmt_f);

        echo json_encode($resultado);

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
