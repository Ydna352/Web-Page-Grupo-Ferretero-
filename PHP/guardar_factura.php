<?php
/**
 * guardar_factura.php — Genera PDF y registra factura en BD
 */

ob_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  ob_end_clean();
  http_response_code(200);
  exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  ob_end_clean();
  http_response_code(405);
  echo json_encode(['error' => 'Método no permitido. Use POST']);
  exit();
}

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once 'conex.php';

// ── Leer body JSON ───────────────────────────────────────────────────
$body = json_decode(file_get_contents('php://input'), true) ?? [];
$id_ticket = intval($body['id_ticket'] ?? 0);
$id_cliente = isset($_SESSION['id_cliente']) ? (int) $_SESSION['id_cliente']
  : intval($body['id_cliente'] ?? 0);
if ($id_cliente <= 0)
  $id_cliente = 6021;

$uso_cfdi = substr(preg_replace('/[^A-Z0-9]/', '', strtoupper($body['uso_cfdi'] ?? 'G03')), 0, 3);

if ($id_ticket <= 0) {
  ob_end_clean();
  http_response_code(400);
  echo json_encode(['error' => 'id_ticket requerido']);
  exit();
}

// ── Conexión ──────────────────────────────────────────────────────────
$link = Conectarse();

// ── Verificar ticket + cliente ────────────────────────────────────────
$sqlT = "SELECT vt.id, DATE_FORMAT(vt.fecha,'%d/%m/%Y') AS fecha,
                DATE_FORMAT(vt.fecha,'%Y-%m-%d')         AS fecha_iso,
                t.nombre AS trabajador,
                c.nombre AS nombre_cliente,
                c.rfc, c.curp, c.regimen,
                c.calle_num, c.colonia, c.ciudad, c.estado, c.cp,
                c.correo_electronico
         FROM   gf_ventas_ticket  vt
         JOIN   gf_trabajadores   t  ON t.id = vt.id_trabajadores
         JOIN   gf_clientes       c  ON c.id = vt.id_clientes
         WHERE  vt.id = $id_ticket
           AND  vt.id_clientes = $id_cliente";

$resT = mysqli_query($link, $sqlT);
if (!$resT || mysqli_num_rows($resT) === 0) {
  ob_end_clean();
  http_response_code(404);
  echo json_encode(['error' => 'Ticket no encontrado o no pertenece al cliente']);
  exit();
}
$info = mysqli_fetch_assoc($resT);

// ── Verificar si ya tiene factura ─────────────────────────────────────
$resF = mysqli_query($link, "SELECT folio, CAST(pdf AS CHAR) AS pdf FROM gf_facturas WHERE id_ventas = $id_ticket");
if ($resF && mysqli_num_rows($resF) > 0) {
  $f = mysqli_fetch_assoc($resF);
  ob_end_clean();
  echo json_encode([
      'error' => "Este ticket ya fue facturado.",
      'estado' => 'ya_facturado',
      'folio' => $f['folio'],
      'pdf' => $f['pdf']
  ]);
  exit();
}

// ── Detalle de artículos ──────────────────────────────────────────────
$sqlD = "SELECT m.nombre AS articulo, vd.cantidad, m.precio_unitario,
                (vd.cantidad * m.precio_unitario) AS subtotal_item
         FROM   gf_ventas_detalle_ticket vd
         JOIN   gf_materiales m ON m.id = vd.id_materiales
         WHERE  vd.id_ventas = $id_ticket";
$resD = mysqli_query($link, $sqlD);
$items = [];
$subtotal = 0.0;
while ($row = mysqli_fetch_assoc($resD)) {
  $row['precio_unitario'] = (float) $row['precio_unitario'];
  $row['subtotal_item'] = (float) $row['subtotal_item'];
  $subtotal += $row['subtotal_item'];
  $items[] = $row;
}

$iva = round($subtotal * 0.16, 2);
$total = round($subtotal + $iva, 2);

// ── Insertar en BD para obtener el folio AUTO_INCREMENT ──────────────
$sqlIns = "INSERT INTO gf_facturas (id_ventas, id_clientes, fecha, uso_cfdi, pdf)
           VALUES ($id_ticket, $id_cliente, NOW(), '$uso_cfdi', '')";
if (!mysqli_query($link, $sqlIns)) {
  ob_end_clean();
  http_response_code(500);
  echo json_encode(['error' => 'No se pudo registrar la factura: ' . mysqli_error($link)]);
  exit();
}
$folio = mysqli_insert_id($link);
$pdfName = "factura_{$folio}.pdf";

// ── Construir HTML de la factura ──────────────────────────────────────
$filasHtml = '';
foreach ($items as $it) {
  $pu = '$' . number_format($it['precio_unitario'], 2);
  $sub = '$' . number_format($it['subtotal_item'], 2);
  $filasHtml .= "<tr>
        <td style='padding:7px 10px;border-bottom:1px solid #eee;font-size:11px;'>{$it['cantidad']}.00</td>
        <td style='padding:7px 10px;border-bottom:1px solid #eee;font-size:11px;'>{$it['articulo']}</td>
        <td style='padding:7px 10px;border-bottom:1px solid #eee;font-size:11px;'>$pu</td>
        <td style='padding:7px 10px;border-bottom:1px solid #eee;font-size:11px;'>$sub</td>
    </tr>";
}

