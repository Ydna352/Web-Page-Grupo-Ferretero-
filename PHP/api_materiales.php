<?php
/**
 * api_materiales.php — API REST para el módulo de Materiales (Inventario)
 */

ob_start(); // Captura cualquier output inesperado de PHP

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

/* ─── Log interno silencioso ─────────────────────────────────────────── */
function logMat(string $msg): void
{
    $f = __DIR__ . '/../logs/api_materiales_errors.log';
    if (!is_dir(dirname($f))) {
        @mkdir(dirname($f), 0755, true);
    }
    @file_put_contents($f, '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL, FILE_APPEND);
}

define('DIR_IMAGENES', __DIR__ . '/../imagenes_materiales/');
define('MAX_BYTES_IMAGEN', 5 * 1024 * 1024); // 5 MB

/* ─── Conexión ───────────────────────────────────────────────────────── */
require_once __DIR__ . '/conex.php';
$link = Conectarse();

/* ════════════════════════════════════════════════════════════════════════
 *  GET — Listar todos los gf_materiales
 * ════════════════════════════════════════════════════════════════════════ */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Paginación
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    if ($page < 1) $page = 1;
    if ($limit < 1) $limit = 10;
    $offset = ($page - 1) * $limit;

    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $area = isset($_GET['area']) ? trim($_GET['area']) : '';
    $stock = isset($_GET['stock']) && $_GET['stock'] !== '' ? intval($_GET['stock']) : -1;

    $condiciones = [];
    $params = [];
    $types = '';

    if ($search !== '') {
        $condiciones[] = "nombre LIKE ?";
        $params[] = '%' . $search . '%';
        $types .= 's';
    }
    if ($area !== '') {
        $condiciones[] = "area = ?";
        $params[] = $area;
        $types .= 's';
    }
    if ($stock >= 0) {
        $condiciones[] = "existencias <= ?";
        $params[] = $stock;
        $types .= 'i';
    }

    $where = '';
    if (count($condiciones) > 0) {
        $where = " WHERE " . implode(' AND ', $condiciones);
    }

    // Total
    $sqlCount = "SELECT COUNT(*) as total FROM gf_materiales" . $where;
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

    $sql = "SELECT id, nombre, area, descripcion, precio_unitario, existencias, imagen
         FROM gf_materiales" . $where . "
         ORDER BY id LIMIT ? OFFSET ?";
         
    $stmt = mysqli_prepare($link, $sql);
    if (!$stmt) {
        logMat("PREPARE GET falló: " . mysqli_error($link));
        http_response_code(500);
        ob_clean();
        echo json_encode(['error' => 'Error interno al consultar gf_materiales.']);
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
        $row['precio_unitario'] = (float) $row['precio_unitario'];
        $row['existencias'] = (int) $row['existencias'];

        /*
         * CACHE BUSTING — imagen_ts
         */
        $rutaFisica = DIR_IMAGENES . ($row['imagen'] ?? '');
        if ($row['imagen'] && !file_exists($rutaFisica)) {
            $lower = strtolower($row['imagen']);
            if ($lower !== $row['imagen'] && file_exists(DIR_IMAGENES . $lower)) {
                $row['imagen'] = $lower;
                $rutaFisica = DIR_IMAGENES . $lower;
            }
        }
        $row['imagen_ts'] = ($row['imagen'] && file_exists($rutaFisica))
            ? filemtime($rutaFisica)
            : time();

        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);
    mysqli_close($link);
    ob_clean();
    echo json_encode([
        'data' => $rows,
        'total' => $total,
        'page' => $page,
        'limit' => $limit
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

/* ════════════════════════════════════════════════════════════════════════
 *  POST — Acciones CRUD (multipart/form-data)
 * ════════════════════════════════════════════════════════════════════════ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $accion = trim($_POST['accion'] ?? '');

    /* ── INSERTAR ─────────────────────────────────────────────────────── */
    if ($accion === 'insertar') {

        $nombre = trim($_POST['nombre'] ?? '');
        $area = trim($_POST['area'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio = floatval($_POST['precio_unitario'] ?? 0);
        $existencias = intval($_POST['existencias'] ?? 0);

        if (!$nombre || !$area) {
            http_response_code(400);
            ob_clean();
            echo json_encode(['error' => 'Nombre y área son requeridos.']);
            exit();
        }
        if ($precio <= 0) {
            http_response_code(400);
            ob_clean();
            echo json_encode(['error' => 'El precio debe ser mayor a 0.']);
            exit();
        }

        if (empty($_FILES['imagen']) || $_FILES['imagen']['error'] === UPLOAD_ERR_NO_FILE) {
            http_response_code(400);
            ob_clean();
            echo json_encode(['error' => 'La imagen del material es obligatoria.']);
            exit();
        }

        $archivo = $_FILES['imagen'];
        $extInfo = validarArchivoImagen($archivo);
        if ($extInfo['error']) {
            http_response_code(400);
            ob_clean();
            echo json_encode(['error' => $extInfo['error']]);
            exit();
        }
        $ext = $extInfo['ext'];

        $sql = "INSERT INTO gf_materiales (nombre, area, descripcion, precio_unitario, existencias, imagen)
                 VALUES (?, ?, ?, ?, ?, '')";
        $stmt = mysqli_prepare($link, $sql);
        if (!$stmt) {
            logMat("PREPARE INSERT falló: " . mysqli_error($link));
            http_response_code(500);
            ob_clean();
            echo json_encode(['error' => 'Error interno al registrar el material.']);
            exit();
        }
        mysqli_stmt_bind_param($stmt, 'sssdi', $nombre, $area, $descripcion, $precio, $existencias);
        if (!mysqli_stmt_execute($stmt)) {
            logMat("EXECUTE INSERT falló: " . mysqli_stmt_error($stmt));
            http_response_code(500);
            ob_clean();
            echo json_encode(['error' => 'No se pudo guardar el material.']);
            mysqli_stmt_close($stmt);
            exit();
        }
        $nuevoId = mysqli_insert_id($link);
        mysqli_stmt_close($stmt);

        asegurarDirectorio(DIR_IMAGENES);
        $nombreImagen = $nuevoId . '.' . $ext;
        $rutaDestino = DIR_IMAGENES . $nombreImagen;

        if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            logMat("move_uploaded_file falló → {$rutaDestino}");
            mysqli_query($link, "DELETE FROM gf_materiales WHERE id = {$nuevoId}");
            http_response_code(500);
            ob_clean();
            echo json_encode(['error' => 'No se pudo guardar la imagen. Operación revertida.']);
            exit();
        }

        $stmtImg = mysqli_prepare($link, "UPDATE gf_materiales SET imagen = ? WHERE id = ?");
        if ($stmtImg) {
            mysqli_stmt_bind_param($stmtImg, 'si', $nombreImagen, $nuevoId);
            mysqli_stmt_execute($stmtImg);
            mysqli_stmt_close($stmtImg);
        }

        mysqli_close($link);
        ob_clean();
        echo json_encode([
            'success' => true,
            'id' => $nuevoId,
            'imagen' => $nombreImagen,
            'message' => 'Material registrado correctamente.',
        ]);
        exit();
    }

    /* ── ACTUALIZAR ───────────────────────────────────────────────────── */
    if ($accion === 'actualizar') {

        $id = intval($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $area = trim($_POST['area'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio = floatval($_POST['precio_unitario'] ?? 0);
        $existencias = intval($_POST['existencias'] ?? 0);
        $imagenActual = trim($_POST['imagen_actual'] ?? '');

        if ($id <= 0 || !$nombre || !$area) {
            http_response_code(400);
            ob_clean();
            echo json_encode(['error' => 'ID, nombre y área son requeridos.']);
            exit();
        }

        $imagenEnBD = $imagenActual;   // por defecto conservar la imagen actual
        $imagenNuevaSubida = false;

        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
            $archivo = $_FILES['imagen'];
            $extInfo = validarArchivoImagen($archivo);
            if ($extInfo['error']) {
                http_response_code(400);
                ob_clean();
                echo json_encode(['error' => $extInfo['error']]);
                exit();
            }
            $ext = $extInfo['ext'];

            asegurarDirectorio(DIR_IMAGENES);

            /*
             * BORRADO PREVIO ROBUSTO:
             * Busca cualquier archivo con ese id sin importar la extensión
             * (ej: 42.jpg, 42.png, 42.webp) y los elimina antes de guardar.
             * Esto evita archivos huérfanos aunque la extensión haya cambiado.
             */
            foreach (glob(DIR_IMAGENES . $id . '.*') as $archivoViejo) {
                @unlink($archivoViejo);
            }

            $nombreImagen = $id . '.' . $ext;
            $rutaDestino = DIR_IMAGENES . $nombreImagen;

            if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                logMat("move_uploaded_file (actualizar) falló → {$rutaDestino}");
                http_response_code(500);
                ob_clean();
                echo json_encode(['error' => 'No se pudo guardar la imagen nueva. Intente más tarde.']);
                exit();
            }

            $imagenEnBD = $nombreImagen;
            $imagenNuevaSubida = true;
        }

        $sql = "UPDATE gf_materiales
                 SET nombre = ?, area = ?, descripcion = ?,
                     precio_unitario = ?, existencias = ?, imagen = ?
                 WHERE id = ?";
        $stmt = mysqli_prepare($link, $sql);
        if (!$stmt) {
            logMat("PREPARE UPDATE falló: " . mysqli_error($link));
            http_response_code(500);
            ob_clean();
            echo json_encode(['error' => 'Error interno al actualizar. Intente más tarde.']);
            exit();
        }
        mysqli_stmt_bind_param(
            $stmt,
            'sssdisi',
            $nombre,
            $area,
            $descripcion,
            $precio,
            $existencias,
            $imagenEnBD,
            $id
        );

        if (!mysqli_stmt_execute($stmt)) {
            logMat("EXECUTE UPDATE falló: " . mysqli_stmt_error($stmt));
            mysqli_stmt_close($stmt);
            http_response_code(500);
            ob_clean();
            echo json_encode(['error' => 'No se pudo actualizar el material.']);
            exit();
        }
        mysqli_stmt_close($stmt);
        mysqli_close($link);
        ob_clean();
        echo json_encode([
            'success' => true,
            'imagen' => $imagenEnBD,
            'imagen_actualizada' => $imagenNuevaSubida,
            'message' => 'Material actualizado correctamente.',
        ]);
        exit();
    }

    /* ── ELIMINAR ─────────────────────────────────────────────────────── */
    if ($accion === 'eliminar') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            ob_clean();
            echo json_encode(['error' => 'ID requerido para eliminar.']);
            exit();
        }

        $stmtGet = mysqli_prepare($link, "SELECT imagen FROM gf_materiales WHERE id = ?");
        $imagenActual = '';
        if ($stmtGet) {
            mysqli_stmt_bind_param($stmtGet, 'i', $id);
            mysqli_stmt_execute($stmtGet);
            $resGet = mysqli_stmt_get_result($stmtGet);
            $fila = mysqli_fetch_assoc($resGet);
            if ($fila)
                $imagenActual = $fila['imagen'];
            mysqli_stmt_close($stmtGet);
        }

        $stmtDel = mysqli_prepare($link, "DELETE FROM gf_materiales WHERE id = ?");
        if (!$stmtDel) {
            logMat("PREPARE DELETE falló: " . mysqli_error($link));
            http_response_code(500);
            ob_clean();
            echo json_encode(['error' => 'Error interno al eliminar.']);
            exit();
        }
        mysqli_stmt_bind_param($stmtDel, 'i', $id);
        if (!mysqli_stmt_execute($stmtDel)) {
            logMat("EXECUTE DELETE falló: " . mysqli_stmt_error($stmtDel));
            mysqli_stmt_close($stmtDel);
            http_response_code(500);
            ob_clean();
            echo json_encode(['error' => 'No se pudo eliminar el material.']);
            exit();
        }
        mysqli_stmt_close($stmtDel);
        mysqli_close($link);

        if ($imagenActual !== '') {
            $rutaImagen = DIR_IMAGENES . $imagenActual;
            if (file_exists($rutaImagen)) {
                @unlink($rutaImagen);
            }
        }

        ob_clean();
        echo json_encode(['success' => true, 'message' => 'Material eliminado correctamente.']);
        exit();
    }

    /* Acción desconocida */
    http_response_code(400);
    ob_clean();
    echo json_encode(['error' => 'Acción no reconocida: ' . htmlspecialchars($accion)]);
    mysqli_close($link);
    exit();
}

/* ── Método no permitido ─────────────────────────────────────────────── */
http_response_code(405);
ob_clean();
echo json_encode(['error' => 'Método no permitido.']);
if (isset($link))
    mysqli_close($link);

/* ════════════════════════════════════════════════════════════════════════
 *  FUNCIONES AUXILIARES
 * ════════════════════════════════════════════════════════════════════════ */

/**
 * validarArchivoImagen — Valida extensión de un archivo de imagen.
 * Sin finfo_open (no disponible en EasyPHP).
 */
function validarArchivoImagen(array $archivo): array
{
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        return ['ext' => '', 'error' => 'Error al subir el archivo (código ' . $archivo['error'] . ').'];
    }

    if ($archivo['size'] > MAX_BYTES_IMAGEN) {
        return ['ext' => '', 'error' => 'El archivo no debe superar 5 MB.'];
    }

    $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    $extValidas = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $extValidas)) {
        return ['ext' => '', 'error' => 'Extensión no permitida. Use JPG, PNG o WebP.'];
    }

    return ['ext' => $ext, 'error' => null];
}

/**
 * asegurarDirectorio — Crea la carpeta si no existe.
 */
function asegurarDirectorio(string $dir): void
{
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}