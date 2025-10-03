<?php

/* include_once('../auditoria/funciones.php'); */


function check_login()
{
    global $mysqli;

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Verificar si $mysqli está definido
    if (!isset($mysqli)) {
        die("Error: Conexión a base de datos no establecida");
    }

    // Verificar sesión
    if (!isset($_SESSION['user_id']) || strlen($_SESSION['user_id']) == 0) {
        $host = $_SERVER['HTTP_HOST'];
        $uri  = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        $extra = "index.php";
        $_SESSION["user_id"] = "";
        header("Location: http://$host$uri/$extra");
        exit();
    }

    $user_id = $_SESSION['user_id'];

    // PRIMERO debemos obtener el rol de la base de datos, no de la sesión
    // Consultar según el tipo de usuario
    $query_admin = "SELECT admin_id, admin_name AS nombre, 'admin' as rol, estado, activation_expiry 
                   FROM rpos_admin WHERE admin_id = ?";
    $query_staff = "SELECT staff_id, staff_name AS nombre, rol, estado 
                   FROM rpos_staff WHERE staff_id = ?";

    // Primero intentar como admin
    $stmt = $mysqli->prepare($query_admin);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    $stmt->close();

    // Si no es admin, intentar como staff
    if (!$user) {
        $stmt = $mysqli->prepare($query_staff);
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        $stmt->close();
    }

    $host = $_SERVER['HTTP_HOST'];
    $uri  = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');

    if (!$user) {
        // Usuario no encontrado
        session_destroy();
        $extra = "index.php?error=Usuario no encontrado";
        header("Location: http://$host$uri/$extra");
        exit();
    }

    // ESTABLECER LAS VARIABLES DE SESIÓN CORRECTAMENTE
    $_SESSION['rol'] = $user['rol']; // ← ESTA LÍNEA FALTABA
    $_SESSION['nombre'] = $user['nombre'];

    // Si admin está inactivo → redirigir a activar cuenta
    if ($user['rol'] == "admin" && $user['estado'] == 'Inactivo') {
        $_SESSION['pending_admin_id'] = $user_id;
        $extra = "activate_admin.php";
        header("Location: http://$host$uri/$extra");
        exit();
    }

   

    // Al final, si el login es correcto
    //$_SESSION['rol'] = $user['rol'];
    // $_SESSION['nombre'] = $user['nombre'];

    // Registrar inicio de sesión
    /* registrar_auditoria("Inicio de sesión", "Usuario " . $user['nombre'] . " ha iniciado sesión"); */



     // Guardar ID del usuario en sesión según rol
    if ($user['rol'] == 'admin') {
        $_SESSION['admin_id'] = $user['admin_id'];
    } else {
        $_SESSION['staff_id'] = $user['staff_id'];
    } 
    
    // Validar expiración de clave para admins activos
    if ($user['rol'] == "admin" && !empty($user['activation_expiry'])) {
        $now = new DateTime();
        $expiry = new DateTime($user['activation_expiry']);
        if ($now > $expiry) {
            session_destroy();
            $extra = "index.php?error=Clave de activación expirada, contacte al administrador";
            header("Location: http://$host$uri/$extra");
            exit();
        }
    }

    // Staff inactivo
    if ($user['rol'] != "admin" && $user['estado'] != 'Activo') {
        session_destroy();
        $extra = "index.php?error=Cuenta inactiva, contacte al administrador";
        header("Location: http://$host$uri/$extra");
        exit();
    }
}