$dir = trim($info['calle_num'] . ', Col. ' . $info['colonia'] . ', ' . $info['ciudad'] . ', ' . $info['estado'] . ' C.P. ' . $info['cp'], ', ');
$fmtS = '$' . number_format($subtotal, 2);
$fmtI = '$' . number_format($iva, 2);
$fmtT = '$' . number_format($total, 2);
$folioPad = str_pad($folio, 6, '0', STR_PAD_LEFT);
$uuid = sprintf(
  '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
  mt_rand(0, 0xffff), mt_rand(0, 0xffff),
  mt_rand(0, 0xffff),
  mt_rand(0, 0x0fff) | 0x4000,
  mt_rand(0, 0x3fff) | 0x8000,
  mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
);

ini_set('memory_limit', '256M');
set_time_limit(120);

// ── Logo en base64 ────────────────────────────────────────────────────
$logoPath = realpath(__DIR__ . '/../formato_facturas/Logo_blanco_GF.PNG');
$logoTag = '';
if ($logoPath && file_exists($logoPath)) {
  $fileSize = filesize($logoPath);
  if ($fileSize > 0 && $fileSize < 500000) {
    $logoData = file_get_contents($logoPath);
    if ($logoData !== false) {
      $logoSrc = 'data:image/png;base64,' . base64_encode($logoData);
      $logoTag = "<img src='$logoSrc' class='logo-img' alt='Logo'>";
    }
  }
}

