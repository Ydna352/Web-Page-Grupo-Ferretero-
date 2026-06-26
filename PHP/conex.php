<?php
// conex.php — Conexión central usando MySQLi (compatible con todo el proyecto 12)
require_once __DIR__ . '/config.php';

function Conectarse(): mysqli
{
    static $conn = null;

    if ($conn === null) {
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if (!$conn) {
            // Nunca mostrar credenciales al usuario
            error_log('Error de conexión MySQLi: ' . mysqli_connect_error());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Error interno de conexión. Intente más tarde.']);
            exit();
        }

        mysqli_set_charset($conn, DB_CHARSET);
    }

    return $conn;
}
?>