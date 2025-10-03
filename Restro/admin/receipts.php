<?php
session_start();
include('config/config.php');
include('config/check_login.php');
check_login();
require_once('partials/_head.php');
?>

<body>
    <!-- Sidenav -->
    <?php require_once('partials/_sidebar.php'); ?>
    <!-- Main content -->
    <div class="main-content">
        <!-- Top navbar -->
        <?php require_once('partials/_topnav.php'); ?>
        <!-- Header -->
        <div style="background-image: url(assets/img/theme/restro00.jpg); background-size: cover;" class="header pb-8 pt-5 pt-md-8">
            <span class="mask bg-gradient-dark opacity-8"></span>
            <div class="container-fluid">
                <div class="header-body">
                </div>
            </div>
        </div>
        <!-- Contenido de la página -->
        <div class="container-fluid mt--8">
            <!-- Tabla -->
            <div class="row">
                <div class="col">
                    <div class="card shadow">
                        <div class="card-header border-0">
                            Órdenes Pagadas
                        </div>
                        <div class="table-responsive">
                            <table class="table align-items-center table-flush">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-success" scope="col">Código</th>
                                        <th scope="col">Cliente</th>
                                        <th class="text-success" scope="col">Producto</th>
                                        <th scope="col">Precio Unitario</th>
                                        <th class="text-success" scope="col">Cantidad</th>
                                        <th scope="col">Precio Total</th>
                                        <th class="text-success" scope="col">Fecha</th>
                                        <th scope="col">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $ret = "SELECT * FROM rpos_orders WHERE order_status = 'Paid' ORDER BY created_at DESC";
                                    $stmt = $mysqli->prepare($ret);
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    while ($order = $res->fetch_object()) {
                                        $total = ($order->prod_price * $order->prod_qty);
                                    ?>
                                        <tr>
                                            <th class="text-success" scope="row"><?php echo htmlspecialchars($order->order_code); ?></th>
                                            <td><?php echo htmlspecialchars($order->customer_name); ?></td>
                                            <td class="text-success"><?php echo htmlspecialchars($order->prod_name); ?></td>
                                            <td>$ <?php echo number_format($order->prod_price, 2); ?></td>
                                            <td class="text-success"><?php echo (int)$order->prod_qty; ?></td>
                                            <td>$ <?php echo number_format($total, 2); ?></td>
                                            <td><?php echo date('d/M/Y g:i a', strtotime($order->created_at)); ?></td>
                                            <td>
                                                <a target="_blank" href="print_receipt.php?order_code=<?php echo urlencode($order->order_code); ?>">
                                                    <button class="btn btn-sm btn-primary">
                                                        <i class="fas fa-print"></i> Imprimir Recibo
                                                    </button>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Pie de página -->
            <?php require_once('partials/_footer.php'); ?>
        </div>
    </div>
    <!-- Scripts -->
    <?php require_once('partials/_scripts.php'); ?>
</body>
</html>
