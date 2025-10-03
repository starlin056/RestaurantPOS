<?php
// factura/lista.php
session_start();
include('../config/config.php');
include('../config/checklogin.php');
check_login();
require_once('../partials/_head.php');

// Fecha de hoy
$hoy = date('Y-m-d');
?>

<body>
    <?php require_once('../partials/_sidebar.php'); ?>

    <div class="main-content">
        <?php require_once('../partials/_topnav.php'); ?>

        <!-- Header -->
        <div class="header bg-gradient-primary pb-7 pt-6">
            <div class="container-fluid">
                <div class="header-body">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <h1 class="text-white display-4">📋 Lista de Facturas</h1>
                            <p class="text-white mb-0">Historial completo de facturas generadas</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla -->
        <div class="container-fluid mt--7">
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-lg rounded-3 border-0">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                            <h3 class="mb-0"><i class="fas fa-file-invoice"></i> Todas las Facturas</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle" id="tablaFacturas">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Factura</th>
                                            <th>Mesa / Delivery</th>
                                            <th>Cliente</th>
                                            <th>Tipo</th>
                                            <th>Subtotal</th>
                                            <th>ITEBIS</th>
                                            <th>Servicio</th>
                                            <th>Total</th>
                                            <th>Fecha</th>
                                            <th>Estado</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Consultar todas las facturas
                                        $query = "SELECT * FROM rpos_facturas
                                                  ORDER BY 
                                                    CASE WHEN DATE(fecha_factura) = ? THEN 1 ELSE 2 END,
                                                    fecha_factura DESC";
                                        $stmt = $mysqli->prepare($query);
                                        $stmt->bind_param('s', $hoy);
                                        $stmt->execute();
                                        $facturas = $stmt->get_result();

                                        while ($factura = $facturas->fetch_object()):
                                        ?>
                                            <tr>
                                                <td class="fw-bold"><?php echo $factura->factura_code; ?></td>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        <?php
                                                        // Mostrar Delivery si es delivery, sino mesa
                                                        if ($factura->mesa_id === 'DELIVERY' || $factura->numero_mesa == 0) {
                                                            echo "Delivery";
                                                        } else {
                                                            echo "Mesa #" . $factura->numero_mesa;
                                                        }
                                                        ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php
                                                    // Evitar mostrar "0" como cliente
                                                    if (empty($factura->cliente_nombre) || $factura->cliente_nombre == "0") {
                                                        echo "Consumidor Final";
                                                    } else {
                                                        echo $factura->cliente_nombre;
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <span class="badge 
            <?php echo $factura->tipo_factura == 'Final' ? 'bg-info' : ($factura->tipo_factura == 'Fiscal' ? 'bg-success' : 'bg-warning'); ?>">
                                                        <?php echo $factura->tipo_factura; ?>
                                                    </span>
                                                </td>
                                                <td>RD$ <?php echo number_format($factura->subtotal, 2); ?></td>
                                                <td>RD$ <?php echo number_format($factura->itebis, 2); ?></td>
                                                <td>RD$ <?php echo number_format($factura->servicio, 2); ?></td>
                                                <td class="text-success fw-bold">RD$ <?php echo number_format($factura->total, 2); ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($factura->fecha_factura)); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $factura->estado == 'Pagada' ? 'bg-success' : 'bg-danger'; ?>">
                                                        <?php echo $factura->estado; ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="imprimir_factura.php?id=<?php echo $factura->factura_id; ?>"
                                                        target="_blank"
                                                        class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-print"></i>
                                                    </a>
                                                </td>
                                            </tr>

                                        <?php endwhile;
                                        $stmt->close(); ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once('../partials/_scripts.php'); ?>
    <?php require_once('../partials/_footer.php'); ?>

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#tablaFacturas').DataTable({
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                },
                order: [
                    [8, "desc"]
                ],
                pageLength: 10,
                responsive: true,
                autoWidth: false
            });
        });
    </script>

    <style>
        .table thead th {
            vertical-align: middle;
            text-align: center;
        }

        .table-hover tbody tr:hover {
            background-color: #f1f5f9;
        }

        .badge {
            font-size: 0.85rem;
        }

        .btn-outline-primary:hover {
            color: white !important;
        }
    </style>
</body>

</html>