<?php
session_start();
include('../config/config.php');
include('../config/checklogin.php');
check_login();

if (!isset($_GET['mesa'])) {
    echo "<div class='alert alert-danger'>Mesa no especificada</div>";
    exit;
}

$mesa_id = $_GET['mesa'];

// Info de mesa
$stmt = $mysqli->prepare("SELECT * FROM rpos_mesas WHERE mesa_id = ?");
$stmt->bind_param('s', $mesa_id);
$stmt->execute();
$mesa = $stmt->get_result()->fetch_object();
$stmt->close();

// Órdenes activas
$stmt = $mysqli->prepare("
    SELECT o.*, p.prod_img, p.prod_desc 
    FROM rpos_orders o
    JOIN rpos_products p ON o.prod_id = p.prod_id
    JOIN rpos_ordenes_mesas om ON o.order_id = om.order_id
    WHERE om.mesa_id = ? AND om.estado = 'Activa'
");
$stmt->bind_param('s', $mesa_id);
$stmt->execute();
$ordenes = $stmt->get_result();
$stmt->close();

// Configuración empresa
$stmt = $mysqli->prepare("SELECT * FROM rpos_configuracion WHERE config_id = 1");
$stmt->execute();
$config = $stmt->get_result()->fetch_object();
$stmt->close();

// Calcular totales
$subtotal = 0;
$items = [];
while ($orden = $ordenes->fetch_object()) {
    $total_item = $orden->prod_price * $orden->prod_qty;
    $items[] = [
        'nombre' => $orden->prod_name,
        'cantidad' => $orden->prod_qty,
        'precio' => $orden->prod_price,
        'total' => $total_item,
        'notas' => $orden->notas
    ];
    $subtotal += $total_item;
}

$itebis = $subtotal * ($config->itebis_porcentaje / 100);
$servicio = $subtotal * ($config->servicio_porcentaje / 100);
$total = $subtotal + $itebis + $servicio;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Prefactura Mesa <?= htmlspecialchars($mesa->numero_mesa) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .prefactura-container {
            font-family: 'Segoe UI', sans-serif;
            background: #fefefe;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        @media print {
            body * { visibility: hidden; }
            .prefactura-container, .prefactura-container * { visibility: visible; }
            .prefactura-container { position: absolute; left: 00; top: 00; width: 50%; }
            .btn-print { display: none; }
        }
    </style>
</head>
<body class="bg-light">

<div class="container my-4">
    <div class="prefactura-container p-4 border rounded shadow-sm bg-white">
        <!-- Encabezado -->
        <div class="row mb-4">
            <div class="col-md-8">
                <?php if (!empty($config->logo)): ?>
                    <img src="<?= $config->logo ?>" alt="Logo" class="mb-2" style="height:70px;">
                <?php endif; ?>
                <h5 class="fw-bold mb-1"><?= $config->nombre_empresa ?></h5>
                <small class="text-muted">
                    RNC: <?= $config->rnc ?> <br>
                    <?= $config->direccion ?> <br>
                    Tel: <?= $config->telefono ?>
                </small>
            </div>
            <div class="col-md-4 text-end">
                <h4 class="text-primary fw-bold">🧾 Prefactura</h4>
                <p class="mb-0">📅 <?= date('d/m/Y H:i') ?></p>
                <p class="mb-0">🪑 Mesa: <strong><?= $mesa->numero_mesa ?></strong> | 👥 Personas: <?= $mesa->num_personas ?></p>
            </div>
        </div>

        <!-- Detalle de productos -->
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>Cant.</th>
                        <th>Producto</th>
                        <th>Precio Unit.</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td class="text-center"><?= $item['cantidad'] ?></td>
                        <td>
                            <strong><?= $item['nombre'] ?></strong>
                            
                        </td>
                        <td class="text-end">RD$ <?= number_format($item['precio'], 2) ?></td>
                        <td class="text-end">RD$ <?= number_format($item['total'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Totales -->
        <div class="row justify-content-end mt-3">
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr><th>Subtotal:</th><td class="text-end">RD$ <?= number_format($subtotal, 2) ?></td></tr>
                    <tr><th>ITEBIS (<?= $config->itebis_porcentaje ?>%):</th><td class="text-end">RD$ <?= number_format($itebis, 2) ?></td></tr>
                    <tr><th>Servicio (<?= $config->servicio_porcentaje ?>%):</th><td class="text-end">RD$ <?= number_format($servicio, 2) ?></td></tr>
                    <tr class="fw-bold text-primary"><th>Total:</th><td class="text-end">RD$ <?= number_format($total, 2) ?></td></tr>
                </table>
            </div>
        </div>

        <!-- Nota -->
        <div class="alert alert-info mt-4">
            <i class="bi bi-info-circle-fill me-2"></i>
            Esta es una vista previa de la factura. Para generar la definitiva.
        </div>

        <!-- Botón imprimir -->
        <div class="text-end mt-3">
            <button class="btn btn-outline-primary btn-print" onclick="window.print()">
                🖨️ Imprimir Prefactura
            </button>
        </div>
    </div>
</div>

</body>
</html>
