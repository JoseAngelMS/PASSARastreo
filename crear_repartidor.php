<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$host = 'localhost';
$dbname = 'passa_rastreo';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data || empty($data['nombre'])) {
        throw new Exception("El nombre del repartidor es obligatorio.");
    }

    // El rol se asigna por defecto como 'repartidor' basándonos en la estructura de tu DB
    $sql = "INSERT INTO usuarios (nombre, rol, telefono, correo) VALUES (:nombre, 'repartidor', :telefono, :correo)";
    $stmt = $pdo->prepare($sql);
    
    $stmt->execute([
        ':nombre'   => $data['nombre'],
        ':telefono' => !empty($data['telefono']) ? $data['telefono'] : null,
        ':correo'   => !empty($data['correo']) ? $data['correo'] : null
    ]);

    echo json_encode([
        'success' => true,
        'id' => $pdo->lastInsertId(),
        'mensaje' => 'Repartidor creado correctamente.'
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>