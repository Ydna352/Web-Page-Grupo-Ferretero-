<?php

ini_set('display_errors', 0);
ini_set('log_errors', 1);
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR])) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Error fatal PHP: ' . $error['message'] . ' en ' . $error['file'] . ':' . $error['line']
        ]);
    }
});
ob_start();
/**
 * api_home.php — API REST para la gestión del contenido de gf_empresa_home
 *
 * Ruta:   /PHP/api_home.php
 * Métodos: GET  → SELECT de datos actuales (id = 1)
 *          POST → UPDATE de todos los campos + manejo de logo (multipart/form-data)
 *
 * Seguridad:
 *  - Sentencias preparadas (PDO/MySQLi PreparedStatement) para evitar inyección SQL.
 *  - Validación estricta de tipo MIME y extensión del archivo de imagen.
 *  - Errores de BD se loguean internamente; el usuario solo recibe mensajes genéricos.
 */

/* ─── Cabeceras CORS ─────────────────────────────────────────────────── */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

/* ─── Utilidad de log interno (silencioso para el usuario) ───────────── */
function logInterno(string $msg): void
{
    $logFile = __DIR__ . '/../logs/api_home_errors.log';
    $dir = dirname($logFile);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    @file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL, FILE_APPEND);
}

/* ─── Conexión centralizada (reutiliza conex.php del proyecto) ───────── */
require_once __DIR__ . '/conex.php';
$link = Conectarse(); // Usa el usuario administrador con permisos completos

/* ════════════════════════════════════════════════════════════════════════
 *  GET — Obtener los datos actuales de gf_empresa_home (fila id = 1)
 * ════════════════════════════════════════════════════════════════════════ */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    /*
     * Sincronización: Siempre se lee la fila más reciente directamente
     * de la BD sin caché, garantizando datos frescos en cada petición.
     */
    $stmt = mysqli_prepare($link, "SELECT * FROM gf_empresa_home WHERE id = 1 LIMIT 1");

    if (!$stmt) {
        logInterno("PREPARE SELECT falló: " . mysqli_error($link));
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error interno. Intente más tarde.']);
        exit();
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $datos = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$datos) {
        // No existe la fila aún; devolver objeto vacío
        echo json_encode(['success' => true, 'data' => []]);
    } else {
        echo json_encode(['success' => true, 'data' => $datos]);
    }

    mysqli_close($link);
    exit();
}

