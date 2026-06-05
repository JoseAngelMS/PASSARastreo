<?php
// Ocultar errores HTML de PHP para que no rompan el formato JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Configurar cabeceras para aceptar JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Configuración de conexión a la base de datos (Ajusta con tus credenciales reales)
$host = 'localhost';
$dbname = 'passa_rastreo';
$username = 'root';
$password = '';

try {
    // Conexión PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtener y decodificar el cuerpo JSON de la petición (fetch)
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Validación básica
    if (!$data || empty($data['cliente_id']) || empty($data['direccion']) || empty($data['detalles']) || empty($data['latitud']) || empty($data['longitud'])) {
        throw new Exception("Faltan datos obligatorios. Asegúrate de llenar todos los campos y seleccionar una ubicación en el mapa.");
    }

    // Preparar la consulta SQL
    $estado_inicial = !empty($data['repartidor_id']) ? 'asignado' : 'pendiente';

    $sql = "INSERT INTO pedidos (id_cliente, id_admin_creador, id_repartidor_asignado, direccion_entrega, detalles, latitud_destino, longitud_destino, estado, fecha_creacion) 
            VALUES (:id_cliente, :id_admin_creador, :id_repartidor_asignado, :direccion_entrega, :detalles, :latitud_destino, :longitud_destino, :estado, NOW())";
    
    $stmt = $pdo->prepare($sql);
    
    // Ejecutar pasando los parámetros para evitar inyección SQL
    $stmt->execute([
        ':id_cliente'             => $data['cliente_id'],
        ':id_admin_creador'       => 1, // NOTA: Aquí deberías poner el ID del administrador de la sesión actual
        ':id_repartidor_asignado' => !empty($data['repartidor_id']) ? $data['repartidor_id'] : null,
        ':direccion_entrega'      => $data['direccion'],
        ':detalles'               => $data['detalles'],
        ':latitud_destino'        => $data['latitud'],
        ':longitud_destino'       => $data['longitud'],
        ':estado'                 => $estado_inicial
    ]);

    $nuevo_id = $pdo->lastInsertId();

    // Responder éxito
    echo json_encode([
        'success' => true,
        'id' => $nuevo_id,
        'mensaje' => 'Pedido registrado correctamente en el sistema.'
    ]);

} catch (Throwable $e) {
    // Capturar y responder errores
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>