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
check_login();;


if (!isset($_POST['mesa_id']) || !isset($_POST['prod_id'])) {
    $_SESSION['error'] = "Datos incompletos";
    header("Location: ../mesas/mesas.php");
    exit;
}

$mesa_id = $_POST['mesa_id'];
$productos = $_POST['prod_id']; // puede venir como array de productos
$cantidades = isset($_POST['cantidad']) ? $_POST['cantidad'] : [];
$notas_array = isset($_POST['notas']) ? $_POST['notas'] : [];

$customer_id = 'fe6bb69bdd29'; // Cliente genérico
$order_id = uniqid();
$order_code = strtoupper(substr(md5(time()), 0, 8));

// nombre de la mesa
$query_mesa = "SELECT numero_mesa FROM rpos_mesas WHERE mesa_id = ?";
$stmt_mesa = $mysqli->prepare($query_mesa);
$stmt_mesa->bind_param('s', $mesa_id);
$stmt_mesa->execute();
$res_mesa = $stmt_mesa->get_result()->fetch_object();
$customer_name = "Mesa " . $res_mesa->numero_mesa;

// recorrer productos
foreach ($productos as $i => $prod_id) {
    $cantidad = isset($cantidades[$i]) ? intval($cantidades[$i]) : 1;
    $notas = isset($notas_array[$i]) ? trim($notas_array[$i]) : '';

    // info producto
    $query_producto = "SELECT * FROM rpos_products WHERE prod_id = ?";
    $stmt_producto = $mysqli->prepare($query_producto);
    $stmt_producto->bind_param('s', $prod_id);
    $stmt_producto->execute();
    $producto = $stmt_producto->get_result()->fetch_object();

    if (!$producto) {
        continue; // saltar si el producto no existe
    }

    // insertar detalle de orden
    $query_orden = "INSERT INTO rpos_orders 
        (order_id, order_code, customer_id, customer_name, prod_id, prod_name, prod_price, prod_qty, order_status, notas)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente', ?)";
    $stmt_orden = $mysqli->prepare($query_orden);
    $stmt_orden->bind_param(
        'sssssssis',
        $order_id,
        $order_code,
        $customer_id,
        $customer_name,
        $prod_id,
        $producto->prod_name,
        $producto->prod_price,
        $cantidad,
        $notas
    );
    $stmt_orden->execute();
}

// vincular mesa y orden (una sola vez)
$orden_mesa_id = uniqid();
$query_vinculo = "INSERT INTO rpos_ordenes_mesas 
                 (orden_mesa_id, order_id, mesa_id, estado)
                 VALUES (?, ?, ?, 'Activa')";
$stmt_vinculo = $mysqli->prepare($query_vinculo);
$stmt_vinculo->bind_param('sss', $orden_mesa_id, $order_id, $mesa_id);
$stmt_vinculo->execute();

$_SESSION['success'] = "Productos agregados correctamente a la mesa";
header("Location: ../mesas/mesa_detalle.php?mesa=$mesa_id");
exit;
?>
