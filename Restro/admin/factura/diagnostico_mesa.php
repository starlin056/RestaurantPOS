fr<?php
// diagnostico_mesa.php
session_start();
include('../config/config.php');

$mesa_id = $_GET['mesa_id'] ?? 'm001';

echo "<h2>Diagnóstico de Mesa: $mesa_id</h2>";

// Verificar mesa
$query_mesa = "SELECT * FROM rpos_mesas WHERE mesa_id = ?";
$stmt = $mysqli->prepare($query_mesa);
$stmt->bind_param('s', $mesa_id);
$stmt->execute();
$mesa = $stmt->get_result()->fetch_object();
$stmt->close();

echo "<h3>Información de la Mesa:</h3>";
echo "<pre>" . print_r($mesa, true) . "</pre>";

// Verificar órdenes activas
$query_ordenes = "SELECT o.*, om.estado as orden_mesa_estado 
                 FROM rpos_orders o
                 JOIN rpos_ordenes_mesas om ON o.order_id = om.order_id
                 WHERE om.mesa_id = ? AND om.estado = 'Activa'";
$stmt = $mysqli->prepare($query_ordenes);
$stmt->bind_param('s', $mesa_id);
$stmt->execute();
$ordenes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo "<h3>Órdenes Activas:</h3>";
echo "<pre>" . print_r($ordenes, true) . "</pre>";

echo "<h3>Total de órdenes activas: " . count($ordenes) . "</h3>";
?>