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
    $order_id    = $_POST['order_id'];
    $estado      = $_POST['estado'];
    $es_delivery = isset($_POST['es_delivery']) ? $_POST['es_delivery'] : 0;
    $delivery_id = isset($_POST['delivery_id']) ? $_POST['delivery_id'] : null;

    // ===============================
    // Actualizar estado del pedido
    // ===============================
    $update_order = "UPDATE rpos_orders SET order_status = ? WHERE order_id = ?";
    $stmt = $mysqli->prepare($update_order);
    $stmt->bind_param('ss', $estado, $order_id);
    $stmt->execute();

    // ===============================
    // Actualizar estado en cocina
    // ===============================
    $update_cocina = "UPDATE rpos_estados_cocina SET estado = ? WHERE order_id = ?";
    $stmt2 = $mysqli->prepare($update_cocina);
    $stmt2->bind_param('ss', $estado, $order_id);
    $stmt2->execute();

    // ===============================
    // Sección de notificaciones
    // ===============================
    if ($estado === 'Listo') {
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

        // ===============================
        // Preparar datos notificación
        // ===============================
        $notificacion_id = uniqid();
        $tipo_pedido = $es_delivery ? 'delivery' : 'mesa';

        if ($es_delivery) {
            $mesa_id = null; // Delivery no tiene mesa
            $mensaje = "Delivery #{$delivery_id}: {$pedido_info->prod_name} listo para entrega";
        } else {
            $mesa_id = $pedido_info->mesa_id;
            $mensaje = "Mesa #{$pedido_info->numero_mesa}: {$pedido_info->prod_name} listo para servir";
            $delivery_id = null; // Pedido de mesa no tiene delivery
        }

        // ===============================
        // Insertar notificación
        // ===============================
        $query_notif = "INSERT INTO rpos_notificaciones 
                        (notificacion_id, mesa_id, order_id, delivery_id, mensaje, tipo, tipo_pedido, estado) 
                        VALUES (?, ?, ?, ?, ?, 'nuevo_pedido', ?, 'pendiente')";
        $stmt_notif = $mysqli->prepare($query_notif);
        $stmt_notif->bind_param(
            'ssssss',
            $notificacion_id,
            $mesa_id,
            $order_id,
            $delivery_id,
            $mensaje,
            $tipo_pedido
        );
        $stmt_notif->execute();
    }

    // ===============================
    // Si es delivery y se marca como listo → pasa a "Lista para facturar"
    // ===============================
    if ($es_delivery && $estado === 'Listo' && $delivery_id) {
        $update_delivery = "UPDATE rpos_delivery_orders SET estado = 'Lista para facturar' WHERE delivery_id = ?";
        $stmt3 = $mysqli->prepare($update_delivery);
        $stmt3->bind_param('s', $delivery_id);
        $stmt3->execute();
    }

    // ===============================
    // Redirigir de vuelta a cocina
    // ===============================
    header("Location: ../cocina/cocina.php");
    exit();
}
?>
