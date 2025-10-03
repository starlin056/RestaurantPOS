<?php
ob_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/error.log'); // asegúrate que exista la carpeta logs
error_reporting(E_ALL);
session_start();
include('config/config.php');
include('config/checklogin.php');
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

// Procesar registro de pago si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mysqli->begin_transaction();
    
    try {
        $metodo_pago = $_POST['metodo_pago'];
        $monto_recibido = floatval($_POST['monto_recibido']);
        $cambio = $monto_recibido - $pedido['total'];
        
        if ($monto_recibido < $pedido['total']) {
            throw new Exception('El monto recibido es menor al total del pedido');
        }
        
        // Registrar pago
        $pay_id = generarIDUnico($mysqli, 'rpos_payments', 'pay_id', 12);
        $pay_code = 'PAY-' . date('YmdHis');
        
        $stmt = $mysqli->prepare("INSERT INTO rpos_payments 
                                (pay_id, pay_code, order_code, customer_id, pay_amt, pay_method) 
                                VALUES (?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param('ssssss', 
            $pay_id,
            $pay_code,
            $pedido['order_code'],
            $pedido['cliente_id'],
            $pedido['total'],
            $metodo_pago
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Error al registrar pago: ' . $stmt->error);
        }
        $stmt->close();
        
        // Actualizar estado del pedido
        $stmt = $mysqli->prepare("UPDATE rpos_delivery_orders 
                                SET estado = 'Entregado', fecha_cobro = NOW(), 
                                metodo_pago_cobro = ?, fecha_pago = NOW() 
                                WHERE delivery_id = ?");
        
        $stmt->bind_param('ss', $metodo_pago, $delivery_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Error al actualizar pedido: ' . $stmt->error);
        }
        $stmt->close();
        
        $mysqli->commit();
        
        // Redirigir a comprobante
        header('Location: delivery_comprobante.php?delivery_id=' . $delivery_id . '&cambio=' . $cambio);
        exit;
        
    } catch (Exception $e) {
        $mysqli->rollback();
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include('../partials/head.php'); ?>
    <title>Registrar Pago Delivery - RPO System</title>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- Navbar -->
        <?php include('../partials/navbar.php'); ?>

        <!-- Sidebar -->
        <?php include('../partials/sidebar.php'); ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>Registrar Pago de Delivery</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="../dashboard">Inicio</a></li>
                                <li class="breadcrumb-item"><a href="delivery_lista.php">Delivery</a></li>
                                <li class="breadcrumb-item active">Registrar Pago</li>
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
                                        
                                        <dt class="col-sm-4">Total a Pagar:</dt>
                                        <dd class="col-sm-8"><strong>$<?= number_format($pedido['total'], 2) ?></strong></dd>
                                    </dl>
                                </div>
                            </div>
                            
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h3 class="card-title">Registrar Pago</h3>
                                </div>
                                <div class="card-body">
                                    <form method="POST" id="formPago">
                                        <div class="form-group">
                                            <label>Método de Pago:</label>
                                            <select class="form-control" name="metodo_pago" required id="metodoPago">
                                                <option value="Efectivo">Efectivo</option>
                                                <option value="Tarjeta">Tarjeta</option>
                                                <option value="Transferencia">Transferencia</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group" id="montoEfectivoGroup">
                                            <label>Monto Recibido:</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">$</span>
                                                </div>
                                                <input type="number" class="form-control" name="monto_recibido" 
                                                       step="0.01" min="<?= $pedido['total'] ?>" 
                                                       value="<?= $pedido['total'] ?>" id="montoRecibido">
                                            </div>
                                            <small class="form-text text-muted">Ingrese el monto recibido del cliente</small>
                                        </div>
                                        
                                        <div class="form-group" id="cambioGroup" style="display: none;">
                                            <label>Cambio a Entregar:</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">$</span>
                                                </div>
                                                <input type="text" class="form-control" id="cambio" readonly>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-success btn-lg btn-block">
                                                <i class="fas fa-check-circle mr-2"></i>
                                                Registrar Pago y Completar Entrega
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Resumen del Pedido</h3>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Concepto</th>
                                                    <th class="text-right">Monto</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Subtotal</td>
                                                    <td class="text-right">$<?= number_format($pedido['subtotal'], 2) ?></td>
                                                </tr>
                                                <tr>
                                                    <td>ITBIS (18%)</td>
                                                    <td class="text-right">$<?= number_format($pedido['impuestos'], 2) ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Servicio (10%)</td>
                                                    <td class="text-right">$<?= number_format($pedido['servicio'], 2) ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Cargo de entrega</td>
                                                    <td class="text-right">$<?= number_format($pedido['cargo_entrega'], 2) ?></td>
                                                </tr>
                                            </tbody>
                                            <tfoot>
                                                <tr class="table-primary">
                                                    <td><strong>TOTAL A PAGAR</strong></td>
                                                    <td class="text-right"><strong>$<?= number_format($pedido['total'], 2) ?></strong></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h3 class="card-title">Información del Repartidor</h3>
                                </div>
                                <div class="card-body">
                                    <?php if ($pedido['repartidor_nombre']): ?>
                                    <p><strong>Repartidor:</strong> <?= $pedido['repartidor_nombre'] ?></p>
                                    <p><strong>Estado:</strong> 
                                        <span class="badge bg-<?= 
                                            $pedido['estado'] == 'Entregado' ? 'success' : 
                                            ($pedido['estado'] == 'En camino' ? 'primary' : 
                                            ($pedido['estado'] == 'En preparación' ? 'info' : 
                                            ($pedido['estado'] == 'Cancelado' ? 'danger' : 'warning'))) ?>">
                                            <?= $pedido['estado'] ?>
                                        </span>
                                    </p>
                                    <?php else: ?>
                                    <p class="text-muted">No se ha asignado un repartidor</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Footer -->
        <?php include('../partials/footer.php'); ?>
    </div>

    <!-- Scripts -->
    <?php include('../partials/scripts.php'); ?>

    <script>
        $(document).ready(function() {
            // Calcular cambio cuando se modifica el monto recibido
            $('#montoRecibido').on('input', function() {
                calcularCambio();
            });
            
            // Mostrar/ocultar campos según método de pago
            $('#metodoPago').change(function() {
                toggleCamposPago();
            });
            
            // Inicializar estado de campos
            toggleCamposPago();
            calcularCambio();
        });
        
        function toggleCamposPago() {
            const metodo = $('#metodoPago').val();
            
            if (metodo === 'Efectivo') {
                $('#montoEfectivoGroup').show();
                $('#cambioGroup').show();
                $('#montoRecibido').attr('required', true);
            } else {
                $('#montoEfectivoGroup').hide();
                $('#cambioGroup').hide();
                $('#montoRecibido').removeAttr('required');
                $('#montoRecibido').val(<?= $pedido['total'] ?>);
            }
        }
        
        function calcularCambio() {
            const total = <?= $pedido['total'] ?>;
            const recibido = parseFloat($('#montoRecibido').val()) || 0;
            const cambio = recibido - total;
            
            if (cambio >= 0) {
                $('#cambio').val(cambio.toFixed(2));
            } else {
                $('#cambio').val('0.00');
            }
        }
    </script>
</body>
</html>