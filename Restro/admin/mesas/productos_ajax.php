<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include('../config/config.php');

$categoria = $_GET['categoria'] ?? 'Todas';
$busqueda  = strtolower(trim($_GET['busqueda'] ?? ''));

$sql = "SELECT p.*, c.nombre_categoria 
        FROM rpos_products p
        LEFT JOIN rpos_categorias_productos c ON p.categoria_id = c.categoria_id
        WHERE 1=1";

$params = [];
$types  = "";

if ($categoria !== "Todas") {
    $sql .= " AND c.nombre_categoria = ?";
    $params[] = $categoria;
    $types .= "s";
}
if ($busqueda !== "") {
    $sql .= " AND LOWER(p.prod_name) LIKE ?";
    $params[] = "%$busqueda%";
    $types .= "s";
}

$sql .= " ORDER BY p.prod_name ASC";

$stmt = $mysqli->prepare($sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$productos = [];
while ($row = $result->fetch_assoc()) {
    $productos[] = $row;
}

header('Content-Type: application/json');
echo json_encode($productos);
