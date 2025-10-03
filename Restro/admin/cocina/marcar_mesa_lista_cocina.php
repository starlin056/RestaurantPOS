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
    $mesa_id = $_POST['mesa_id'];
    $es_delivery = isset($_POST['es_delivery']) ? $_POST['es_delivery'] : 0;

    // Solo procesar mesas, no delivery
    if (!$es_delivery) {
        // Marcar todos los pedidos de mesa como listos
        $update_orders = "UPDATE rpos_orders o 
                         JOIN rpos_ordenes_mesas om ON o.order_id = om.order_id 
                         SET o.order_status = 'Listo' 
                         WHERE om.mesa_id = ? AND o.order_status IN ('Pendiente', 'En preparación')";
        $stmt = $mysqli->prepare($update_orders);
        $stmt->bind_param('s', $mesa_id);
        $stmt->execute();
        
        // Actualizar estado en cocina
        $update_cocina = "UPDATE rpos_estados_cocina ec 
                         JOIN rpos_orders o ON ec.order_id = o.order_id 
                         JOIN rpos_ordenes_mesas om ON o.order_id = om.order_id 
                         SET ec.estado = 'Listo' 
                         WHERE om.mesa_id = ? AND o.order_status = 'Listo'";
        $stmt2 = $mysqli->prepare($update_cocina);
        $stmt2->bind_param('s', $mesa_id);
        $stmt2->execute();
    }

    // Redirigir de vuelta
    header("Location: ../cocina/cocina.php");
    exit();
}
?>