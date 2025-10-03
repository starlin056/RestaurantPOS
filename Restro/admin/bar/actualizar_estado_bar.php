<?php
ob_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/error.log');
error_reporting(E_ALL);
session_start();
include('../config/config.php');
include('../config/checklogin.php');
check_login();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = $_POST['order_id'];
    $estado = $_POST['estado'];
    $es_delivery = isset($_POST['es_delivery']) ? $_POST['es_delivery'] : 0;
    $delivery_id = isset($_POST['delivery_id']) ? $_POST['delivery_id'] : null;

    // Actualizar estado del pedido
    $update_order = "UPDATE rpos_orders SET order_status = ? WHERE order_id = ?";
    $stmt = $mysqli->prepare($update_order);
    $stmt->bind_param('ss', $estado, $order_id);
    $stmt->execute();

    // Actualizar estado en bar
    $update_bar = "UPDATE rpos_estados_bar SET estado = ? WHERE order_id = ?";
    $stmt2 = $mysqli->prepare($update_bar);
    $stmt2->bind_param('ss', $estado, $order_id);
    $stmt2->execute();

    // Si el pedido está listo, crear notificación
    if ($estado == 'Listo') {
        // Obtener información del pedido
        $query_info = "SELECT o.*, m.mesa_id, m.numero_mesa, p.prod_name 
                      FROM rpos_orders o 
                      LEFT JOIN rpos_ordenes_mesas om ON o.order_id = om.order_id 
                      LEFT JOIN rpos_mesas m ON om.mesa_id = m.mesa_id 
                      LEFT JOIN rpos_products p ON o.prod_id = p.prod_id 
                      WHERE o.order_id = ?";
        $stmt_info = $mysqli->prepare($query_info);
        $stmt_info->bind_param('s', $order_id);
        $stmt_info->execute();
        $pedido_info = $stmt_info->get_result()->fetch_object();
        
        $notificacion_id = uniqid();
        $mensaje = $es_delivery ? 
            "Delivery: {$pedido_info->prod_name} listo para entrega" : 
            "Mesa #{$pedido_info->numero_mesa}: {$pedido_info->prod_name} listo para servir";
        
        $query_notif = "INSERT INTO rpos_notificaciones 
                       (notificacion_id, mesa_id, order_id, delivery_id, mensaje, tipo, tipo_pedido, estado) 
                       VALUES (?, ?, ?, ?, ?, 'nuevo_pedido', ?, 'pendiente')";
        $stmt_notif = $mysqli->prepare($query_notif);
        $tipo_pedido = $es_delivery ? 'delivery' : 'mesa';
        $stmt_notif->bind_param('ssssss', $notificacion_id, $pedido_info->mesa_id, $order_id, $delivery_id, $mensaje, $tipo_pedido);
        $stmt_notif->execute();
    }

    // Si es delivery y se marca como listo, cambiar estado a "Lista para facturar"
    if ($es_delivery && $estado == 'Listo' && $delivery_id) {
        $update_delivery = "UPDATE rpos_delivery_orders SET estado = 'Lista para facturar' WHERE delivery_id = ?";
        $stmt3 = $mysqli->prepare($update_delivery);
        $stmt3->bind_param('s', $delivery_id);
        $stmt3->execute();
    }

    // Redirigir de vuelta
    header("Location: ../bar/bar.php");
    exit();
}
?>