<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ob_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/error.log');
error_reporting(E_ALL);
include('../config/config.php');
include('../config/checklogin.php');
check_login();

if (!isset($_GET['order_id']) || !isset($_GET['mesa'])) {
    $_SESSION['err'] = "Datos incompletos";
    header("Location: ../mesas/mesas.php");
    exit;
}

$order_id = $_GET['order_id'];
$mesa_id = $_GET['mesa'];

try {
    // Eliminar de órdenes de mesa
    $query = "DELETE FROM rpos_ordenes_mesas WHERE order_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('s', $order_id);
    $stmt->execute();
    $stmt->close();
    
    // Eliminar de órdenes principales
    $query = "DELETE FROM rpos_orders WHERE order_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('s', $order_id);
    $stmt->execute();
    $stmt->close();
    
    // Eliminar de cocina/bar si existe
    $query = "DELETE FROM rpos_estados_cocina WHERE order_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('s', $order_id);
    $stmt->execute();
    $stmt->close();
    
    $query = "DELETE FROM rpos_estados_bar WHERE order_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('s', $order_id);
    $stmt->execute();
    $stmt->close();
    
    $_SESSION['success'] = "Orden eliminada correctamente";
} catch(Exception $e) {
    $_SESSION['err'] = "Error al eliminar orden: " . $e->getMessage();
}

header("Location: ../mesas/mesa_detalle.php?mesa=$mesa_id");
exit;
?>