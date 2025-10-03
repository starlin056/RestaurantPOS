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

if (!isset($_GET['mesa'])) {
    $_SESSION['error'] = "Mesa no especificada";
    header("Location: ../mesas/mesas.php");
    exit;
}

$mesa_id = $_GET['mesa'];
$num_personas = isset($_POST['num_personas']) ? intval($_POST['num_personas']) : 1;

// Verificar que la mesa esté disponible
$query = "SELECT * FROM rpos_mesas WHERE mesa_id = ? AND estado = 'Disponible'";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('s', $mesa_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error'] = "La mesa no está disponible";
    header("Location: ../mesas/mesas.php");
    exit;
}

// Ocupar la mesa
$query = "UPDATE rpos_mesas 
          SET estado = 'Ocupada', num_personas = ?
          WHERE mesa_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('is', $num_personas, $mesa_id);
$stmt->execute();

// Crear una orden genérica para la mesa
$order_id = uniqid();
$order_code = strtoupper(substr(md5(time()), 0, 8));
$customer_id = 'fe6bb69bdd29'; // Cliente genérico

$query_orden = "INSERT INTO rpos_orders 
               (order_id, order_code, customer_id, customer_name, prod_id, prod_name, prod_price, prod_qty, order_status)
               VALUES (?, ?, ?, 'Mesa ' || (SELECT numero_mesa FROM rpos_mesas WHERE mesa_id = ?), '', 'Inicio de mesa', '0', '1', 'Pendiente')";
$stmt_orden = $mysqli->prepare($query_orden);
$stmt_orden->bind_param('ssss', $order_id, $order_code, $customer_id, $mesa_id);
$stmt_orden->execute();

// Vincular orden con mesa
$orden_mesa_id = uniqid();
$query_vinculo = "INSERT INTO rpos_ordenes_mesas 
                 (orden_mesa_id, order_id, mesa_id, estado)
                 VALUES (?, ?, ?, 'Activa')";
$stmt_vinculo = $mysqli->prepare($query_vinculo);
$stmt_vinculo->bind_param('sss', $orden_mesa_id, $order_id, $mesa_id);
$stmt_vinculo->execute();

$_SESSION['success'] = "Mesa ocupada correctamente";
header("Location: ../mesas/mesa_detalle.php?mesa=$mesa_id");
exit;
?>