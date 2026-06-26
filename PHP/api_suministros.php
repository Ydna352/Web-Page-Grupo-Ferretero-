<?php
/**
 * api_suministros.php — API REST para el módulo de Suministros (multi-material tickets)
 *
 * Esquema real:
 *   gf_suministros_ticket:         id, id_proveedores, fecha
 *   gf_suministros_detalle_ticket: id_suministros, id_materiales, cantidad, costo_unitario
 *   gf_proveedores:                id, nombre, correo_electronico, telefono, persona_de_contacto
 *   gf_materiales:                 id, nombre, area, descripcion, precio_unitario, existencias
 *
 * GET  → Devuelve tickets, detalles, gf_proveedores, gf_materiales
 * POST accion=insertar_ticket  → Crea ticket + N líneas de detalle en una transacción
 * POST accion=eliminar_ticket  → Elimina un ticket y todos sus detalles
 * POST accion=actualizar_detalle → Actualiza cantidad y costo de una línea
 * POST accion=eliminar_detalle   → Elimina una línea de detalle del ticket
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

// ================================================================
// GET — Datos completos
// ================================================================
if ($metodo === 'GET') {

    // Paginación
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    if ($page < 1) $page = 1;
    if ($limit < 1) $limit = 10;
    $offset = ($page - 1) * $limit;

    // Filtros
    $filtro_desde = isset($_GET['desde']) ? $_GET['desde'] : '';
    $filtro_hasta = isset($_GET['hasta']) ? $_GET['hasta'] : '';
    $filtro_prov = isset($_GET['proveedor']) ? intval($_GET['proveedor']) : 0;
    $filtro_mat = isset($_GET['material']) ? intval($_GET['material']) : 0;

    $whereClauses = [];
    if ($filtro_desde !== '') {
        $whereClauses[] = "fecha >= '" . mysqli_real_escape_string($link, $filtro_desde . " 00:00:00") . "'";
    }
    if ($filtro_hasta !== '') {
        $whereClauses[] = "fecha <= '" . mysqli_real_escape_string($link, $filtro_hasta . " 23:59:59") . "'";
    }
    if ($filtro_prov > 0) {
        $whereClauses[] = "id_proveedores = $filtro_prov";
    }
    if ($filtro_mat > 0) {
        // Ticket must have this material
        $whereClauses[] = "id IN (SELECT id_suministros FROM gf_suministros_detalle_ticket WHERE id_materiales = $filtro_mat)";
    }
    $where = count($whereClauses) > 0 ? " WHERE " . implode(" AND ", $whereClauses) : "";

    $sqlCount = "SELECT COUNT(*) as total FROM gf_suministros_ticket" . $where;
    $resCount = mysqli_query($link, $sqlCount);
    $total = 0;
    if ($resCount && $row = mysqli_fetch_assoc($resCount)) {
        $total = intval($row['total']);
    }

    // Tickets paginados
    $resT = mysqli_query($link, "SELECT id, id_proveedores, DATE_FORMAT(fecha, '%Y-%m-%d') AS fecha FROM gf_suministros_ticket" . $where . " ORDER BY id ASC LIMIT $limit OFFSET $offset");
    if (!$resT) {
        http_response_code(500);
        echo json_encode(['error' => mysqli_error($link)]);
        exit();
    }
    $tickets = [];
    $ticketIds = [];
    while ($r = mysqli_fetch_assoc($resT)) {
        $tickets[] = $r;
        $ticketIds[] = $r['id'];
    }

    // Detalles (solo de los tickets en la página)
    $detalles = [];
    if (count($ticketIds) > 0) {
        $idsStr = implode(',', $ticketIds);
        $resD = mysqli_query($link, "SELECT id_suministros, id_materiales, cantidad, costo_unitario
                                      FROM gf_suministros_detalle_ticket
                                      WHERE id_suministros IN ($idsStr)
                                      ORDER BY id_suministros ASC");
        if (!$resD) {
            http_response_code(500);
            echo json_encode(['error' => mysqli_error($link)]);
            exit();
        }
        while ($r = mysqli_fetch_assoc($resD)) {
            $detalles[] = $r;
        }
    }

    // Proveedores completos (siempre se ocupan para combos)
    $resP = mysqli_query($link, "SELECT id, nombre, correo_electronico, telefono, persona_de_contacto
                                  FROM gf_proveedores ORDER BY nombre");
    if (!$resP) {
        http_response_code(500);
        echo json_encode(['error' => mysqli_error($link)]);
        exit();
    }
    $gf_proveedores = [];
    while ($r = mysqli_fetch_assoc($resP))
        $gf_proveedores[] = $r;

    // Materiales completos (siempre se ocupan para combos)
    $resM = mysqli_query($link, "SELECT id, nombre, area, descripcion, precio_unitario, existencias
                                  FROM gf_materiales ORDER BY nombre");
    if (!$resM) {
        http_response_code(500);
        echo json_encode(['error' => mysqli_error($link)]);
        exit();
    }
    $gf_materiales = [];
    while ($r = mysqli_fetch_assoc($resM))
        $gf_materiales[] = $r;

    mysqli_close($link);
    echo json_encode([
        'data' => [
            'tickets'     => $tickets,
            'detalles'    => $detalles,
            'proveedores' => $gf_proveedores,
            'materiales'  => $gf_materiales
        ],
        'total' => $total,
        'page' => $page,
        'limit' => $limit
    ]);
    exit();
}

// ================================================================
// POST — Operaciones de escritura
// ================================================================
if ($metodo === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $accion = trim($body['accion'] ?? '');

    // ── INSERTAR TICKET (con múltiples gf_materiales) ────────────────
    if ($accion === 'insertar_ticket') {
        $id_prov = intval($body['id_proveedores'] ?? 0);
        $fecha = mysqli_real_escape_string($link, $body['fecha'] ?? date('d-m-Y'));
        $lineas = isset($body['lineas']) && is_array($body['lineas']) ? $body['lineas'] : [];

        if ($id_prov <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Proveedor requerido']);
            exit();
        }
        if (count($lineas) === 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Debe agregar al menos un material']);
            exit();
        }

        // Validar líneas antes de insertar
        foreach ($lineas as $l) {
            if (intval($l['id_materiales'] ?? 0) <= 0 || intval($l['cantidad'] ?? 0) <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Cada línea debe tener material y cantidad válidos']);
                exit();
            }
        }

        // Transacción
        mysqli_begin_transaction($link);
        try {
            // 1. Insertar cabecera
            $sqlT = "INSERT INTO gf_suministros_ticket (id_proveedores, fecha) VALUES ($id_prov, '$fecha')";
            if (!mysqli_query($link, $sqlT))
                throw new Exception(mysqli_error($link));
            $id_ticket = mysqli_insert_id($link);

            // 2. Insertar cada línea de detalle
            foreach ($lineas as $l) {
                $idMat = intval($l['id_materiales']);
                $cant = intval($l['cantidad']);
                $costo = floatval($l['costo_unitario'] ?? 0);
                $sqlDet = "INSERT INTO gf_suministros_detalle_ticket
                               (id_suministros, id_materiales, cantidad, costo_unitario)
                           VALUES ($id_ticket, $idMat, $cant, $costo)";
                if (!mysqli_query($link, $sqlDet))
                    throw new Exception(mysqli_error($link));

                // Actualizar existencias
                mysqli_query($link, "UPDATE gf_materiales SET existencias = existencias + $cant WHERE id = $idMat");
            }

            mysqli_commit($link);
            echo json_encode(['success' => true, 'id_ticket' => $id_ticket]);

        } catch (Exception $e) {
            mysqli_rollback($link);
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }

        // ── ELIMINAR TICKET (cascada de detalles) ─────────────────────
    } elseif ($accion === 'eliminar_ticket') {
        $id = intval($body['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID requerido']);
            exit();
        }

        mysqli_begin_transaction($link);
        try {
            // Revertir existencias antes de eliminar
            $resLineas = mysqli_query($link, "SELECT id_materiales, cantidad FROM gf_suministros_detalle_ticket WHERE id_suministros=$id");
            if ($resLineas) {
                while ($l = mysqli_fetch_assoc($resLineas)) {
                    mysqli_query($link, "UPDATE gf_materiales SET existencias = existencias - {$l['cantidad']} WHERE id = {$l['id_materiales']}");
                }
            }
            if (!mysqli_query($link, "DELETE FROM gf_suministros_detalle_ticket WHERE id_suministros=$id"))
                throw new Exception(mysqli_error($link));
            if (!mysqli_query($link, "DELETE FROM gf_suministros_ticket WHERE id=$id"))
                throw new Exception(mysqli_error($link));

            mysqli_commit($link);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            mysqli_rollback($link);
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }

        // ── ACTUALIZAR LÍNEA DE DETALLE ───────────────────────────────
    } elseif ($accion === 'actualizar_detalle') {
        $id_suministro = intval($body['id_suministros'] ?? 0);
        $id_material = intval($body['id_materiales'] ?? 0);
        $cantidad_new = intval($body['cantidad'] ?? 0);
        $costo = floatval($body['costo_unitario'] ?? 0);

        if ($id_suministro <= 0 || $id_material <= 0 || $cantidad_new <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos incompletos']);
            exit();
        }

        // Obtener cantidad anterior para ajustar existencias
        $resOld = mysqli_query($link, "SELECT cantidad FROM gf_suministros_detalle_ticket WHERE id_suministros=$id_suministro AND id_materiales=$id_material");
        if ($resOld && $rowOld = mysqli_fetch_assoc($resOld)) {
            $diff = $cantidad_new - intval($rowOld['cantidad']);
            mysqli_query($link, "UPDATE gf_materiales SET existencias = existencias + $diff WHERE id=$id_material");
        }

        $sql = "UPDATE gf_suministros_detalle_ticket
                SET cantidad=$cantidad_new, costo_unitario=$costo
                WHERE id_suministros=$id_suministro AND id_materiales=$id_material";

        if (mysqli_query($link, $sql)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => mysqli_error($link)]);
        }

        // ── ELIMINAR LÍNEA DE DETALLE ─────────────────────────────────
    } elseif ($accion === 'eliminar_detalle') {
        $id_suministro = intval($body['id_suministros'] ?? 0);
        $id_material = intval($body['id_materiales'] ?? 0);

        if ($id_suministro <= 0 || $id_material <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'IDs requeridos']);
            exit();
        }

        // Revertir existencias
        $resOld = mysqli_query($link, "SELECT cantidad FROM gf_suministros_detalle_ticket WHERE id_suministros=$id_suministro AND id_materiales=$id_material");
        if ($resOld && $rowOld = mysqli_fetch_assoc($resOld)) {
            mysqli_query($link, "UPDATE gf_materiales SET existencias = existencias - {$rowOld['cantidad']} WHERE id=$id_material");
        }

        if (mysqli_query($link, "DELETE FROM gf_suministros_detalle_ticket WHERE id_suministros=$id_suministro AND id_materiales=$id_material")) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => mysqli_error($link)]);
        }

    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Acción no reconocida: ' . $accion]);
    }

    mysqli_close($link);
    exit();
}

http_response_code(405);
echo json_encode(['error' => 'Método no permitido']);
?>