<?php
session_start();
include('../config/config.php');
include('../config/checklogin.php');
check_login();

// Procesar marcado como leído
if (isset($_POST['marcar_leido'])) {
    $notificacion_id = $_POST['notificacion_id'];
    $query = "UPDATE rpos_notificaciones SET estado = 'vista', vista_at = NOW() WHERE notificacion_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('s', $notificacion_id);
    $stmt->execute();
    exit(json_encode(['success' => true]));
}

// Procesar marcado como atendido
if (isset($_POST['marcar_atendido'])) {
    $notificacion_id = $_POST['notificacion_id'];
    $query = "UPDATE rpos_notificaciones SET estado = 'atendida', atendida_at = NOW() WHERE notificacion_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('s', $notificacion_id);
    $stmt->execute();
    exit(json_encode(['success' => true]));
}

// Obtener notificaciones no leídas
if (isset($_GET['obtener_notificaciones'])) {
    $query = "SELECT n.*, m.numero_mesa 
              FROM rpos_notificaciones n 
              LEFT JOIN rpos_mesas m ON n.mesa_id = m.mesa_id 
              WHERE n.estado = 'pendiente' 
              ORDER BY n.created_at DESC";
    $result = $mysqli->query($query);
    $notificaciones = [];
    while ($row = $result->fetch_assoc()) {
        $notificaciones[] = $row;
    }
    exit(json_encode($notificaciones));
}
?>