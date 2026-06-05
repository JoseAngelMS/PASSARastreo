<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$host = 'localhost';
$dbname = 'passa_rastreo';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($_GET['repartidor_id'])) {
        throw new Exception("Falta el ID del repartidor.");
    }
    $repartidor_id = $_GET['repartidor_id'];

    // Consultamos los pedidos de ESTE repartidor y los unimos con los datos del cliente
    $sql = "SELECT p.id_pedido, c.nombre AS cliente, p.direccion_entrega, p.estado, DATE_FORMAT(p.fecha_actualizacion, '%h:%i %p') AS hora_actualizacion, p.motivo_excepcion
            FROM pedidos p
            LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
            WHERE p.id_repartidor_asignado = :repartidor_id
            ORDER BY p.id_pedido DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':repartidor_id' => $repartidor_id]);
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $activas = [];
    $historial = [];

    // Separamos los pedidos por estado
    foreach ($pedidos as $pedido) {
        if (in_array($pedido['estado'], ['pendiente', 'asignado', 'en_camino'])) {
            $activas[] = $pedido;
        } else if (in_array($pedido['estado'], ['entregado', 'fallido', 'reprogramado'])) {
            $historial[] = $pedido;
        }
    }

    echo json_encode(['success' => true, 'activas' => $activas, 'historial' => $historial]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>