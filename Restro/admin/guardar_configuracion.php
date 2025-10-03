<?php
session_start();
include('config/config.php');
include('config/check_login.php');
check_login();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $itebis = floatval($_POST['itebis']);
    $servicio = floatval($_POST['servicio']);
    $rnc = $_POST['rnc'];
    
    try {
        $query = "UPDATE rpos_configuracion SET 
                  itebis_porcentaje = ?, servicio_porcentaje = ?, rnc = ?
                  WHERE config_id = 1";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('dds', $itebis, $servicio, $rnc);
        $stmt->execute();
        $stmt->close();
        
        $_SESSION['success'] = "Configuración actualizada correctamente";
    } catch (Exception $e) {
        $_SESSION['err'] = "Error al guardar configuración: " . $e->getMessage();
    }
}

header("Location: mesa_pagos.php");
exit;