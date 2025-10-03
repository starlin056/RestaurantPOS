<?php
session_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/error.log'); 
include('../config/config.php');
include('../config/checklogin.php');
check_login();

// Verificar si se proporcionó un ID de delivery
if (!isset($_GET['delivery_id'])) {
    header('Location: delivery_lista.php');
    exit;
}

$delivery_id = $_GET['delivery_id'];

// Obtener información del pedido de delivery
$query = "SELECT d.*, c.*, s.staff_name as repartidor_nombre 
          FROM rpos_delivery_orders d 
          LEFT JOIN rpos_customers c ON d.cliente_id = c.customer_id 
          LEFT JOIN rpos_staff s ON d.repartidor_id = s.staff_id 
          WHERE d.delivery_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('s', $delivery_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Pedido no encontrado');
}

$pedido = $result->fetch_assoc();

// Obtener items del pedido
$items_query = $mysqli->prepare("SELECT * FROM rpos_delivery_items WHERE delivery_id = ?");
$items_query->bind_param('s', $delivery_id);
$items_query->execute();
$items_result = $items_query->get_result();
$items = $items_result->fetch_all(MYSQLI_ASSOC);

// Obtener configuración de la empresa
$config_query = $mysqli->query("SELECT * FROM rpos_configuracion LIMIT 1");
$config = $config_query->fetch_assoc();

