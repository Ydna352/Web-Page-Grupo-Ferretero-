<?php
/**
 * generar_factura.php — Vista previa de factura desde la BD
 * GET ?id_ticket=X&id_cliente=Y
 * Retorna JSON con todos los datos necesarios para renderizar la factura.
 * Conexión: ConectarseCliente() (solo lectura)
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/conex.php';

// ── Validar parámetros ────────────────────────────────────────────────
$id_ticket  = intval($_GET['id_ticket']  ?? 0);
$id_cliente = intval($_GET['id_cliente'] ?? 0);

// Fallback al cliente demo (6021) si no se pasa id_cliente
if ($id_cliente <= 0) $id_cliente = 6021;

if ($id_ticket <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetro id_ticket requerido']);
    exit();
}

$link = Conectarse();

// ── Verificar que el ticket existe y pertenece al cliente ─────────────
$sql = "SELECT vt.id, DATE_FORMAT(vt.fecha,'%d/%m/%Y') AS fecha,
               DATE_FORMAT(vt.fecha,'%Y-%m-%d') AS fecha_iso,
               t.nombre AS trabajador, t.id AS id_trabajador
        FROM   gf_ventas_ticket vt
        JOIN   gf_trabajadores  t  ON t.id  = vt.id_trabajadores
        WHERE  vt.id = $id_ticket
          AND  vt.id_clientes = $id_cliente";

$res = mysqli_query($link, $sql);
if (!$res || mysqli_num_rows($res) === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Ticket no encontrado o no pertenece al cliente']);
    exit();
}
$ticket = mysqli_fetch_assoc($res);

// ── Verificar si ya tiene factura ─────────────────────────────────────
$resF = mysqli_query($link, "SELECT folio, CAST(pdf AS CHAR) AS pdf FROM gf_facturas WHERE id_ventas = $id_ticket");
if ($resF && mysqli_num_rows($resF) > 0) {
    $fila = mysqli_fetch_assoc($resF);
    echo json_encode([
        'ya_facturado' => true,
        'folio'        => $fila['folio'],
        'pdf'          => $fila['pdf']
    ]);
    exit();
}

// ── Datos del cliente ─────────────────────────────────────────────────
$sqlC = "SELECT nombre, rfc, curp, regimen, calle_num, colonia, ciudad, estado, cp, correo_electronico
         FROM   gf_clientes
         WHERE  id = $id_cliente";
$resC = mysqli_query($link, $sqlC);
if (!$resC || mysqli_num_rows($resC) === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Cliente no encontrado']);
    exit();
}
$cliente = mysqli_fetch_assoc($resC);

// ── Detalle de artículos con precio ──────────────────────────────────
$sqlD = "SELECT m.id AS id_material,
                m.nombre AS articulo,
                vd.cantidad,
                m.precio_unitario,
                (vd.cantidad * m.precio_unitario) AS subtotal_item
         FROM   gf_ventas_detalle_ticket vd
         JOIN   gf_materiales m ON m.id = vd.id_materiales
         WHERE  vd.id_ventas = $id_ticket";
$resD = mysqli_query($link, $sqlD);
if (!$resD) {
    http_response_code(500);
    echo json_encode(['error' => mysqli_error($link)]);
    exit();
}
$items    = [];
$subtotal = 0.0;
while ($row = mysqli_fetch_assoc($resD)) {
    $row['precio_unitario'] = floatval($row['precio_unitario']);
    $row['subtotal_item']   = floatval($row['subtotal_item']);
    $subtotal              += $row['subtotal_item'];
    $items[] = $row;
}

$iva   = round($subtotal * 0.16, 2);
$total = round($subtotal + $iva, 2);

mysqli_close($link);

echo json_encode([
    'ya_facturado' => false,
    'ticket'       => $ticket,
    'cliente'      => $cliente,
    'items'        => $items,
    'subtotal'     => $subtotal,
    'iva'          => $iva,
    'total'        => $total
]);
?>
