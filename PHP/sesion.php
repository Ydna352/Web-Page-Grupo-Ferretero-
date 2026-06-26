<?php
/**
 * sesion.php — Endpoint JSON que retorna los datos de la sesión activa
 * =====================================================================
 * GET ../PHP/sesion.php
 *
 * Respuesta si hay sesión:
 *   { "logueado": true, "id": 4014, "nombre": "Jorge Piedras", "email": "...", "rol": "admin", "puesto": "Administrador" }
 *
 * Respuesta si NO hay sesión:
 *   { "logueado": false }
 */

session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

if (!isset($_SESSION['rol'])) {
    echo json_encode(['logueado' => false]);
    exit();
}

$datos = [
    'logueado' => true,
    'id'       => $_SESSION['id_usuario']  ?? null,
    'nombre'   => $_SESSION['nombre']      ?? '',
    'email'    => $_SESSION['email']       ?? '',
    'rol'      => $_SESSION['rol']         ?? '',
    'puesto'   => $_SESSION['puesto']      ?? null,
    'telefono' => $_SESSION['telefono']    ?? null,
];

// Agregar id_cliente solo si es cliente
if ($_SESSION['rol'] === 'cliente') {
    $datos['id_cliente'] = $_SESSION['id_cliente'] ?? $_SESSION['id_usuario'];
}

echo json_encode($datos);
?>