// Procesar generación de factura si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mysqli->begin_transaction();

    try {
        $tipo_factura = $_POST['tipo_factura'];
        $metodo_pago = $_POST['metodo_pago'];

        // Generar número de factura
        $factura_id = generarIDUnico($mysqli, 'rpos_facturas', 'factura_id', 12);
        $factura_code = 'FACT-' . date('YmdHis');

        // Obtener secuencial para comprobante fiscal si es necesario
        $ncf = null;
        if ($tipo_factura === 'Fiscal') {
            // Aquí iría la lógica para generar NCF según el tipo de comprobante
            $ncf = 'B01' . str_pad(rand(1, 99999999), 8, '0', STR_PAD_LEFT);
        }

        // Insertar factura
        $stmt = $mysqli->prepare("INSERT INTO rpos_facturas 
                                (factura_id, factura_code, cliente_nombre, cliente_rnc, ncf, 
                                tipo_factura, estado, subtotal, itebis, servicio, total, 
                                fecha_factura, usuario_id) 
                                VALUES (?, ?, ?, ?, ?, ?, 'Pagada', ?, ?, ?, ?, NOW(), ?)");

        $stmt->bind_param(
            'ssssssdddds',
            $factura_id,
            $factura_code,
            $pedido['customer_name'],
            $pedido['rnc_cedula'],
            $ncf,
            $tipo_factura,
            $pedido['subtotal'],
            $pedido['impuestos'],
            $pedido['servicio'],
            $pedido['total'],
            $_SESSION['user_id']
        );

        if (!$stmt->execute()) {
            throw new Exception('Error al crear factura: ' . $stmt->error);
        }
        $stmt->close();

        // Registrar pago
        $pay_id = generarIDUnico($mysqli, 'rpos_payments', 'pay_id', 12);
        $pay_code = 'PAY-' . date('YmdHis');

        $stmt = $mysqli->prepare("INSERT INTO rpos_payments 
                                (pay_id, pay_code, order_code, customer_id, pay_amt, pay_method) 
                                VALUES (?, ?, ?, ?, ?, ?)");

        $stmt->bind_param(
            'ssssss',
            $pay_id,
            $pay_code,
            $factura_code,
            $pedido['cliente_id'],
            $pedido['total'],
            $metodo_pago
        );

        if (!$stmt->execute()) {
            throw new Exception('Error al registrar pago: ' . $stmt->error);
        }
        $stmt->close();

        // Actualizar estado del pedido de delivery
        $stmt = $mysqli->prepare("UPDATE rpos_delivery_orders 
                                SET facturado = 1, factura_id = ?, estado = 'Entregado' 
                                WHERE delivery_id = ?");

        $stmt->bind_param('ss', $factura_id, $delivery_id);

        if (!$stmt->execute()) {
            throw new Exception('Error al actualizar pedido: ' . $stmt->error);
        }
        $stmt->close();

        $mysqli->commit();

        // Redirigir a comprobante de factura
        header('Location: delivery_comprobante.php?factura_id=' . $factura_id);
        exit;
    } catch (Exception $e) {
        $mysqli->rollback();
        $error = $e->getMessage();
    }
}
?>
<?php require_once('../partials/_head.php');
?>


<body>
    <?php require_once('../partials/_sidebar.php'); ?>
    <div class="main-content">
        <?php require_once('../partials/_topnav.php'); ?>

        <div class="header bg-gradient-primary pb-6 pt-5 pt-md-6">
            <div class="container-fluid">
                <div class="header-body">
                    <div class="row align-items-center py-4">
                        <div class="col-lg-6 col-7">
                            <h1 class="text-white display-4">🛵 Creación de órdenes delivery</h1>
                            <p class="text-white mb-0">Gestión de pedidos para llevar</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>Facturación de Delivery</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="../dashboard">Inicio</a></li>
                                <li class="breadcrumb-item"><a href="delivery_lista.php">Delivery</a></li>
                                <li class="breadcrumb-item active">Facturación</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Información del Pedido</h3>
                                </div>
                                <div class="card-body">
                                    <dl class="row">
                                        <dt class="col-sm-4">Número de Orden:</dt>
                                        <dd class="col-sm-8"><?= $pedido['order_code'] ?></dd>

                                        <dt class="col-sm-4">Cliente:</dt>
                                        <dd class="col-sm-8"><?= $pedido['customer_name'] ?></dd>

                                        <dt class="col-sm-4">Teléfono:</dt>
                                        <dd class="col-sm-8"><?= $pedido['customer_phone'] ?></dd>

                                        <dt class="col-sm-4">Dirección:</dt>
                                        <dd class="col-sm-8"><?= $pedido['customer_address'] ?></dd>

                                        <dt class="col-sm-4">Repartidor:</dt>
                                        <dd class="col-sm-8"><?= $pedido['repartidor_nombre'] ?? 'Sin asignar' ?></dd>

                                        <dt class="col-sm-4">Estado:</dt>
                                        <dd class="col-sm-8">
                                            <span class="badge bg-<?=
                                                                    $pedido['estado'] == 'Entregado' ? 'success' : ($pedido['estado'] == 'En camino' ? 'primary' : ($pedido['estado'] == 'En preparación' ? 'info' : ($pedido['estado'] == 'Cancelado' ? 'danger' : 'warning'))) ?>">
                                                <?= $pedido['estado'] ?>
                                            </span>
                                        </dd>
                                    </dl>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <h3 class="card-title">Detalles de Facturación</h3>
                                </div>
                                <div class="card-body">
                                    <form method="POST" id="formFactura">
                                        <div class="form-group">
                                            <label>Tipo de Factura:</label>
                                            <select class="form-control" name="tipo_factura" required>
                                                <option value="Final">Factura Final (Consumidor)</option>
                                                <option value="Fiscal">Factura Fiscal (Con NCF)</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Método de Pago:</label>
                                            <select class="form-control" name="metodo_pago" required>
                                                <option value="Efectivo">Efectivo</option>
                                                <option value="Tarjeta">Tarjeta</option>
                                                <option value="Transferencia">Transferencia</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <button type="submit" class="btn btn-success btn-lg btn-block">
                                                <i class="fas fa-file-invoice-dollar mr-2"></i>
                                                Generar Factura y Registrar Pago
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Resumen de la Orden</h3>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Producto</th>
                                                    <th>Precio</th>
                                                    <th>Cantidad</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($items as $item): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($item['prod_name']) ?></td>
                                                        <td>$<?= number_format($item['prod_price'], 2) ?></td>
                                                        <td><?= $item['prod_qty'] ?></td>
                                                        <td>$<?= number_format($item['prod_price'] * $item['prod_qty'], 2) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="3" class="text-right"><strong>Subtotal:</strong></td>
                                                    <td><strong>$<?= number_format($pedido['subtotal'], 2) ?></strong></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" class="text-right">ITBIS (18%):</td>
                                                    <td>$<?= number_format($pedido['impuestos'], 2) ?></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" class="text-right">Servicio (10%):</td>
                                                    <td>$<?= number_format($pedido['servicio'], 2) ?></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" class="text-right">Cargo de entrega:</td>
                                                    <td>$<?= number_format($pedido['cargo_entrega'], 2) ?></td>
                                                </tr>
                                                <tr class="table-primary">
                                                    <td colspan="3" class="text-right"><strong>TOTAL:</strong></td>
                                                    <td><strong>$<?= number_format($pedido['total'], 2) ?></strong></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <?php require_once('../partials/_footer.php'); ?>
    </div>
    </div>

    <?php require_once('../partials/_scripts.php'); ?>
</body>

</html>