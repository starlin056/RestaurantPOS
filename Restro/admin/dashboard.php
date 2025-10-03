<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('config/config.php');
require_once('config/checklogin.php');
check_login();

// Ventas últimos 7 días
$ventas_labels = [];
$ventas_data = [];
$ret = $mysqli->query("SELECT DATE(fecha_factura) as fecha, SUM(total) as total_ventas 
                       FROM rpos_facturas 
                       WHERE fecha_factura >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                       GROUP BY DATE(fecha_factura)
                       ORDER BY fecha ASC");
while ($row = $ret->fetch_object()) {
    $ventas_labels[] = date('d/m', strtotime($row->fecha));
    $ventas_data[] = floatval($row->total_ventas);
}

// Top 5 clientes
$clientes_labels = [];
$clientes_data = [];
$ret2 = $mysqli->query("SELECT cliente_nombre, SUM(total) as total_ventas 
                        FROM rpos_facturas 
                        WHERE cliente_nombre NOT IN ('Consumidor Final', '') 
                        GROUP BY cliente_nombre 
                        ORDER BY total_ventas DESC 
                        LIMIT 5");
while ($row = $ret2->fetch_object()) {
    $clientes_labels[] = $row->cliente_nombre;
    $clientes_data[] = floatval($row->total_ventas);
}

require_once('partials/_head.php');
?>

<body>
<?php require_once('partials/_sidebar.php'); ?>

<div class="main-content">
<?php require_once('partials/_topnav.php'); ?>

<!-- Header -->
<div class="header bg-gradient-primary pb-8 pt-5 pt-md-8">
    <div class="container-fluid">
        <div class="header-body">
            <h2 class="text-white mb-0">📊 Dashboard</h2>
            <p class="text-white text-sm">Resumen de comprobantes y pedidos recientes</p>
        </div>
    </div>
</div>

<div class="container-fluid mt--7">

    <!-- Tarjetas de Comprobantes tipo batería -->
    <div class="row">
    <?php
    $comprobantes_query = "SELECT * FROM rpos_secuenciales_comprobantes WHERE tipo_comprobante IN ('B01','B02','B03','B04') ORDER BY tipo_comprobante";
    $comprobantes_result = $mysqli->query($comprobantes_query);

    while ($comp = $comprobantes_result->fetch_object()):
        $disponibles = $comp->secuencial_final - $comp->secuencial_actual;
        $porcentaje = $comp->secuencial_final > 0 ? ($disponibles / $comp->secuencial_final) * 100 : 0;
        $color = $porcentaje > 50 ? '#28a745' : ($porcentaje > 20 ? '#ffc107' : '#dc3545');
    ?>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow battery-card">
                <div class="card-body text-center">
                    <h5 class="card-title mb-2"><?php echo $comp->tipo_comprobante; ?></h5>
                    <div class="battery-wrapper">
                        <div class="battery-level" style="height: <?php echo $porcentaje; ?>%; background: <?php echo $color; ?>;"></div>
                    </div>
                    <p class="mt-2"><?php echo $disponibles; ?> disponibles (<?php echo round($porcentaje); ?>%)</p>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
    </div>

    <!-- Gráficos -->
    <div class="row">
        <!-- Ventas últimos 7 días -->
        <div class="col-xl-6 col-lg-12 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h3 class="mb-0">Ventas últimos 7 días</h3>
                </div>
                <div class="card-body">
                    <canvas id="ventasChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top clientes -->
        <div class="col-xl-6 col-lg-12 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h3 class="mb-0">Top 5 Clientes</h3>
                </div>
                <div class="card-body">
                    <canvas id="clientesChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Pedidos recientes -->
    <div class="row mt-4">
        <div class="col-xl-12">
            <div class="card shadow">
                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Pedidos Recientes</h3>
                    <a href="orders_reports.php" class="btn btn-sm btn-primary">Ver todos</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-flush table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Código</th>
                                <th>Cliente</th>
                                <th>Producto</th>
                                <th>Precio unitario</th>
                                <th>Cantidad</th>
                                <th>Total</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $ret = "SELECT * FROM rpos_orders ORDER BY created_at DESC LIMIT 7";
                        $stmt = $mysqli->prepare($ret);
                        $stmt->execute();
                        $res = $stmt->get_result();
                        while ($order = $res->fetch_object()):
                            $total = ($order->prod_price * $order->prod_qty);
                        ?>
                            <tr>
                                <td><?php echo $order->order_code; ?></td>
                                <td><?php echo $order->customer_name; ?></td>
                                <td><?php echo $order->prod_name; ?></td>
                                <td>$<?php echo $order->prod_price; ?></td>
                                <td><?php echo $order->prod_qty; ?></td>
                                <td>$<?php echo $total; ?></td>
                                <td><?php echo date('d/M/Y g:i A', strtotime($order->created_at)); ?></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once('partials/_scripts.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Ventas últimos 7 días
const ctxVentas = document.getElementById('ventasChart').getContext('2d');
new Chart(ctxVentas, {
    type: 'line',
    data: {
        labels: <?= json_encode($ventas_labels) ?>,
        datasets: [{
            label: 'Ventas RD$',
            data: <?= json_encode($ventas_data) ?>,
            borderColor: '#4e73df',
            backgroundColor: 'rgba(78, 115, 223,0.1)',
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        responsive:true,
        plugins:{legend:{display:false}},
        scales:{
            y:{beginAtZero:true,title:{display:true,text:'RD$'}},
            x:{title:{display:true,text:'Fecha'}}
        }
    }
});

// Top 5 clientes
const ctxClientes = document.getElementById('clientesChart').getContext('2d');
new Chart(ctxClientes, {
    type: 'bar',
    data: {
        labels: <?= json_encode($clientes_labels) ?>,
        datasets:[{
            label:'Total Ventas',
            data: <?= json_encode($clientes_data) ?>,
            backgroundColor: 'rgba(28, 200, 138, 0.7)',
            borderColor:'rgba(28, 200, 138, 1)',
            borderWidth:1
        }]
    },
    options: {
        responsive:true,
        plugins:{legend:{display:false}},
        scales:{y:{beginAtZero:true}}
    }
});
</script>

<style>
.battery-card { border-radius: 1rem; transition: transform 0.3s; }
.battery-card:hover { transform: translateY(-5px); }
.battery-wrapper { width: 40px; height: 120px; border: 2px solid #333; border-radius: 4px; margin: auto; position: relative; background: #e9ecef; }
.battery-level { position: absolute; bottom: 0; width: 100%; border-radius: 2px; transition: height 0.5s, background 0.5s; }
.table-hover tbody tr:hover { background-color: rgba(78, 115, 223, 0.05); }
.card-header h3 { font-weight: 600; }
.bg-gradient-primary { background: linear-gradient(87deg,#1d8cf8,#3358f4)!important; }
.card { border-radius: 0.75rem; }
</style>
</body>
</html>
