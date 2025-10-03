<?php
include('../config/config.php');

$mesa = $_GET['mesa'] ?? '';

if ($mesa === 'all') {
    $query = "SELECT m.numero_mesa, o.prod_name, o.prod_qty, o.prod_price
              FROM rpos_mesas m
              JOIN rpos_ordenes_mesas om ON m.mesa_id = om.mesa_id
              JOIN rpos_orders o ON om.order_id = o.order_id
              WHERE om.estado = 'Activa'
              ORDER BY m.numero_mesa";
} else {
    $query = "SELECT m.numero_mesa, o.prod_name, o.prod_qty, o.prod_price
              FROM rpos_mesas m
              JOIN rpos_ordenes_mesas om ON m.mesa_id = om.mesa_id
              JOIN rpos_orders o ON om.order_id = o.order_id
              WHERE m.mesa_id = ? AND om.estado = 'Activa'";
}

$stmt = $mysqli->prepare($query);
if ($mesa !== 'all') {
    $stmt->bind_param('s', $mesa);
}
$stmt->execute();
$result = $stmt->get_result();

$currentMesa = null;
$totalMesa = 0;

echo '<div class="table-responsive">';
while ($row = $result->fetch_object()) {
    if ($currentMesa !== $row->numero_mesa) {
        // Si no es la primera mesa, mostrar total anterior
        if ($currentMesa !== null) {
            echo '<tr class="table-active fw-bold"><td colspan="4" class="text-end">Total Mesa #' . $currentMesa . ':</td>';
            echo '<td>RD$ ' . number_format($totalMesa, 2) . '</td></tr>';
            echo '</tbody></table><hr>';
        }

        // Nueva mesa
        $currentMesa = $row->numero_mesa;
        $totalMesa = 0;

        echo '<h5 class="mt-4 text-primary">🪑 Mesa #' . $currentMesa . '</h5>';
        echo '<table class="table table-striped table-sm">';
        echo '<thead class="table-light"><tr><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th></tr></thead><tbody>';
    }

    $subtotal = $row->prod_price * $row->prod_qty;
    $totalMesa += $subtotal;

    echo '<tr>';
    echo '<td>' . htmlspecialchars($row->prod_name) . '</td>';
    echo '<td>' . $row->prod_qty . '</td>';
    echo '<td>RD$ ' . number_format($row->prod_price, 2) . '</td>';
    echo '<td>RD$ ' . number_format($subtotal, 2) . '</td>';
    echo '</tr>';
}

// Mostrar total de la última mesa
if ($currentMesa !== null) {
    echo '<tr class="table-active fw-bold"><td colspan="3" class="text-end">Total Mesa #' . $currentMesa . ':</td>';
    echo '<td>RD$ ' . number_format($totalMesa, 2) . '</td></tr>';
    echo '</tbody></table>';
}
echo '</div>';
