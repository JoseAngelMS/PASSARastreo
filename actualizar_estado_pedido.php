<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');

$host = 'localhost';
$dbname = 'passa_rastreo';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Como enviamos FormData (para soportar archivos), leemos directamente de $_POST y $_FILES
    $id_pedido = $_POST['id_pedido'] ?? null;
    $estado = $_POST['estado'] ?? null;
    $motivo_excepcion = $_POST['motivo_excepcion'] ?? null;
    $id_repartidor = $_POST['id_repartidor'] ?? null;
    $latitud_real = $_POST['latitud_real'] ?? 0;
    $longitud_real = $_POST['longitud_real'] ?? 0;
    
    if (empty($id_pedido) || empty($estado)) throw new Exception("Datos incompletos.");

    // 1. Manejo de la foto de evidencia si existe
    $url_foto_evidencia = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "", basename($_FILES['foto']['name']));
        $targetFilePath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetFilePath)) {
            $url_foto_evidencia = $targetFilePath;
        }
    }

    // 2. Actualizar la tabla principal de pedidos
    $sql_pedido = "UPDATE pedidos SET estado = :estado";
    $params_pedido = [':estado' => $estado, ':id_pedido' => $id_pedido];

    // Si falló la entrega, actualizamos el motivo_excepcion en la tabla pedidos
    if ($estado === 'fallido' && !empty($motivo_excepcion)) {
        $sql_pedido .= ", motivo_excepcion = :motivo";
        $params_pedido[':motivo'] = trim($motivo_excepcion);
    }

    $sql_pedido .= " WHERE id_pedido = :id_pedido";
    
    $stmt = $pdo->prepare($sql_pedido);
    $stmt->execute($params_pedido);

    // 3. Si el estado es "entregado", insertamos en tu tabla entregas_confirmadas
    if ($estado === 'entregado') {
        $sql_conf = "INSERT INTO entregas_confirmadas 
                    (id_pedido, id_repartidor, latitud_real, longitud_real, distancia_metros, url_foto_evidencia, notas_repartidor)
                    VALUES (:id_pedido, :id_repartidor, :lat, :lon, 0, :foto, :notas)
                    ON DUPLICATE KEY UPDATE 
                    url_foto_evidencia = :foto, notas_repartidor = :notas";
                    
        $stmt_conf = $pdo->prepare($sql_conf);
        $stmt_conf->execute([
            ':id_pedido' => $id_pedido,
            ':id_repartidor' => $id_repartidor,
            ':lat' => $latitud_real,
            ':lon' => $longitud_real,
            ':foto' => $url_foto_evidencia,
            ':notas' => $motivo_excepcion
        ]);
    }

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>