$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; margin: 0; padding: 20px; color: #222; }
  .header-table { width: 100%; background: #3e8cdd; border-collapse: collapse; }
  .header-left  { padding: 16px 20px; color: white; vertical-align: middle; }
  .header-right { padding: 16px 20px; background: #74cb3c; color: white; vertical-align: middle; text-align: right; width: 30%; }
  .empresa-name { font-size: 22px; font-weight: bold; color: white; }
  .slogan       { font-size: 8px; letter-spacing: 1px; color: #e0f0ff; }
  .logo-img     { width: 44px; height: 44px; vertical-align: middle; margin-right: 10px; }
  .issuer { border-bottom: 2px solid #3e8cdd; padding: 8px 0; text-align: right; margin-bottom: 12px; }
  .issuer p { margin: 0; font-size: 9.5px; line-height: 1.6; color: #333; }
  .data-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; border-bottom: 2px solid #3e8cdd; }
  .data-table td { padding: 3px 0; font-size: 9.5px; vertical-align: top; }
  .data-table .meta-td { text-align: right; }
  .items-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
  .items-table th { background: #3e8cdd; color: white; padding: 8px 10px; font-size: 10px; text-align: left; }
  .items-table td { padding: 6px 10px; border-bottom: 1px solid #eee; font-size: 10px; }
  .items-table .even td { background: #f8faff; }
  .totals-table { width: 100%; border-collapse: collapse; margin-top: 12px; }
  .totals-table .fiscal-td { font-size: 8.5px; color: #555; vertical-align: top; width: 55%; padding-right: 20px; }
  .totals-table .totals-td { text-align: right; vertical-align: top; }
  .cfdi-notice { color: red; font-weight: bold; font-size: 7.5px; margin-top: 6px; }
  .total-row { border-bottom: 1px solid #eee; padding: 3px 0; font-size: 10.5px; }
  .grand-total-row { border-bottom: 2px solid #3e8cdd; padding: 3px 0; font-size: 12px; color: #3e8cdd; font-weight: bold; }
  .sig { text-align: center; font-size: 8.5px; color: #888; margin-top: 20px; border-top: 1px solid #ccc; padding-top: 8px; }
  .footer-table { width: 100%; background: #3e8cdd; border-collapse: collapse; margin-top: 24px; }
  .footer-left  { background: #3e8cdd; padding: 18px; width: 70%; }
  .footer-right { background: #74cb3c; padding: 18px; }
</style>
</head>
<body>

<table class="header-table">
  <tr>
    <td class="header-left">
      $logoTag
      <span class="empresa-name">Piedras</span><br>
      <span class="slogan">UNA SOLUCIÓN A TUS NECESIDADES</span>
    </td>
    <td class="header-right">
      <span style="font-size:11px;font-weight:bold;">FACTURA</span><br>
      <span style="font-size:9px;">Folio: $folioPad</span>
    </td>
  </tr>
</table>

<div class="issuer">
  <p><strong>GUADALUPE MONSERRAT MORA RAMÍREZ</strong></p>
  <p>C. VENUSTIANO CARRANZA NTE. No. 6 SAN BARTOLOMÉ</p>
  <p>SAN PABLO DEL MONTE TLAXCALA C.P. 90970 &nbsp;|&nbsp; RFC: MORG-821212-VD2</p>
</div>

<table class="data-table">
  <tr>
    <td style="width:55%;">
      <strong>CLIENTE:</strong> {$info['nombre_cliente']}<br>
      <strong>RFC:</strong> {$info['rfc']}<br>
      <strong>CURP:</strong> {$info['curp']}<br>
      <strong>RÉGIMEN:</strong> {$info['regimen']}<br>
      <strong>DIRECCIÓN:</strong> $dir<br>
      <strong>ATENDIÓ:</strong> {$info['trabajador']}
    </td>
    <td class="meta-td">
      <strong>FOLIO:</strong> $folioPad<br>
      <strong>FECHA:</strong> {$info['fecha']}<br>
      <strong>USO CFDI:</strong> $uso_cfdi<br>
      <strong>MÉTODO PAGO:</strong> PUE
    </td>
  </tr>
</table>

<table class="items-table">
  <thead>
    <tr>
      <th>CANT.</th>
      <th>ARTÍCULO</th>
      <th>PRECIO UNIT.</th>
      <th>IMPORTE</th>
    </tr>
  </thead>
  <tbody>
    $filasHtml
  </tbody>
</table>

<table class="totals-table">
  <tr>
    <td class="fiscal-td">
      <strong>Folio Fiscal UUID:</strong> $uuid<br>
      <p class="cfdi-notice">ESTE DOCUMENTO ES UNA REPRESENTACIÓN IMPRESA DE UN CFDI (SIMULACIÓN)</p>
    </td>
    <td class="totals-td">
      <table style="width:100%;border-collapse:collapse;">
        <tr class="total-row"><td>SUBTOTAL:</td><td style="text-align:right;"><strong>$fmtS</strong></td></tr>
        <tr class="total-row"><td>IVA (16%):</td><td style="text-align:right;"><strong>$fmtI</strong></td></tr>
        <tr class="grand-total-row"><td><strong>TOTAL:</strong></td><td style="text-align:right;"><strong>$fmtT</strong></td></tr>
      </table>
    </td>
  </tr>
</table>

<div class="sig">
  <p>SELLO DIGITAL DEL CFDI (SIMULADO) — Folio $folioPad</p>
</div>

<table class="footer-table" style="margin-top:30px;">
  <tr>
    <td class="footer-left"></td>
    <td class="footer-right"></td>
  </tr>
</table>

</body>
</html>
HTML;

// ── Generar PDF con DomPDF ─────────────────────────────────────────────
$composerAutoload = __DIR__ . '/../vendor/autoload.php';
$legacyDompdfLoad = __DIR__ . '/../Cliente/libreria_dompdf/dompdf/autoload.inc.php';

$pdfDir = __DIR__ . '/../facturas_pdf/';
if (!is_dir($pdfDir))
  mkdir($pdfDir, 0755, true);

$aviso = null;
$dompdfLoaded = false;

if (file_exists($composerAutoload)) {
  try {
    require_once $composerAutoload;
    $dompdfLoaded = true;
  } catch (\Exception $e) { /* ignorar */
  }
}

if (!$dompdfLoaded && file_exists($legacyDompdfLoad)) {
  try {
    require_once $legacyDompdfLoad;
    $dompdfLoaded = true;
  } catch (\Exception $e) { /* ignorar */
  }
}

if ($dompdfLoaded && class_exists('\Dompdf\Dompdf')) {
  try {
    $options = new \Dompdf\Options();
    $options->set('isRemoteEnabled', false);
    $options->set('isHtml5ParserEnabled', true);
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('chroot', realpath(__DIR__ . '/..'));

    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('letter', 'portrait');
    $dompdf->render();

    $pdfContent = $dompdf->output();

    // ── Guardar en servidor ──────────────────────────────────────
    if (!file_put_contents($pdfDir . $pdfName, $pdfContent)) {
      throw new \Exception('No se pudo escribir el PDF en disco');
    }

  } catch (\Exception $e) {
    $pdfName = "factura_{$folio}.html";
    file_put_contents($pdfDir . $pdfName, $html);
    $aviso = 'PDF fallback a HTML: ' . $e->getMessage();
  }
} else {
  $pdfName = "factura_{$folio}.html";
  file_put_contents($pdfDir . $pdfName, $html);
  $aviso = 'Librería DomPDF no disponible — archivo guardado como HTML';
}

// ── Actualizar BD ──────────────────────────────────────────────────────
$safeName = mysqli_real_escape_string($link, $pdfName);
mysqli_query($link, "UPDATE gf_facturas SET pdf='$safeName' WHERE folio=$folio");
mysqli_close($link);

// ── Responder JSON ─────────────────────────────────────────────────────
ob_end_clean();

$resp = [
  'success' => true,
  'folio' => $folio,
  'pdf' => $pdfName,
  'total' => $total
];
if ($aviso)
  $resp['aviso'] = $aviso;

echo json_encode($resp);
?>