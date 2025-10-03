<?php
session_start();
include('../config/config.php');
include('../config/checklogin.php');
check_login();

if (!isset($_POST['order_id']) || !isset($_POST['destino'])) {
    $_SESSION['error'] = "Datos incompletos";
    header("Location: " . ($_POST['destino'] ?? 'cocina') . ".php");
    exit;
}

$order_id = $_POST['order_id'];
$destino = $_POST['destino'];

// Actualizar estado del pedido
$query = "UPDATE rpos_orders SET order_status = 'Listo' WHERE order_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('s', $order_id);
$stmt->execute();

// Verificar si toda la mesa está lista
$query_mesa = "SELECT om.mesa_id 
               FROM rpos_ordenes_mesas om
               WHERE om.order_id = ?";
$stmt_mesa = $mysqli->prepare($query_mesa);
$stmt_mesa->bind_param('s', $order_id);
$stmt_mesa->execute();
$result_mesa = $stmt_mesa->get_result();
$mesa_id = $result_mesa->fetch_object()->mesa_id;

// Contar pedidos pendientes de la mesa
$query_pendientes = "SELECT COUNT(*) as pendientes
                     FROM rpos_orders o
                     JOIN rpos_ordenes_mesas om ON o.order_id = om.order_id
                     WHERE om.mesa_id = ?
                     AND o.order_status != 'Listo'";
$stmt_pendientes = $mysqli->prepare($query_pendientes);
$stmt_pendientes->bind_param('s', $mesa_id);
$stmt_pendientes->execute();
$result_pendientes = $stmt_pendientes->get_result();
$pendientes = $result_pendientes->fetch_object()->pendientes;

if ($pendientes == 0) {
    // Todos los pedidos están listos
    $query_update_mesa = "UPDATE rpos_mesas SET estado = 'Listo para servir' WHERE mesa_id = ?";
    $stmt_update_mesa = $mysqli->prepare($query_update_mesa);
    $stmt_update_mesa->bind_param('s', $mesa_id);
    $stmt_update_mesa->execute();
    
    // Enviar notificación
    $notificacion_id = uniqid();
    $query_notif = "INSERT INTO rpos_notificaciones 
                   (notificacion_id, mesa_id, mensaje, tipo, estado)
                   VALUES (?, ?, 'Mesa #' || (SELECT numero_mesa FROM rpos_mesas WHERE mesa_id = ?) || ' lista para servir', 'mesa_lista', 'pendiente')";
    $stmt_notif = $mysqli->prepare($query_notif);
    $stmt_notif->bind_param('sss', $notificacion_id, $mesa_id, $mesa_id);
    $stmt_notif->execute();
}

$_SESSION['success'] = "Pedido marcado como listo";
header("Location: " . $destino . ".php");
exit;
?>