<?php
// logout.php
session_start();
$_SESSION = [];
session_destroy();

// Redirigir correctamente al login
header('Location: http://localhost/RestaurantPOS/Restro/admin/index.php');
exit();
?>
