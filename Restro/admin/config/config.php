<?php
// Configuración de la base de datos
$dbuser = "root";
$dbpass = "";
$host = "localhost";
$db = "rposystem";
$base_path = '/RestaurantPOS/Restro/admin/';

$mysqli = new mysqli($host, $dbuser, $dbpass, $db);

// Verificar conexión
if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}

// Función para generar IDs únicos
if (!function_exists('generarID')) {
    function generarID($longitud = 12) {
        $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $id = '';
        for ($i = 0; $i < $longitud; $i++) {
            $id .= $caracteres[rand(0, strlen($caracteres) - 1)];
        }
        return $id;
    }
}

// Función para verificar si ya existe un ID en una tabla
if (!function_exists('verificarIDUnico')) {
    function verificarIDUnico($mysqli, $tabla, $campo, $id) {
        $query = "SELECT COUNT(*) as total FROM $tabla WHERE $campo = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_object();
        $stmt->close();
        
        return $row->total == 0;
    }
}

// Función para generar un ID único garantizado
if (!function_exists('generarIDUnico')) {
    function generarIDUnico($mysqli, $tabla, $campo, $longitud = 12) {
        $intentos = 0;
        $max_intentos = 10;
        
        do {
            $id = generarID($longitud);
            $es_unico = verificarIDUnico($mysqli, $tabla, $campo, $id);
            $intentos++;
            
            if ($intentos > $max_intentos) {
                // Si después de varios intentos no encuentra uno único, usar timestamp
                $id = uniqid() . rand(100, 999);
                break;
            }
        } while (!$es_unico);
        
        return $id;
    }
}
?>
