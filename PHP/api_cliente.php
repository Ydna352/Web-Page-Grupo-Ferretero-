<?php
/**
 * api_cliente.php — API para el portal del cliente
 * ================================================
 * Usa ConectarseCliente() (usuario MySQL "cliente" con permisos restringidos).
 *
 * Endpoints (via ?accion=):
 *   perfil              → GET datos del cliente
 *   actualizar_perfil   → POST actualizar datos del cliente
 *   compras             → GET tickets de compra + detalles + gf_materiales (JOIN READ-ONLY)
 *   gf_facturas            → GET gf_facturas del cliente
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/conex.php';

// ── Resolución del id_cliente ─────────────────────────────────────────────────
// Prioridad: 1) Sesión PHP  2) Param GET (legacy/SPA)  3) Demo fallback (6021)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'cliente' && isset($_SESSION['id_cliente'])) {
    // Sesión PHP real — cliente autenticado
    $id_cliente = (int) $_SESSION['id_cliente'];
} elseif (isset($_GET['id_cliente']) && (int) $_GET['id_cliente'] > 0) {
    // Parámetro GET (compatibilidad con SPA via localStorage)
    $id_cliente = (int) $_GET['id_cliente'];
} else {
    // Fallback de desarrollo (quitar en producción)
    $id_cliente = 6021;
}

// ── Conexión con privilegios de cliente ──────────────────────────────────────
$link = Conectarse();

// ── Determinar acción ────────────────────────────────────────────────────────
$accion = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // POST: leer JSON del body
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['accion'])) {
        $accion = $input['accion'];
    }
} else {
    // GET: leer del query string
    $accion = isset($_GET['accion']) ? $_GET['accion'] : '';
}

// ══════════════════════════════════════════════════════════════════════════════
// ACCIÓN: perfil — Obtener datos del cliente
// ══════════════════════════════════════════════════════════════════════════════
if ($accion === 'perfil') {

    $sql = "SELECT id, nombre, rfc, curp, correo_electronico, regimen,
                   calle_num, colonia, ciudad, estado, cp, telefono
            FROM gf_clientes
            WHERE id = ?";

    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id_cliente);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode($row);
    } else {
        echo json_encode(["error" => "Cliente no encontrado"]);
    }

    mysqli_stmt_close($stmt);
}

// ══════════════════════════════════════════════════════════════════════════════
// ACCIÓN: actualizar_perfil — Actualizar datos del cliente (POST)
// ══════════════════════════════════════════════════════════════════════════════
elseif ($accion === 'actualizar_perfil') {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(["error" => "Método no permitido"]);
        mysqli_close($link);
        exit();
    }

    // Campos permitidos para actualización
    $nombre = isset($input['nombre']) ? trim($input['nombre']) : null;
    $rfc = isset($input['rfc']) ? trim($input['rfc']) : null;
    $curp = isset($input['curp']) ? trim($input['curp']) : null;
    $email = isset($input['email']) ? trim($input['email']) : null;
    $regimen = isset($input['regimen']) ? trim($input['regimen']) : null;
    $calle = isset($input['calle_num']) ? trim($input['calle_num']) : null;
    $colonia = isset($input['colonia']) ? trim($input['colonia']) : null;
    $ciudad = isset($input['ciudad']) ? trim($input['ciudad']) : null;
    $estado = isset($input['estado']) ? trim($input['estado']) : null;
    $cp = isset($input['cp']) ? trim($input['cp']) : null;
    $telefono = isset($input['telefono']) ? trim($input['telefono']) : null;

    // Validación mínima
    if (!$nombre || !$email) {
        echo json_encode(["error" => "Nombre y correo electrónico son obligatorios"]);
        mysqli_close($link);
        exit();
    }

    $sql = "UPDATE gf_clientes SET
                nombre = ?, rfc = ?, curp = ?, correo_electronico = ?,
                regimen = ?, calle_num = ?, colonia = ?, ciudad = ?,
                estado = ?, cp = ?, telefono = ?
            WHERE id = ?";

    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param(
        $stmt,
        'sssssssssssi',
        $nombre,
        $rfc,
        $curp,
        $email,
        $regimen,
        $calle,
        $colonia,
        $ciudad,
        $estado,
        $cp,
        $telefono,
        $id_cliente
    );

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["ok" => true, "mensaje" => "Perfil actualizado correctamente"]);
    } else {
        echo json_encode(["error" => "Error al actualizar: " . mysqli_error($link)]);
    }

    mysqli_stmt_close($stmt);
}

// ══════════════════════════════════════════════════════════════════════════════
// ACCIÓN: compras — Tickets + detalles + gf_materiales (JOINs READ-ONLY)
// ══════════════════════════════════════════════════════════════════════════════
elseif ($accion === 'compras') {

    $resultado = [
        "tickets"    => [],
        "detalles"   => [],
        "materiales" => [],
        "trabajadores" => []
    ];

    // 1. Tickets del cliente (SELECT en gf_ventas_ticket)
    $sql_tickets = "SELECT t.id, t.id_clientes, t.id_trabajadores,
                           DATE_FORMAT(t.fecha, '%Y-%m-%d') AS fecha,
                           IF(f.folio IS NOT NULL, 1, 0) AS facturado,
                           f.folio AS folio_factura
                    FROM gf_ventas_ticket t
                    LEFT JOIN gf_facturas f ON t.id = f.id_ventas
                    WHERE t.id_clientes = ?";

    $params = [$id_cliente];
    $types = "i";

    if (!empty($_GET['fecha_inicio']) && !empty($_GET['fecha_fin'])) {
        $sql_tickets .= " AND t.fecha BETWEEN ? AND ?";
        $params[] = $_GET['fecha_inicio'] . " 00:00:00";
        $params[] = $_GET['fecha_fin'] . " 23:59:59";
        $types .= "ss";
    }

    if (isset($_GET['sin_facturar']) && $_GET['sin_facturar'] == '1') {
        $sql_tickets .= " AND f.folio IS NULL";
    }

    $sql_tickets .= " ORDER BY t.fecha DESC";

    $stmt = mysqli_prepare($link, $sql_tickets);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    $ticket_ids = [];
    while ($row = mysqli_fetch_assoc($res)) {
        // Convertir facturado a boolean
        $row['facturado'] = ($row['facturado'] == 1);
        $resultado["tickets"][] = $row;
        $ticket_ids[] = (int) $row['id'];
    }
    mysqli_stmt_close($stmt);

    // 2. Detalles de esos tickets (SELECT en gf_ventas_detalle_ticket)
    //    JOIN con gf_materiales para obtener precio_unitario como precio_venta
    if (count($ticket_ids) > 0) {
        // Construir placeholders para IN(...)
        $placeholders = implode(',', array_fill(0, count($ticket_ids), '?'));
        $types = str_repeat('i', count($ticket_ids));

        $sql_detalles = "SELECT vdt.id_ventas, vdt.id_materiales, vdt.cantidad,
                                m.precio_unitario AS precio_venta
                         FROM gf_ventas_detalle_ticket vdt
                         JOIN gf_materiales m ON vdt.id_materiales = m.id
                         WHERE vdt.id_ventas IN ($placeholders)";

        $stmt = mysqli_prepare($link, $sql_detalles);
        mysqli_stmt_bind_param($stmt, $types, ...$ticket_ids);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);

        // Recopilar IDs de gf_materiales para la consulta de gf_materiales
        $material_ids = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $resultado["detalles"][] = $row;
            $material_ids[(int) $row['id_materiales']] = true;
        }
        mysqli_stmt_close($stmt);

        // 3. Info de gf_materiales (SELECT READ-ONLY en gf_materiales)
        if (count($material_ids) > 0) {
            $mat_ids = array_keys($material_ids);
            $placeholders_m = implode(',', array_fill(0, count($mat_ids), '?'));
            $types_m = str_repeat('i', count($mat_ids));

            $sql_mat = "SELECT id, nombre, area, descripcion, precio_unitario
                        FROM gf_materiales
                        WHERE id IN ($placeholders_m)";

            $stmt = mysqli_prepare($link, $sql_mat);
            mysqli_stmt_bind_param($stmt, $types_m, ...$mat_ids);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);

            while ($row = mysqli_fetch_assoc($res)) {
                $resultado["materiales"][] = $row;
            }
            mysqli_stmt_close($stmt);
        }

        // 4. Nombres de trabajadores que atendieron esos tickets
        $worker_ids = array_unique(array_column($resultado["tickets"], 'id_trabajadores'));
        if (count($worker_ids) > 0) {
            $placeholders_w = implode(',', array_fill(0, count($worker_ids), '?'));
            $types_w = str_repeat('i', count($worker_ids));
            $sql_w = "SELECT id, nombre FROM gf_trabajadores WHERE id IN ($placeholders_w)";
            $stmt = mysqli_prepare($link, $sql_w);
            mysqli_stmt_bind_param($stmt, $types_w, ...$worker_ids);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($res)) {
                $resultado["trabajadores"][$row['id']] = $row['nombre'];
            }
            mysqli_stmt_close($stmt);
        }
    }

    echo json_encode($resultado);
}

// ══════════════════════════════════════════════════════════════════════════════
// ACCIÓN: gf_facturas — Facturas del cliente
// ══════════════════════════════════════════════════════════════════════════════
elseif ($accion === 'gf_facturas') {

    // Facturas vinculadas al cliente (a través de id_clientes directo en gf_facturas)
    $sql = "SELECT folio, id_ventas, id_clientes,
                   DATE_FORMAT(fecha, '%Y-%m-%d %H:%i:%s') AS fecha,
                   uso_cfdi
            FROM gf_facturas
            WHERE id_clientes = ?
            ORDER BY fecha DESC";

    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id_cliente);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    $gf_facturas = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $gf_facturas[] = $row;
    }
    mysqli_stmt_close($stmt);

    echo json_encode($gf_facturas);
}

// ══════════════════════════════════════════════════════════════════════════════
// ACCIÓN: guardar_factura — Registrar nueva factura (POST)
// Permiso: INSERT en gf_facturas  ✔
// ══════════════════════════════════════════════════════════════════════════════
elseif ($accion === 'guardar_factura') {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(["error" => "Método no permitido"]);
        mysqli_close($link);
        exit();
    }

    $id_ventas = isset($input['id_ventas']) ? (int) $input['id_ventas'] : 0;
    $uso_cfdi = isset($input['uso_cfdi']) ? trim($input['uso_cfdi']) : 'G01';

    if ($id_ventas <= 0) {
        echo json_encode(["error" => "Se requiere un id_ventas válido"]);
        mysqli_close($link);
        exit();
    }

    // Validar que el ticket pertenece al cliente demo (seguridad mínima)
    $checkSql = "SELECT id FROM gf_ventas_ticket WHERE id = ? AND id_clientes = ?";
    $checkStmt = mysqli_prepare($link, $checkSql);
    mysqli_stmt_bind_param($checkStmt, 'ii', $id_ventas, $id_cliente);
    mysqli_stmt_execute($checkStmt);
    mysqli_stmt_store_result($checkStmt);

    if (mysqli_stmt_num_rows($checkStmt) === 0) {
        echo json_encode(["error" => "El ticket no pertenece a este cliente o no existe"]);
        mysqli_stmt_close($checkStmt);
        mysqli_close($link);
        exit();
    }
    mysqli_stmt_close($checkStmt);

    // Verificar que el ticket no tenga ya una factura (UNIQUE id_ventas)
    $dupSql = "SELECT folio FROM gf_facturas WHERE id_ventas = ?";
    $dupStmt = mysqli_prepare($link, $dupSql);
    mysqli_stmt_bind_param($dupStmt, 'i', $id_ventas);
    mysqli_stmt_execute($dupStmt);
    mysqli_stmt_store_result($dupStmt);

    if (mysqli_stmt_num_rows($dupStmt) > 0) {
        echo json_encode(["error" => "Este ticket ya tiene una factura registrada"]);
        mysqli_stmt_close($dupStmt);
        mysqli_close($link);
        exit();
    }
    mysqli_stmt_close($dupStmt);

    // Insertar la factura
    $insSql = "INSERT INTO gf_facturas (id_ventas, id_clientes, uso_cfdi) VALUES (?, ?, ?)";
    $insStmt = mysqli_prepare($link, $insSql);
    mysqli_stmt_bind_param($insStmt, 'iis', $id_ventas, $id_cliente, $uso_cfdi);

    if (mysqli_stmt_execute($insStmt)) {
        $folio = mysqli_insert_id($link);
        echo json_encode(["ok" => true, "folio" => $folio, "mensaje" => "Factura registrada correctamente"]);
    } else {
        echo json_encode(["error" => "No se pudo registrar la factura: " . mysqli_error($link)]);
    }
    mysqli_stmt_close($insStmt);
}

// ══════════════════════════════════════════════════════════════════════════════
// ACCIÓN: constancia_fiscal — Consultar ruta del PDF actual del cliente (GET)
// ══════════════════════════════════════════════════════════════════════════════
elseif ($accion === 'constancia_fiscal') {

    $sql = "SELECT constancia_fiscal FROM gf_clientes WHERE id = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id_cliente);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode([
            "tiene_constancia" => !empty($row['constancia_fiscal']),
            "ruta" => $row['constancia_fiscal'] ?? null
        ]);
    } else {
        echo json_encode(["error" => "Cliente no encontrado"]);
    }

    mysqli_stmt_close($stmt);
}

// ══════════════════════════════════════════════════════════════════════════════
// ACCIÓN NO RECONOCIDA
// ══════════════════════════════════════════════════════════════════════════════
else {
    echo json_encode(["error" => "Acción no reconocida: " . $accion]);
}

mysqli_close($link);
?>