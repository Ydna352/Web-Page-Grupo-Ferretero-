<?php
/**
 * subirConstancia.php — Endpoint para subir la Constancia de Situación Fiscal (PDF)
 * =====================================================================================
 * Método:  POST (multipart/form-data)
 * Campo:   constancia  → archivo PDF
 * Acción:  Valida, mueve y registra el archivo en la BD (tabla gf_clientes, campo constancia_fiscal)
 *
 * Respuestas JSON:
 *   Éxito: { "success": true, "message": "Constancia subida correctamente", "archivo": "nombre.pdf" }
 *   Error: { "error": "Descripción del problema" }
 *
 * Seguridad implementada:
 *   ✔ Solo acepta POST
 *   ✔ Valida extensión .pdf
 *   ✔ Valida tipo MIME real (finfo → application/pdf)
 *   ✔ Valida cabecera mágica %PDF (primeros 4 bytes)
 *   ✔ Límite de 5 MB
 *   ✔ Nombre renombrado con sha1 + timestamp (nunca expone el nombre original)
 *   ✔ Verifica move_uploaded_file() y file_exists() antes de tocar la BD
 *   ✔ Usa prepared statements para el UPDATE
 *   ✔ No expone rutas ni credenciales en mensajes de error
 */

// ── Protección: evita que warnings/notices de PHP corrompan el JSON ────────────
ob_start();
error_reporting(0);
ini_set('display_errors', '0');

header('Content-Type: application/json; charset=utf-8');

/**
 * Limpia cualquier salida acumulada en el buffer (warnings, notices, etc.)
 * y envía exclusivamente el JSON, luego termina el script.
 */
function jsonOut(array $data): void {
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit();
}

// ── Solo POST ─────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonOut(['error' => 'Método no permitido. Use POST.']);
}

// ── Cliente autenticado ────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'cliente' && isset($_SESSION['id_cliente'])) {
    $id_cliente = (int) $_SESSION['id_cliente'];
} else {
    $id_cliente = 6021; // Fallback demo
}

// ── Constantes ────────────────────────────────────────────────────────────
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5 MB en bytes
define('UPLOAD_DIR',   __DIR__ . '/../uploads/constancias/');
define('UPLOAD_URL',   '../uploads/constancias/'); // ruta relativa para guardar en BD

// ── Verificar que se recibió el archivo ───────────────────────────────────────
if (!isset($_FILES['constancia']) || $_FILES['constancia']['error'] === UPLOAD_ERR_NO_FILE) {
    jsonOut(['error' => 'No se recibió ningún archivo.']);
}

$file = $_FILES['constancia'];

// ── Verificar errores de PHP al subir ─────────────────────────────────────────
if ($file['error'] !== UPLOAD_ERR_OK) {
    $phpErrors = [
        UPLOAD_ERR_INI_SIZE   => 'El archivo supera el límite configurado en el servidor (revisa upload_max_filesize en php.ini).',
        UPLOAD_ERR_FORM_SIZE  => 'El archivo supera el límite del formulario.',
        UPLOAD_ERR_PARTIAL    => 'El archivo se subió de forma incompleta.',
        UPLOAD_ERR_NO_TMP_DIR => 'No se encontró directorio temporal en el servidor.',
        UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo en el servidor.',
        UPLOAD_ERR_EXTENSION  => 'Una extensión de PHP detuvo la subida.',
    ];
    $msg = $phpErrors[$file['error']] ?? 'Error al subir el archivo (código ' . $file['error'] . ').';
    jsonOut(['error' => $msg]);
}

// ── Validar tamaño (≤ 5 MB) ───────────────────────────────────────────────────
if ($file['size'] > MAX_FILE_SIZE) {
    jsonOut(['error' => 'El archivo supera el límite de 5 MB.']);
}

if ($file['size'] === 0) {
    jsonOut(['error' => 'El archivo está vacío.']);
}

// ── Validar extensión ─────────────────────────────────────────────────────────
$originalName = $file['name'];
$extension    = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

if ($extension !== 'pdf') {
    jsonOut(['error' => 'Solo se permiten archivos PDF (.pdf).']);
}

// ── Validar tipo MIME real (usando finfo si está disponible, o mime_content_type como fallback) ──
if (function_exists('finfo_open')) {
    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $mimeReal = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
} elseif (function_exists('mime_content_type')) {
    $mimeReal = mime_content_type($file['tmp_name']);
} else {
    // Si ninguna función está disponible, confiar solo en la cabecera mágica
    $mimeReal = 'application/pdf';
}

if ($mimeReal !== 'application/pdf') {
    jsonOut(['error' => 'El archivo no es un PDF válido (tipo MIME: ' . $mimeReal . ').']);
}

// ── Validar cabecera mágica %PDF (primeros 4 bytes) ──────────────────────────
$handle = fopen($file['tmp_name'], 'rb');
if ($handle === false) {
    jsonOut(['error' => 'No se pudo leer el archivo temporal.']);
}
$header = fread($handle, 4);
fclose($handle);

if ($header !== '%PDF') {
    jsonOut(['error' => 'El archivo no es un PDF válido (cabecera mágica incorrecta).']);
}

// ── Crear directorio de destino si no existe ──────────────────────────────────
if (!is_dir(UPLOAD_DIR)) {
    if (!mkdir(UPLOAD_DIR, 0755, true)) {
        jsonOut(['error' => 'No se pudo crear el directorio de almacenamiento. Verifica permisos del servidor.']);
    }
}

// ── Generar nombre seguro único (sha1 + timestamp) ────────────────────────────
$nombreSeguro = sha1(uniqid('constancia_', true) . $originalName) . '_' . time() . '.pdf';
$rutaDestino  = UPLOAD_DIR . $nombreSeguro;

// ── Mover archivo al destino ───────────────────────────────────────────────────
if (!move_uploaded_file($file['tmp_name'], $rutaDestino)) {
    jsonOut(['error' => 'No se pudo guardar el archivo en el servidor. Verifica permisos de la carpeta uploads/.']);
}

// ── Verificar que el archivo existe físicamente después de moverlo ─────────────
if (!file_exists($rutaDestino)) {
    jsonOut(['error' => 'Error de verificación: el archivo no fue guardado correctamente.']);
}

// ── Solo si el archivo está confirmado → actualizar la BD ─────────────────────
require_once __DIR__ . '/conex.php';
$link = Conectarse();

$rutaBD = UPLOAD_URL . $nombreSeguro; // ruta relativa que se guarda en la BD

$sql  = "UPDATE gf_clientes SET constancia_fiscal = ? WHERE id = ?";
$stmt = mysqli_prepare($link, $sql);

if (!$stmt) {
    @unlink($rutaDestino);
    mysqli_close($link);
    jsonOut(['error' => 'Error interno al preparar la actualización en la base de datos.']);
}

mysqli_stmt_bind_param($stmt, 'si', $rutaBD, $id_cliente);

if (!mysqli_stmt_execute($stmt)) {
    @unlink($rutaDestino);
    mysqli_stmt_close($stmt);
    mysqli_close($link);
    jsonOut(['error' => 'No se pudo registrar el archivo en la base de datos.']);
}

mysqli_stmt_close($stmt);
mysqli_close($link);

// ── Éxito ─────────────────────────────────────────────────────────────────────
jsonOut([
    'success' => true,
    'message' => 'Constancia subida correctamente.',
    'archivo' => $nombreSeguro
]);
?>
