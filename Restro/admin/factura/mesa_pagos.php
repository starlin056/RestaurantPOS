<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ob_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/error.log');
error_reporting(E_ALL);
include('../config/config.php');
include('../config/checklogin.php');
check_login();

$err = null;
$success = null;

// Verificar que hay caja abierta para el usuario actual
$query = "SELECT * FROM rpos_caja WHERE estado = 'Abierta' AND usuario_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('s', $_SESSION['user_id']);
$stmt->execute();
$caja_abierta = $stmt->get_result()->fetch_object();
$stmt->close();

// Obtener configuración de la empresa
$query = "SELECT * FROM rpos_configuracion WHERE config_id = 1";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$config = $stmt->get_result()->fetch_object();
$stmt->close();

// Obtener clientes para el modal
$query_clientes = "SELECT customer_id, customer_name, rnc_cedula FROM rpos_customers ORDER BY customer_name";
$clientes_result = $mysqli->query($query_clientes);

require_once('../partials/_head.php');
?>

<body>
    <?php require_once('../partials/_sidebar.php'); ?>

    <div class="main-content">
        <?php require_once('../partials/_topnav.php'); ?>

        <!-- Header moderno -->
        <div class="header bg-gradient-primary pb-8 pt-5 pt-md-8">
            <div class="container-fluid">
                <div class="header-body">
                    <div class="row align-items-center py-4">
                        <div class="col-lg-6 col-7">
                            <h1 class="text-white display-3">💳 Gestión de Pagos</h1>
                            <p class="text-white mb-0">Sistema de facturación</p>
                        </div>
                        <div class="col-lg-6 col-5 text-right">
                            <div class="d-flex justify-content-end">
                                <?php if ($caja_abierta): ?>
                                    <a href="caja_cierre.php" class="btn btn-warning mr-2">
                                        <i class="fas fa-cash-register"></i> Cerrar Caja
                                    </a>
                                <?php else: ?>
                                    <a href="caja_apertura.php" class="btn btn-success mr-2">
                                        <i class="fas fa-cash-register"></i> Abrir Caja
                                    </a>
                                <?php endif; ?>
                                <a href="../mesas.php" class="btn btn-neutral">
                                    <i class="fas fa-chair"></i> Ver Mesas
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid mt--7">
            <!-- Alertas flotantes -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show floating-alert" role="alert">
                    <span class="alert-icon"><i class="ni ni-like-2"></i></span>
                    <span class="alert-text"><?php echo $_SESSION['success'];
                                                unset($_SESSION['success']); ?></span>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['err'])): ?>
                <div class="alert alert-danger alert-dismissible fade show floating-alert" role="alert">
                    <span class="alert-icon"><i class="ni ni-support-16"></i></span>
                    <span class="alert-text"><?php echo $_SESSION['err'];
                                                unset($_SESSION['err']); ?></span>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Estado de Caja -->
            <?php if ($caja_abierta): ?>
                <div class="alert alert-info mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="alert-heading"><i class="fas fa-cash-register"></i> Caja Abierta</h4>
                            <p class="mb-0">
                                <strong>Monto Inicial:</strong> RD$ <?php echo number_format($caja_abierta->monto_inicial, 2); ?> |
                                <strong>Ventas Totales:</strong> RD$ <?php echo number_format($caja_abierta->total_ventas, 2); ?> |
                                <strong>Abierta desde:</strong> <?php echo date('d/m/Y H:i', strtotime($caja_abierta->fecha_apertura)); ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-right">
                            <a href="caja_cierre.php" class="btn btn-sm btn-success">
                                <i class="fas fa-door-closed"></i> Cerrar Caja
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-warning mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Caja Cerrada</h4>
                            <p class="mb-0">Debe abrir caja para poder procesar pagos y facturaciones.</p>
                        </div>
                        <div class="col-md-4 text-right">
                            <a href="caja_apertura.php" class="btn btn-sm btn-success">
                                <i class="fas fa-door-open"></i> Abrir Caja
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Dashboard de métricas -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card card-stats bg-gradient-info">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-white mb-0">Mesas Activas</h5>
                                    <span class="h2 font-weight-bold text-white mb-0">
                                        <?php
                                        $query = "SELECT COUNT(DISTINCT mesa_id) as total FROM rpos_ordenes_mesas WHERE estado = 'Activa'";
                                        $stmt = $mysqli->prepare($query);
                                        $stmt->execute();
                                        echo $stmt->get_result()->fetch_object()->total;
                                        $stmt->close();
                                        ?>
                                    </span>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-white text-info rounded-circle shadow">
                                        <i class="fas fa-utensils"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card card-stats bg-gradient-success">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-white mb-0">Por Facturar</h5>
                                    <span class="h2 font-weight-bold text-white mb-0">
                                        <?php
                                        $query = "SELECT COUNT(*) as total FROM rpos_mesas WHERE estado = 'Lista para facturar'";
                                        $stmt = $mysqli->prepare($query);
                                        $stmt->execute();
                                        echo $stmt->get_result()->fetch_object()->total;
                                        $stmt->close();
                                        ?>
                                    </span>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-white text-success rounded-circle shadow">
                                        <i class="fas fa-cash-register"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card card-stats bg-gradient-warning">
                        <div class= "card-body"> 
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-white mb-0">Ventas Hoy</h5>
                                    <span class="h2 font-weight-bold text-white mb-0">
                                        <?php
                                        $query = "SELECT COUNT(*) as total FROM rpos_facturas WHERE DATE(fecha_factura) = CURDATE()";
                                        $stmt = $mysqli->prepare($query);
                                        $stmt->execute();
                                        echo $stmt->get_result()->fetch_object()->total;
                                        $stmt->close();
                                        ?>
                                    </span>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-white text-warning rounded-circle shadow">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card card-stats bg-gradient-danger">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-white mb-0">Total Recaudado</h5>
                                    <span class="h2 font-weight-bold text-white mb-0">
                                        <?php
                                        $query = "SELECT COALESCE(SUM(total), 0) as total FROM rpos_facturas WHERE DATE(fecha_factura) = CURDATE()";
                                        $stmt = $mysqli->prepare($query);
                                        $stmt->execute();
                                        echo number_format($stmt->get_result()->fetch_object()->total, 2);
                                        $stmt->close();
                                        ?>
                                    </span>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-white text-danger rounded-circle shadow">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vista tipo cocina - Mesas y Delivery listos para facturar -->
            <div class="row">
                <div class="col-12">
                    <div class="card bg-secondary shadow">
                        <div class="card-header bg-white border-0">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <h3 class="mb-0">🍽️ Mesas y Delivery Listos para Facturar</h3>
                                </div>
                                <div class="col-4 text-right">
                                    <button class="btn btn-sm btn-outline-primary" id="btnVistaPreviaGlobal">
                                        <i class="fas fa-receipt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <?php if (!$caja_abierta): ?>
                                <div class="alert alert-warning text-center">
                                    <i class="fas fa-exclamation-triangle"></i> La caja está cerrada. De must open cash register to be able to invoice.
                                </div>
                            <?php endif; ?>

                            <!-- Pestañas para Mesas y Delivery -->
                            <ul class="nav nav-pills mb-4" id="pills-tab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="pills-mesas-tab" data-toggle="pill" href="#pills-mesas" role="tab">
                                        <i class="fas fa-chair"></i> Mesas
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="pills-delivery-tab" data-toggle="pill" href="#pills-delivery" role="tab">
                                        <i class="fas fa-motorcycle"></i> Delivery
                                    </a>
                                </li>
                            </ul>

                            <div class="tab-content" id="pills-tabContent">
                                <!-- Pestaña de Mesas -->
                                <div class="tab-pane fade show active" id="pills-mesas" role="tabpanel">
                                    <!-- Vista de cuadrícula (tipo cocina) para Mesas -->
                                    <div class="row" id="vistaCuadriculaMesas">
                                        <?php
                                        $query = "SELECT m.mesa_id, m.numero_mesa, m.ubicacion, m.num_personas, 
                                                  COUNT(o.order_id) as num_ordenes,
                                                  SUM(o.prod_price * o.prod_qty) as subtotal
                                                  FROM rpos_mesas m
                                                  JOIN rpos_ordenes_mesas om ON m.mesa_id = om.mesa_id
                                                  JOIN rpos_orders o ON om.order_id = o.order_id
                                                  WHERE m.estado = 'Lista para facturar' AND om.estado = 'Activa'
                                                  GROUP BY m.mesa_id
                                                  ORDER BY m.numero_mesa";
                                        $stmt = $mysqli->prepare($query);
                                        $stmt->execute();
                                        $mesas = $stmt->get_result();

                                        while ($mesa = $mesas->fetch_object()):
                                            $subtotal = $mesa->subtotal;
                                            $itebis = $subtotal * ($config->itebis_porcentaje / 100);
                                            $servicio = $subtotal * ($config->servicio_porcentaje / 100);
                                            $total = $subtotal + $itebis + $servicio;
                                        ?>
                                            <div class="col-xl-3 col-md-4 col-sm-6 mb-4">
                                                <div class="card card-lift--hover shadow border-0 mesa-card">
                                                    <div class="card-header bg-gradient-primary">
                                                        <h4 class="text-white text-center mb-0">Mesa #<?php echo $mesa->numero_mesa; ?></h4>
                                                    </div>
                                                    <div class="card-body py-3">
                                                        <div class="text-center">
                                                            <span class="badge badge-info mb-2"><?php echo $mesa->ubicacion; ?></span>
                                                            <div class="d-flex justify-content-around mb-3">
                                                                <small class="text-muted">👥 <?php echo $mesa->num_personas; ?> personas</small>
                                                                <small class="text-muted">📦 <?php echo $mesa->num_ordenes; ?> órdenes</small>
                                                            </div>
                                                            <h3 class="text-success mb-3">RD$ <?php echo number_format($total, 2); ?></h3>

                                                            <div class="btn-group w-100" role="group">
                                                                <a class="btn btn-sm btn-info vista-previa-btn"
                                                                    href="generar_prefactura.php?mesa=<?php echo $mesa->mesa_id; ?>"
                                                                    target="_blank"
                                                                    title="Ver prefactura">
                                                                    <i class="fas fa-eye"></i> Ver prefactura
                                                                </a>

                                                                <button class="btn btn-sm btn-success facturar-btn"
                                                                    data-mesa-id="<?php echo $mesa->mesa_id; ?>"
                                                                    data-mesa-numero="<?php echo $mesa->numero_mesa; ?>"
                                                                    data-mesa-total="<?php echo $total; ?>"
                                                                    data-tipo="mesa"
                                                                    title="Facturar mesa"
                                                                    <?php echo !$caja_abierta ? 'disabled' : ''; ?>>
                                                                    <i class="fas fa-cash-register"></i> Facturar
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                        <?php $stmt->close(); ?>

                                        <?php if ($mesas->num_rows === 0): ?>
                                            <div class="col-12 text-center py-5">
                                                <i class="fas fa-chair fa-3x text-muted mb-3"></i>
                                                <h5 class="text-muted">No hay mesas listas para facturar</h5>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Pestaña de Delivery -->
                                <div class="tab-pane fade" id="pills-delivery" role="tabpanel">
                                    <!-- Vista de cuadrícula (tipo cocina) para Delivery -->
                                    <div class="row" id="vistaCuadriculaDelivery">
                                        <?php
                                        $query = "SELECT d.delivery_id, d.order_code, d.customer_name, d.customer_phone, 
                                                 d.subtotal, d.impuestos, d.servicio, d.cargo_entrega, d.total,
                                                 COUNT(di.item_id) as num_items
                                                 FROM rpos_delivery_orders d
                                                 JOIN rpos_delivery_items di ON d.delivery_id = di.delivery_id
                                                 WHERE d.estado = 'Lista para facturar'
                                                 GROUP BY d.delivery_id
                                                 ORDER BY d.created_at DESC";
                                        $stmt = $mysqli->prepare($query);
                                        $stmt->execute();
                                        $deliveries = $stmt->get_result();

                                        while ($delivery = $deliveries->fetch_object()):
                                            $total = $delivery->total;
                                            // Obtener últimos 4 dígitos del order_code
                                            $order_code = $delivery->order_code;
                                            preg_match_all('/\d/', $order_code, $digitos);
                                            $todos_digitos = implode('', $digitos[0]);
                                            $ultimos_4_digitos = substr($todos_digitos, -4);
                                            if (strlen($ultimos_4_digitos) < 4) {
                                                $ultimos_4_digitos = substr($order_code, -4);
                                            }
                                        ?>
                                            <div class="col-xl-3 col-md-4 col-sm-6 mb-4">
                                                <div class="card card-lift--hover shadow border-0 delivery-card">
                                                    <div class="card-header bg-gradient-info">
                                                        <h4 class="text-white text-center mb-0">Delivery #<?php echo $ultimos_4_digitos; ?></h4>
                                                    </div>
                                                    <div class="card-body py-3">
                                                        <div class="text-center">
                                                            <span class="badge badge-info mb-2"><?php echo $delivery->customer_phone; ?></span>
                                                            <div class="d-flex justify-content-around mb-3">
                                                                <small class="text-muted">👤 <?php echo $delivery->customer_name; ?></small>
                                                                <small class="text-muted">📦 <?php echo $delivery->num_items; ?> items</small>
                                                            </div>
                                                            <h3 class="text-success mb-3">RD$ <?php echo number_format($total, 2); ?></h3>

                                                            <div class="btn-group w-100" role="group">
                                                                <a class="btn btn-sm btn-info vista-previa-btn"
                                                                    href="generar_prefactura_delivery.php?delivery_id=<?php echo $delivery->delivery_id; ?>"
                                                                    target="_blank"
                                                                    title="Ver prefactura">
                                                                    <i class="fas fa-eye"></i> Ver prefactura
                                                                </a>

                                                                <button class="btn btn-sm btn-success facturar-delivery-btn"
                                                                    data-delivery-id="<?php echo $delivery->delivery_id; ?>"
                                                                    data-delivery-codigo="<?php echo $ultimos_4_digitos; ?>"
                                                                    data-delivery-total="<?php echo $total; ?>"
                                                                    data-delivery-cliente="<?php echo $delivery->customer_name; ?>"
                                                                    data-tipo="delivery"
                                                                    title="Facturar delivery"
                                                                    <?php echo !$caja_abierta ? 'disabled' : ''; ?>>
                                                                    <i class="fas fa-cash-register"></i> Facturar
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                        <?php $stmt->close(); ?>

                                        <?php if ($deliveries->num_rows === 0): ?>
                                            <div class="col-12 text-center py-5">
                                                <i class="fas fa-motorcycle fa-3x text-muted mb-3"></i>
                                                <h5 class="text-muted">No hay delivery listos para facturar</h5>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal para Facturar Delivery -->
            <div class="modal fade" id="modalFacturarDelivery" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <form method="post" action="generar_factura_delivery.php" id="formFacturaDelivery" target="_blank">
                            <div class="modal-header bg-gradient-info">
                                <h5 class="modal-title text-white">🛵 Facturar Delivery #<span id="modalDeliveryCodigo"></span></h5>
                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>

                            <div class="modal-body">
                                <input type="hidden" name="delivery_id" id="modalDeliveryId">
                                <input type="hidden" name="comprobante_tipo" id="comprobanteTipoDelivery" value="B02">

                                <!-- Información del cliente -->
                                <div class="alert alert-info">
                                    <h6 class="alert-heading">👤 Información del Cliente</h6>
                                    <p class="mb-1"><strong>Cliente:</strong> <span id="modalDeliveryCliente"></span></p>
                                    <p class="mb-0"><strong>Total Delivery:</strong> RD$ <span id="modalDeliveryTotal"></span></p>
                                </div>

                                <!-- Tipo de factura -->
                                <div class="form-group">
                                    <label>Tipo de Factura *</label>
                                    <select class="form-control" name="tipo_factura" id="tipoFacturaDelivery" required>
                                        <option value="Final" data-comprobante="B02">Factura consumidor Final (B02)</option>
                                        <option value="Fiscal" data-comprobante="B01">Factura con Crédito Fiscal (B01)</option>
                                        <option value="Credito" data-comprobante="">Crédito</option>
                                    </select>
                                </div>

                                <!-- Cliente -->
                                <div class="form-group" id="clienteGroupDelivery" style="display: none;">
                                    <label>Seleccionar Cliente *</label>
                                    <select class="form-control" name="cliente_id" id="clienteSelectDelivery">
                                        <option value="">Seleccione un cliente</option>
                                        <?php
                                        $clientes_result->data_seek(0);
                                        while ($cliente = $clientes_result->fetch_object()): ?>
                                            <option value="<?= $cliente->customer_id ?>">
                                                <?= $cliente->customer_name . ' - ' . $cliente->rnc_cedula ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <!-- Método de pago -->
                                <div class="form-group">
                                    <label>Método de Pago *</label>
                                    <div class="btn-group btn-group-toggle w-100" data-toggle="buttons" id="metodoPagoGroupDelivery">
                                        <label class="btn btn-outline-primary metodo-pago-btn">
                                            <input type="radio" name="metodo_pago" value="Efectivo" required>
                                            <i class="fas fa-money-bill-wave mr-2"></i>Efectivo
                                        </label>
                                        <label class="btn btn-outline-primary metodo-pago-btn">
                                            <input type="radio" name="metodo_pago" value="Tarjeta Débito">
                                            <i class="fas fa-credit-card mr-2"></i>Débito
                                        </label>
                                        <label class="btn btn-outline-primary metodo-pago-btn">
                                            <input type="radio" name="metodo_pago" value="Tarjeta Crédito">
                                            <i class="fas fa-credit-card mr-2"></i>Crédito
                                        </label>
                                        <label class="btn btn-outline-primary metodo-pago-btn">
                                            <input type="radio" name="metodo_pago" value="Transferencia">
                                            <i class="fas fa-exchange-alt mr-2"></i>Transferencia
                                        </label>
                                    </div>
                                </div>

                                <!-- Monto recibido -->
                                <div class="form-group" id="montoEfectivoGroupDelivery" style="display: none;">
                                    <label>Monto Recibido *</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">RD$</span>
                                        </div>
                                        <input type="number" step="0.01" class="form-control" name="monto_recibido" id="montoRecibidoDelivery" placeholder="0.00" min="0">
                                    </div>
                                    <small class="form-text text-muted">Ingrese el monto que recibió del cliente</small>
                                </div>

                                <!-- Vuelto -->
                                <div class="alert alert-warning" id="vueltoSectionDelivery" style="display: none;">
                                    <h6 class="alert-heading">💵 Vuelto a Entregar</h6>
                                    <h3 class="text-center mb-0">RD$ <span id="vueltoMontoDelivery">0.00</span></h3>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                <button type="submit" id="btnGenerarFacturaDelivery" class="btn btn-success">Generar Factura</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal para Facturar Mesa -->
            <div class="modal fade" id="modalFacturarMesa" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <form method="post" action="generar_factura.php" id="formFacturaMesa" target="_blank">
                            <div class="modal-header bg-gradient-primary">
                                <h5 class="modal-title text-white">🍽️ Facturar Mesa #<span id="modalMesaNumero"></span></h5>
                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>

                            <div class="modal-body">
                                <input type="hidden" name="mesa_id" id="modalMesaId">
                                <input type="hidden" name="comprobante_tipo" id="comprobanteTipoMesa" value="B02">

                                <!-- Información de la mesa -->
                                <div class="alert alert-info">
                                    <h6 class="alert-heading">🍽️ Información de la Mesa</h6>
                                    <p class="mb-0"><strong>Total Mesa:</strong> RD$ <span id="modalMesaTotal"></span></p>
                                </div>

                                <!-- Tipo de factura -->
                                <div class="form-group">
                                    <label>Tipo de Factura *</label>
                                    <select class="form-control" name="tipo_factura" id="tipoFacturaMesa" required>
                                        <option value="Final" data-comprobante="B02">Factura consumidor Final (B02)</option>
                                        <option value="Fiscal" data-comprobante="B01">Factura con Crédito Fiscal (B01)</option>
                                        <option value="Credito" data-comprobante="">Crédito</option>
                                    </select>
                                </div>

                                <!-- Cliente -->
                                <div class="form-group" id="clienteGroupMesa" style="display: none;">
                                    <label>Seleccionar Cliente *</label>
                                    <select class="form-control" name="cliente_id" id="clienteSelectMesa">
                                        <option value="">Seleccione un cliente</option>
                                        <?php
                                        $clientes_result->data_seek(0);
                                        while ($cliente = $clientes_result->fetch_object()): ?>
                                            <option value="<?= $cliente->customer_id ?>">
                                                <?= $cliente->customer_name . ' - ' . $cliente->rnc_cedula ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <!-- Método de pago -->
                                <div class="form-group">
                                    <label>Método de Pago *</label>
                                    <div class="btn-group btn-group-toggle w-100" data-toggle="buttons" id="metodoPagoGroupMesa">
                                        <label class="btn btn-outline-primary metodo-pago-btn">
                                            <input type="radio" name="metodo_pago" value="Efectivo" required>
                                            <i class="fas fa-money-bill-wave mr-2"></i>Efectivo
                                        </label>
                                        <label class="btn btn-outline-primary metodo-pago-btn">
                                            <input type="radio" name="metodo_pago" value="Tarjeta Débito">
                                            <i class="fas fa-credit-card mr-2"></i>Débito
                                        </label>
                                        <label class="btn btn-outline-primary metodo-pago-btn">
                                            <input type="radio" name="metodo_pago" value="Tarjeta Crédito">
                                            <i class="fas fa-credit-card mr-2"></i>Crédito
                                        </label>
                                        <label class="btn btn-outline-primary metodo-pago-btn">
                                            <input type="radio" name="metodo_pago" value="Transferencia">
                                            <i class="fas fa-exchange-alt mr-2"></i>Transferencia
                                        </label>
                                    </div>
                                </div>

                                <!-- Monto recibido -->
                                <div class="form-group" id="montoEfectivoGroupMesa" style="display: none;">
                                    <label>Monto Recibido *</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">RD$</span>
                                        </div>
                                        <input type="number" step="0.01" class="form-control" name="monto_recibido" id="montoRecibidoMesa" placeholder="0.00" min="0">
                                    </div>
                                    <small class="form-text text-muted">Ingrese el monto que recibió del cliente</small>
                                </div>

                                <!-- Vuelto -->
                                <div class="alert alert-warning" id="vueltoSectionMesa" style="display: none;">
                                    <h6 class="alert-heading">💵 Vuelto a Entregar</h6>
                                    <h3 class="text-center mb-0">RD$ <span id="vueltoMontoMesa">0.00</span></h3>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                <button type="submit" id="btnGenerarFacturaMesa" class="btn btn-success">Generar Factura</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sección de facturas recientes -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card bg-default shadow">
                        <div class="card-header bg-transparent">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <h3 class="mb-0 text-white">📋 Facturas Recientes</h3>
                                </div>
                                <div class="col-4 text-right">
                                    <a href="factura/lista.php" class="btn btn-sm btn-outline-light">
                                        <i class="fas fa-list"></i> Ver todas
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-dark table-flush">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Factura</th>
                                            <th>Mesa</th>
                                            <th>Cliente</th>
                                            <th>Total</th>
                                            <th>Fecha</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query = "SELECT * FROM rpos_facturas ORDER BY fecha_factura DESC LIMIT 10";
                                        $stmt = $mysqli->prepare($query);
                                        $stmt->execute();
                                        $facturas = $stmt->get_result();

                                        while ($factura = $facturas->fetch_object()):
                                        ?>
                                            <tr>
                                                <td class="font-weight-bold"><?php echo $factura->factura_code; ?></td>
                                                <td>
                                                    <?php
                                                    if ($factura->numero_mesa == 0) {
                                                        echo "Delivery";
                                                    } else {
                                                        echo "Mesa #" . $factura->numero_mesa;
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    if (empty($factura->cliente_nombre) || $factura->cliente_nombre == "0") {
                                                        echo "Consumidor Final";
                                                    } else {
                                                        echo $factura->cliente_nombre;
                                                    }
                                                    ?>
                                                </td>
                                                <td class="text-success">RD$ <?php echo number_format($factura->total, 2); ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($factura->fecha_factura)); ?></td>
                                                <td>
                                                    <a href="imprimir_factura.php?id=<?php echo $factura->factura_id; ?>"
                                                        target="_blank"
                                                        class="btn btn-sm btn-info"
                                                        title="Reimprimir factura">
                                                        <i class="fas fa-print"></i> Reimprimir
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                        <?php $stmt->close(); ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <?php require_once('../partials/_scripts.php'); ?>
    </div>
    <?php require_once('../partials/_footer.php'); ?>

    <style>
        .mesa-card,
        .delivery-card {
            transition: all 0.3s ease;
            border-radius: 15px;
        }

        .mesa-card:hover,
        .delivery-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .floating-alert {
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
        }

        .card-stats .icon-shape {
            width: 3rem;
            height: 3rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .bg-gradient-primary {
            background: linear-gradient(87deg, #5e72e4 0, #825ee4 100%) !important;
        }

        .bg-gradient-info {
            background: linear-gradient(87deg, #11cdef 0, #1171ef 100%) !important;
        }

        .bg-gradient-success {
            background: linear-gradient(87deg, #2dce89 0, #2dcecc 100%) !important;
        }

        .bg-gradient-warning {
            background: linear-gradient(87deg, #fb6340 0, #fbb140 100%) !important;
        }

        .bg-gradient-danger {
            background: linear-gradient(87deg, #f5365c 0, #f56036 100%) !important;
        }

        /* Estilos para botones de método de pago */
        .metodo-pago-btn {
            border: 2px solid #dee2e6;
            margin: 2px;
            border-radius: 8px !important;
        }

        .metodo-pago-btn.active {
            border-color: #5e72e4;
            background-color: #5e72e4;
            color: white;
        }

        .metodo-pago-btn:hover {
            border-color: #5e72e4;
        }

        /* Mejora para botones de acción */
        .btn-group .btn {
            border-radius: 6px;
            margin: 0 2px;
        }

        .vista-previa-btn {
            background-color: #11cdef;
            border-color: #11cdef;
            color: white;
        }

        .vista-previa-btn:hover {
            background-color: #0da5c0;
            border-color: #0da5c0;
        }

        .facturar-btn {
            background-color: #2dce89;
            border-color: #2dce89;
            color: white;
        }

        .facturar-btn:hover {
            background-color: #24a46d;
            border-color: #24a46d;
        }

        .facturar-btn:disabled {
            background-color: #6c757d;
            border-color: #6c757d;
            cursor: not-allowed;
        }

        /*mensaje flotante*/
        .floating-alert {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 350px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            border-radius: 10px;
            border: none;
        }

        .floating-alert .alert-icon {
            font-size: 24px;
            margin-right: 15px;
        }

        .floating-alert .alert-heading {
            font-size: 18px;
            margin-bottom: 5px;
            color: #2dce89;
        }
    </style>

    <script>
        $(document).ready(function() {
            // Función para mostrar mensaje de éxito
            function mostrarMensajeExito() {
                // Crear alerta de éxito
                var alerta = $('<div class="alert alert-success alert-dismissible fade show floating-alert" role="alert">' +
                    '<span class="alert-icon"><i class="ni ni-like-2"></i></span>' +
                    '<span class="alert-text">Factura generada correctamente</span>' +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                    '<span aria-hidden="true">&times;</span>' +
                    '</button>' +
                    '</div>');

                // Agregar al DOM
                $('body').append(alerta);

                // Auto-eliminar después de 5 segundos
                setTimeout(function() {
                    alerta.alert('close');
                }, 5000);
            }

            // Manejar clic en botón de facturar mesa
            $(document).on('click', '.facturar-btn', function() {
                var mesaId = $(this).data('mesa-id');
                var mesaNumero = $(this).data('mesa-numero');
                var mesaTotal = parseFloat($(this).data('mesa-total'));

                $('#modalMesaId').val(mesaId);
                $('#modalMesaNumero').text(mesaNumero);
                $('#modalMesaTotal').text(mesaTotal.toFixed(2));

                // Resetear selecciones
                $('#metodoPagoGroupMesa .btn').removeClass('active');
                $('#montoRecibidoMesa').val('');
                $('#vueltoSectionMesa').hide();
                $('#tipoFacturaMesa').val('Final');
                $('#clienteGroupMesa').hide();
                $('#clienteSelectMesa').removeAttr('required');
                $('#montoEfectivoGroupMesa').hide();
                $('#montoRecibidoMesa').removeAttr('required');

                $('#modalFacturarMesa').modal('show');
            });

            // Manejar clic en botón de facturar delivery
            $(document).on('click', '.facturar-delivery-btn', function() {
                var deliveryId = $(this).data('delivery-id');
                var deliveryCodigo = $(this).data('delivery-codigo');
                var deliveryTotal = parseFloat($(this).data('delivery-total'));
                var deliveryCliente = $(this).data('delivery-cliente');

                $('#modalDeliveryId').val(deliveryId);
                $('#modalDeliveryCodigo').text(deliveryCodigo);
                $('#modalDeliveryTotal').text(deliveryTotal.toFixed(2));
                $('#modalDeliveryCliente').text(deliveryCliente);

                // Resetear selecciones
                $('#metodoPagoGroupDelivery .btn').removeClass('active');
                $('#montoRecibidoDelivery').val('');
                $('#vueltoSectionDelivery').hide();
                $('#tipoFacturaDelivery').val('Final');
                $('#clienteGroupDelivery').hide();
                $('#clienteSelectDelivery').removeAttr('required');
                $('#montoEfectivoGroupDelivery').hide();
                $('#montoRecibidoDelivery').removeAttr('required');

                $('#modalFacturarDelivery').modal('show');
            });

            // Lógica para el modal de mesa
            $('#tipoFacturaMesa').change(function() {
                let comprobante = $(this).find(':selected').data('comprobante') || '';
                $('#comprobanteTipoMesa').val(comprobante);

                if ($(this).val() === 'Final') {
                    $('#clienteGroupMesa').hide();
                    $('#clienteSelectMesa').removeAttr('required');
                } else {
                    $('#clienteGroupMesa').show();
                    $('#clienteSelectMesa').attr('required', 'required');
                }
            });

            // Lógica para el modal de delivery
            $('#tipoFacturaDelivery').change(function() {
                let comprobante = $(this).find(':selected').data('comprobante') || '';
                $('#comprobanteTipoDelivery').val(comprobante);

                if ($(this).val() === 'Final') {
                    $('#clienteGroupDelivery').hide();
                    $('#clienteSelectDelivery').removeAttr('required');
                } else {
                    $('#clienteGroupDelivery').show();
                    $('#clienteSelectDelivery').attr('required', 'required');
                }
            });

            // Manejar cambio de método de pago para mesa
            $('#metodoPagoGroupMesa input[name="metodo_pago"]').change(function() {
                if ($(this).val() === 'Efectivo') {
                    $('#montoEfectivoGroupMesa').show();
                    $('#montoRecibidoMesa').attr('required', 'required');
                } else {
                    $('#montoEfectivoGroupMesa').hide();
                    $('#montoRecibidoMesa').removeAttr('required').val('');
                    $('#vueltoSectionMesa').hide();
                }
            });

            // Manejar cambio de método de pago para delivery
            $('#metodoPagoGroupDelivery input[name="metodo_pago"]').change(function() {
                if ($(this).val() === 'Efectivo') {
                    $('#montoEfectivoGroupDelivery').show();
                    $('#montoRecibidoDelivery').attr('required', 'required');
                } else {
                    $('#montoEfectivoGroupDelivery').hide();
                    $('#montoRecibidoDelivery').removeAttr('required').val('');
                    $('#vueltoSectionDelivery').hide();
                }
            });

            // Calcular vuelto para mesa
            $('#montoRecibidoMesa').on('input', function() {
                var montoRecibido = parseFloat($(this).val()) || 0;
                var total = parseFloat($('#modalMesaTotal').text());

                if (montoRecibido >= total) {
                    $('#vueltoMontoMesa').text((montoRecibido - total).toFixed(2));
                    $('#vueltoSectionMesa').show();
                } else {
                    $('#vueltoSectionMesa').hide();
                }
            });

            // Calcular vuelto para delivery
            $('#montoRecibidoDelivery').on('input', function() {
                var montoRecibido = parseFloat($(this).val()) || 0;
                var total = parseFloat($('#modalDeliveryTotal').text());

                if (montoRecibido >= total) {
                    $('#vueltoMontoDelivery').text((montoRecibido - total).toFixed(2));
                    $('#vueltoSectionDelivery').show();
                } else {
                    $('#vueltoSectionDelivery').hide();
                }
            });

            // Envío del formulario de mesa
            $('#formFacturaMesa').on('submit', function(e) {
                e.preventDefault();

                var metodoPago = $('#metodoPagoGroupMesa input[name="metodo_pago"]:checked').val();
                var montoRecibido = parseFloat($('#montoRecibidoMesa').val()) || 0;
                var total = parseFloat($('#modalMesaTotal').text());
                var tipoFactura = $('#tipoFacturaMesa').val();
                var clienteId = $('#clienteSelectMesa').val();

                // Validaciones
                if ((tipoFactura === 'Fiscal' || tipoFactura === 'Credito') && !clienteId) {
                    alert('❌ Debe seleccionar un cliente para factura ' + tipoFactura);
                    return false;
                }

                if (metodoPago === 'Efectivo' && montoRecibido < total) {
                    alert('❌ El monto recibido es menor al total.');
                    return false;
                }

                // Deshabilitar botón para evitar múltiples clics
                $('#btnGenerarFacturaMesa').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');

                // Crear formulario temporal para abrir en nueva pestaña
                var tempForm = document.createElement('form');
                tempForm.action = $(this).attr('action');
                tempForm.method = 'POST';
                tempForm.target = '_blank';
                tempForm.style.display = 'none';

                // Agregar todos los campos del formulario original
                $(this).find(':input').each(function() {
                    if (this.name) {
                        if ((this.type === 'radio' || this.type === 'checkbox') && !this.checked) {
                            return true;
                        }

                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = this.name;
                        input.value = $(this).val();
                        tempForm.appendChild(input);
                    }
                });

                // Agregar el formulario al documento y enviarlo
                document.body.appendChild(tempForm);
                tempForm.submit();

                // Cerrar el modal inmediatamente
                $('#modalFacturarMesa').modal('hide');

                // Mostrar mensaje de éxito
                mostrarMensajeExito();

                // Restaurar botón después de 2 segundos
                setTimeout(function() {
                    $('#btnGenerarFacturaMesa').prop('disabled', false).html('Generar Factura');
                }, 2000);

                return false;
            });

            // Envío del formulario de delivery
            $('#formFacturaDelivery').on('submit', function(e) {
                e.preventDefault();

                var metodoPago = $('#metodoPagoGroupDelivery input[name="metodo_pago"]:checked').val();
                var montoRecibido = parseFloat($('#montoRecibidoDelivery').val()) || 0;
                var total = parseFloat($('#modalDeliveryTotal').text());
                var tipoFactura = $('#tipoFacturaDelivery').val();
                var clienteId = $('#clienteSelectDelivery').val();

                // Validaciones
                if ((tipoFactura === 'Fiscal' || tipoFactura === 'Credito') && !clienteId) {
                    alert('❌ Debe seleccionar un cliente para factura ' + tipoFactura);
                    return false;
                }

                if (metodoPago === 'Efectivo' && montoRecibido < total) {
                    alert('❌ El monto recibido es menor al total.');
                    return false;
                }

                // Deshabilitar botón para evitar múltiples clics
                $('#btnGenerarFacturaDelivery').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');

                // Crear formulario temporal para abrir en nueva pestaña
                var tempForm = document.createElement('form');
                tempForm.action = $(this).attr('action');
                tempForm.method = 'POST';
                tempForm.target = '_blank';
                tempForm.style.display = 'none';

                // Agregar todos los campos del formulario original
                $(this).find(':input').each(function() {
                    if (this.name) {
                        if ((this.type === 'radio' || this.type === 'checkbox') && !this.checked) {
                            return true;
                        }

                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = this.name;
                        input.value = $(this).val();
                        tempForm.appendChild(input);
                    }
                });

                // Agregar el formulario al documento y enviarlo
                document.body.appendChild(tempForm);
                tempForm.submit();

                // Cerrar el modal inmediatamente
                $('#modalFacturarDelivery').modal('hide');

                // Mostrar mensaje de éxito
                mostrarMensajeExito();

                // Restaurar botón después de 2 segundos
                setTimeout(function() {
                    $('#btnGenerarFacturaDelivery').prop('disabled', false).html('Generar Factura');
                }, 2000);

                return false;
            });

            // Solución definitiva para cerrar el modal al cargar la página
            $('.modal').modal('hide');
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();

            // Prevenir que el modal se quede abierto después de recargar
            if (window.performance && window.performance.navigation.type === 1) {
                // La página se recargó
                $('.modal').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
            }

            // Limpiar cualquier parámetro de URL que pueda estar causando que el modal se abra
            if (window.location.href.indexOf('modal=show') > -1) {
                var newUrl = window.location.href.split('?')[0];
                window.history.replaceState({}, document.title, newUrl);
            }
        });

        // También agregar este código para manejar cuando la página se carga completamente
        $(window).on('load', function() {
            // Asegurarse de que todos los modales estén cerrados
            $('.modal').modal('hide');
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
        });
    </script>

</body>

</html>