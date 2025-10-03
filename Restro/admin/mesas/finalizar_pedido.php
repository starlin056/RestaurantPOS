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


if (!isset($_GET['mesa'])) {
    $_SESSION['error'] = "Mesa no especificada";
    header("Location: ../mesas/mesas.php");
    exit;
}

$mesa_id = $_GET['mesa'];

// Verificar si hay pedidos en preparación
$stmt = $mysqli->prepare("SELECT COUNT(*) as pendientes 
    FROM rpos_orders o
    JOIN rpos_ordenes_mesas om ON o.order_id = om.order_id
    WHERE om.mesa_id = ? AND om.estado = 'Activa' 
      AND o.order_status NOT IN ('Listo','Cancelado','Facturada')");
$stmt->bind_param('s', $mesa_id);
$stmt->execute();
$pendientes = $stmt->get_result()->fetch_object()->pendientes;
$stmt->close();

if ($pendientes > 0) {
    $_SESSION['error'] = "No se puede finalizar. Aún hay $pendientes pedidos pendientes o en preparación.";
    header("Location: ../mesas/mesa_detalle.php?mesa=$mesa_id");
    exit;
}

try {
    $mysqli->begin_transaction();

    // Marcar mesa lista para facturar
    $stmt = $mysqli->prepare("UPDATE rpos_mesas 
                              SET estado = 'Lista para facturar' 
                              WHERE mesa_id = ?");
    $stmt->bind_param('s', $mesa_id);
    $stmt->execute();
    $stmt->close();

    // Cambiar estado de las órdenes activas
    $stmt = $mysqli->prepare("UPDATE rpos_orders o
        JOIN rpos_ordenes_mesas om ON o.order_id = om.order_id
        SET o.order_status = 'Listo para facturar'
        WHERE om.mesa_id = ? AND om.estado = 'Activa'");
    $stmt->bind_param('s', $mesa_id);
    $stmt->execute();
    $stmt->close();

    $mysqli->commit();

    $_SESSION['success'] = "Mesa finalizada correctamente. Lista para facturar.";
    header("Location: ../mesas/mesa_detalle.php?mesa=$mesa_id");
    exit;
} catch (Exception $e) {
    $mysqli->rollback();
    $_SESSION['error'] = "Error al finalizar: " . $e->getMessage();
    header("Location: ../mesas/mesa_detalle.php?mesa=$mesa_id");
    exit;
}
