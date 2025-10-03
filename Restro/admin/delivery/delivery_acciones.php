<?php
ob_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/error.log'); // asegúrate que exista la carpeta logs
error_reporting(E_ALL);
session_start();
include('../config/config.php');
include('../config/checklogin.php');
check_login();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$accion = $_POST['accion'] ?? '';
$delivery_id = $_POST['delivery_id'] ?? '';

if (empty($accion) || empty($delivery_id)) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

try {
    switch ($accion) {
        case 'cancelar':
            // Verificar que el pedido existe y está en estado válido para cancelar
            $check_stmt = $mysqli->prepare("SELECT estado FROM rpos_delivery_orders WHERE delivery_id = ?");
            $check_stmt->bind_param('s', $delivery_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows === 0) {
                throw new Exception('Pedido no encontrado');
            }
            
            $pedido = $check_result->fetch_assoc();
            
            if ($pedido['estado'] !== 'Recibido') {
                throw new Exception('Solo se pueden cancelar pedidos en estado "Recibido"');
            }
            
            // Actualizar estado a Cancelado
            $update_stmt = $mysqli->prepare("UPDATE rpos_delivery_orders SET estado = 'Cancelado' WHERE delivery_id = ?");
            $update_stmt->bind_param('s', $delivery_id);
            
            if (!$update_stmt->execute()) {
                throw new Exception('Error al cancelar el pedido: ' . $update_stmt->error);
            }
            
            echo json_encode(['success' => true, 'message' => 'Pedido cancelado correctamente']);
            break;
            
        case 'actualizar_estado':
            $nuevo_estado = $_POST['nuevo_estado'] ?? '';
            $estados_validos = ['Recibido', 'En preparación', 'En camino', 'Entregado'];
            
            if (!in_array($nuevo_estado, $estados_validos)) {
                throw new Exception('Estado no válido');
            }
            
            $update_stmt = $mysqli->prepare("UPDATE rpos_delivery_orders SET estado = ? WHERE delivery_id = ?");
            $update_stmt->bind_param('ss', $nuevo_estado, $delivery_id);
            
            if (!$update_stmt->execute()) {
                throw new Exception('Error al actualizar estado: ' . $update_stmt->error);
            }
            
            echo json_encode(['success' => true, 'message' => 'Estado actualizado correctamente']);
            break;
            
        default:
            throw new Exception('Acción no reconocida');
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}