/* ════════════════════════════════════════════════════════════════════════
 *  POST — Actualizar gf_empresa_home + manejar subida de logo
 * ════════════════════════════════════════════════════════════════════════ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ← AGREGA ESTO TEMPORALMENTE
    ob_clean();
    error_reporting(E_ALL);
    ini_set('display_errors', 0); // no mostrar errores como HTML
    ini_set('log_errors', 1);

    logInterno("POST recibido — FILES: " . json_encode($_FILES) . " — POST keys: " . implode(',', array_keys($_POST)));
    /* ── 1. Leer campos de texto del formulario (multipart/form-data) ── */
    $campos = [
        'nombre_empresa',
        'titulo_bienvenida',
        'subtitulo',
        'mision',
        'vision',
        'email',
        'telefono1',
        'telefono2',
        'calle_num',
        'colonia',
        'ciudad',
        'estado',
        'cp',
        'giro_comercial',
        'ano_fundacion',
    ];

    $data = [];
    foreach ($campos as $campo) {
        // trim() básico; la protección real la garantizan los prepared statements
        $data[$campo] = isset($_POST[$campo]) ? trim($_POST[$campo]) : '';
    }

    /* ── Validaciones mínimas de campos obligatorios ─────────────────── */
    $obligatorios = ['nombre_empresa', 'titulo_bienvenida', 'mision', 'vision', 'email', 'telefono1', 'calle_num', 'colonia', 'ciudad', 'estado', 'cp'];
    foreach ($obligatorios as $campo) {
        if ($data[$campo] === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => "El campo '$campo' es obligatorio."]);
            mysqli_close($link);
            exit();
        }
    }

    /* ── 2. Determinar el valor final de logo_imagen ────────────────────
     *
     * DECISIÓN DE PERSISTENCIA:
     *   - Si el usuario sube un archivo nuevo → validar, mover y usar el nombre nuevo.
     *   - Si NO sube archivo → usar logo_actual (hidden input desde el formulario),
     *     que el JS pobló al cargar los datos de la BD.
     *   Esto garantiza que logo_imagen NUNCA se sobrescriba con vacío.
     * ─────────────────────────────────────────────────────────────────── */

    // Valor de respaldo: nombre de logo que ya existe en la BD (viene del hidden input)
    $logoActual = trim($_POST['logo_actual'] ?? '');
    $nombreLogoEnBD = $logoActual; // por defecto se conserva el logo actual
    $logoNuevoSubido = false;      // flag para la respuesta JSON

    if (isset($_FILES['logo_imagen']) && $_FILES['logo_imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
        $archivo = $_FILES['logo_imagen'];

        // a) Verificar que no haya error de subida del servidor
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Error al subir el archivo de imagen (código ' . $archivo['error'] . ').']);
            mysqli_close($link);
            exit();
        }

        // b) Validar MIME REAL con fileinfo — no confiar en $_FILES['type'] (falsificable)
        $tiposMimePermitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeReal = finfo_file($finfo, $archivo['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeReal, $tiposMimePermitidos)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Tipo de archivo no permitido. Use JPG, PNG, WebP o GIF.']);
            mysqli_close($link);
            exit();
        }

        // c) Validar extensión del nombre original (segunda capa)
        $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $extensionesPermitidas)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Extensión de imagen no permitida.']);
            mysqli_close($link);
            exit();
        }

        // d) Validar tamaño máximo: 5 MB
        if ($archivo['size'] > 5 * 1024 * 1024) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'El logo no debe superar 5 MB.']);
            mysqli_close($link);
            exit();
        }

        // e) Carpeta de destino compartida — accesible por todas las vistas
        //    Ruta: /imagenes_empresa/  (raíz del proyecto, un nivel arriba de /PHP/)
        $dirDestino = __DIR__ . '/../imagenes_empresa/';
        if (!is_dir($dirDestino)) {
            mkdir($dirDestino, 0755, true);
        }

        // f) Nombre único: logo_TIMESTAMP.ext  (evita colisiones de caché)
        $nombreArchivo = 'logo_' . time() . '.' . $ext;
        $rutaDestino = $dirDestino . $nombreArchivo;

        // Agrega esto temporalmente en api_home.php justo antes del move_uploaded_file
        logInterno("Destino: $rutaDestino — Escribible: " . (is_writable($dirDestino) ? 'SÍ' : 'NO'));

        // ── DIAGNÓSTICO TEMPORAL ──
        logInterno("Intentando mover archivo");
        logInterno("Dir destino: " . $dirDestino);
        logInterno("¿Existe?: " . (is_dir($dirDestino) ? 'SÍ' : 'NO'));
        logInterno("¿Escribible?: " . (is_writable($dirDestino) ? 'SÍ' : 'NO'));
        logInterno("Ruta destino: " . $rutaDestino);
        logInterno("Tmp name: " . $archivo['tmp_name']);
        logInterno("¿Tmp existe?: " . (file_exists($archivo['tmp_name']) ? 'SÍ' : 'NO'));



        // g) Mover al destino final (nunca manipular tmp_name directamente)
        if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            logInterno("move_uploaded_file falló → destino: $rutaDestino");
            // Agrega esto también:
            logInterno("PHP error: " . print_r(error_get_last(), true));
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'No se pudo guardar el logo. Intente más tarde.']);
            mysqli_close($link);
            exit();
        }

        // Logo nuevo validado y guardado físicamente
        $nombreLogoEnBD = $nombreArchivo;
        $logoNuevoSubido = true;
    }

    /* ── 3. UPDATE único con sentencia preparada ────────────────────────
     *
     * Un solo query cubre ambos casos (nuevo logo / logo conservado).
     * `actualizado_en` se renueva automáticamente por ON UPDATE CURRENT_TIMESTAMP.
     * ─────────────────────────────────────────────────────────────────── */
    $sql = "UPDATE gf_empresa_home SET
                logo_imagen       = ?,
                nombre_empresa    = ?,
                titulo_bienvenida = ?,
                subtitulo         = ?,
                mision            = ?,
                vision            = ?,
                email             = ?,
                telefono1         = ?,
                telefono2         = ?,
                calle_num         = ?,
                colonia           = ?,
                ciudad            = ?,
                estado            = ?,
                cp                = ?,
                giro_comercial    = ?,
                ano_fundacion     = ?
            WHERE id = 1";

    $stmt = mysqli_prepare($link, $sql);
    if (!$stmt) {
        logInterno("PREPARE UPDATE falló: " . mysqli_error($link));
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error interno al guardar. Intente más tarde.']);
        mysqli_close($link);
        exit();
    }

    // 16 parámetros tipo string — logo_imagen SIEMPRE tiene valor (nuevo o el actual)
    mysqli_stmt_bind_param(
        $stmt,
        'ssssssssssssssss',
        $nombreLogoEnBD,           // nuevo archivo ó logo_actual conservado
        $data['nombre_empresa'],
        $data['titulo_bienvenida'],
        $data['subtitulo'],
        $data['mision'],
        $data['vision'],
        $data['email'],
        $data['telefono1'],
        $data['telefono2'],
        $data['calle_num'],
        $data['colonia'],
        $data['ciudad'],
        $data['estado'],
        $data['cp'],
        $data['giro_comercial'],
        $data['ano_fundacion']
    );

    if (mysqli_stmt_execute($stmt)) {
        $filas = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($link);

        echo json_encode([
            'success' => true,
            'message' => 'Información actualizada correctamente.',
            'logo_guardado' => $logoNuevoSubido,   // true solo si subió archivo nuevo
            'logo_nombre' => $nombreLogoEnBD,    // nombre final en BD (nuevo o conservado)
            'filas_afectadas' => $filas,
        ]);
    } else {
        logInterno("EXECUTE UPDATE falló: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        mysqli_close($link);
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'No se pudo guardar. Intente más tarde.']);
    }

    exit();
}

/* ── Método no permitido ─────────────────────────────────────────────── */
http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Método no permitido.']);
mysqli_close($link);
