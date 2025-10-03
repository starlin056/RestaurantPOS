<?php
$DB_host = "localhost";
$DB_user = "root";
$DB_pass = "";
$DB_name = "rposystem";

try {
    $DB_con = new PDO("mysql:host={$DB_host};dbname={$DB_name}", $DB_user, $DB_pass);
    $DB_con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    $e->getMessage();
}

// Función para generar IDs con PDO (si necesitas usar PDO)
function generarIDPDO($longitud = 12) {
    $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $id = '';
    for ($i = 0; $i < $longitud; $i++) {
        $id .= $caracteres[rand(0, strlen($caracteres) - 1)];
    }
    return $id;
}
?>