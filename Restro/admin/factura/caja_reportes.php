<?php
// caja_reportes.php
session_start();
include('../config/config.php');
include('../config/checklogin.php');
check_login();

// ---------------------------
// 1. Verificación de administrador con clave fija
// ---------------------------
define('ADMIN_KEY', '123456789'); // <-- Cambia esta clave

if ($_SESSION['rol'] !== 'admin') {
  $acceso_permitido = false;

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clave_admin'])) {
    $clave_admin = trim($_POST['clave_admin']);
    if ($clave_admin === ADMIN_KEY) {
      $acceso_permitido = true;
    } else {
      $error = "Clave incorrecta, acceso denegado.";
    }
  }

  if (!$acceso_permitido) {
    require_once(__DIR__ . '/../partials/_head.php');
    require_once('../partials/_sidebar.php');
    require_once('../partials/_topnav.php');
?>
    <div class="container mt-5">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="card shadow">
            <div class="card-header bg-primary text-white">
              <h5 class="mb-0">Acceso Restringido</h5>
            </div>
            <div class="card-body">
              <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
              <?php endif; ?>
              <form method="post">
                <div class="form-group">
                  <label>Clave de Administrador</label>
                  <input type="password" name="clave_admin" class="form-control" required>
                </div>
                <div class="text-center">
                  <button type="submit" class="btn btn-primary">Validar</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
<?php
    require_once('../partials/_scripts.php');
    require_once('../partials/_footer.php');
    exit;
  }
}

// ---------------------------
// 2. Obtener cajas recientes
// ---------------------------
$query = "SELECT c.*, COALESCE(a.admin_name, s.staff_name) AS cajero
          FROM rpos_caja c
          LEFT JOIN rpos_admin a ON c.usuario_id = a.admin_id
          LEFT JOIN rpos_staff s ON c.usuario_id = s.staff_id
          ORDER BY fecha_apertura DESC";
$result = $mysqli->query($query);

$cajas = [];
$ventas_totales = [];
$fechas = [];
while ($row = $result->fetch_object()) {
  $cajas[] = $row;

  $total_ventas = floatval($row->ventas_efectivo)
    + floatval($row->ventas_tarjeta)
    + floatval($row->ventas_transferencia)
    + floatval($row->ventas_app);
  $ventas_totales[] = $total_ventas;
  $fechas[] = date('d/m', strtotime($row->fecha_apertura));
}

// ---------------------------
// 3. Ventas por cajero
// ---------------------------
$query_cajeros = "
  SELECT COALESCE(a.admin_name, s.staff_name) AS cajero,
         SUM(c.ventas_efectivo + c.ventas_tarjeta + c.ventas_transferencia + c.ventas_app) AS total_ventas
  FROM rpos_caja c
  LEFT JOIN rpos_admin a ON c.usuario_id = a.admin_id
  LEFT JOIN rpos_staff s ON c.usuario_id = s.staff_id
  GROUP BY cajero
  ORDER BY total_ventas DESC
";
$res_cajeros = $mysqli->query($query_cajeros);

$cajeros = [];
$ventas_cajeros = [];
while ($row = $res_cajeros->fetch_object()) {
  $cajeros[] = $row->cajero;
  $ventas_cajeros[] = floatval($row->total_ventas);
}

// ---------------------------
// 4. Ventas por turno
// ---------------------------
$query_turnos = "
  SELECT 
    CASE 
      WHEN HOUR(fecha_apertura) BETWEEN 6 AND 13 THEN 'Mañana'
      WHEN HOUR(fecha_apertura) BETWEEN 14 AND 21 THEN 'Tarde'
      ELSE 'Noche'
    END AS turno,
    SUM(c.ventas_efectivo + c.ventas_tarjeta + c.ventas_transferencia + c.ventas_app) AS total_ventas
  FROM rpos_caja c
  GROUP BY turno
  ORDER BY FIELD(turno,'Mañana','Tarde','Noche')
";
$res_turnos = $mysqli->query($query_turnos);

$turnos = [];
$ventas_turnos = [];
while ($row = $res_turnos->fetch_object()) {
  $turnos[] = $row->turno;
  $ventas_turnos[] = floatval($row->total_ventas);
}

require_once(__DIR__ . '/../partials/_head.php');
?>

<body>
  <?php require_once('../partials/_sidebar.php'); ?>
  <div class="main-content">
    <?php require_once('../partials/_topnav.php'); ?>

    <div class="header bg-gradient-primary pb-8 pt-5 pt-md-8">
      <div class="container-fluid">
        <div class="header-body">
          <h2 class="text-white">Reporte de Cajas</h2>
        </div>
      </div>
    </div>

    <div class="container-fluid mt--7">
      <div class="row">
        <div class="col">
          <div class="card shadow">
            <div class="card-header border-0">
              <h3 class="mb-0">Historial de Cajas</h3>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-bordered table-hover">
                  <thead class="thead-light">
                    <tr>
                      <th>Cajero</th>
                      <th>Fecha Apertura</th>
                      <th>Monto Inicial</th>
                      <th>Ventas Efectivo</th>
                      <th>Ventas Tarjeta</th>
                      <th>Ventas Transferencia</th>
                      <th>Ventas App</th>
                      <th>Gastos</th>
                      <th>Total</th>
                      <th>Monto Final</th>
                      <th>Diferencia</th>
                      <th>Estado</th>
                      <th>Observaciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($cajas as $caja):
                      $total_teorico = floatval($caja->monto_inicial)
                        + floatval($caja->ventas_efectivo)
                        + floatval($caja->ventas_tarjeta)
                        + floatval($caja->ventas_transferencia)
                        + floatval($caja->ventas_app)
                        - floatval($caja->gastos);
                      $diferencia = $caja->monto_final ? floatval($caja->monto_final) - $total_teorico : 0;
                    ?>
                      <tr>
                        <td><?= $caja->cajero ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($caja->fecha_apertura)) ?></td>
                        <td>RD$ <?= number_format($caja->monto_inicial, 2) ?></td>
                        <td>RD$ <?= number_format($caja->ventas_efectivo, 2) ?></td>
                        <td>RD$ <?= number_format($caja->ventas_tarjeta, 2) ?></td>
                        <td>RD$ <?= number_format($caja->ventas_transferencia, 2) ?></td>
                        <td>RD$ <?= number_format($caja->ventas_app, 2) ?></td>
                        <td>RD$ <?= number_format($caja->gastos, 2) ?></td>
                        <td>RD$ <?= number_format($total_teorico, 2) ?></td>
                        <td>RD$ <?= $caja->monto_final ? number_format($caja->monto_final, 2) : '-' ?></td>
                        <td>RD$ <?= $caja->monto_final ? number_format($diferencia, 2) : '-' ?></td>
                        <td>
                          <span class="badge badge-<?= $caja->estado == 'Cerrada' ? 'success' : 'warning' ?>">
                            <?= $caja->estado ?>
                          </span>
                        </td>
                        <td><?= $caja->observaciones ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
              <?php if (empty($cajas)): ?>
                <p class="text-center text-muted">No se encontraron registros de cajas.</p>
              <?php endif; ?>
            </div>
          </div>

          <!-- Gráfico de Ventas -->
          <div class="card shadow mt-4">
            <div class="card-header">
              <h3 class="mb-0">Gráfico de Ventas</h3>
            </div>
            <div class="card-body">
              <canvas id="ventasChart" height="100"></canvas>
            </div>
          </div>

          <!-- Ventas por Cajero -->
          <div class="card shadow mt-4">
            <div class="card-header">
              <h3 class="mb-0">Ventas por Cajero</h3>
            </div>
            <div class="card-body">
              <canvas id="cajerosChart" height="80"></canvas>
            </div>
          </div>

          <!-- Ventas por Turno -->
          <div class="row justify-content-center">
            <div class="col-md-12"> <!-- en lugar de col-md-12 -->
              <div class="card shadow mt-4">
                <div class="card-header">
                  <h3 class="mb-0">Ventas por Turno</h3>
                </div>
                <div class="card-body text-center">
                  <div style="max-width:400px; margin:auto;">
                    <canvas id="turnosChart"></canvas>
                  </div>
                </div>
              </div>
            </div>
          </div>



          <?php require_once('../partials/_scripts.php'); ?>
          <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
          <script>
            // Ventas por Caja
            const ctx = document.getElementById('ventasChart').getContext('2d');
            new Chart(ctx, {
              type: 'line',
              data: {
                labels: <?= json_encode($fechas) ?>,
                datasets: [{
                  label: 'Total Ventas registradas',
                  data: <?= json_encode($ventas_totales) ?>,
                  backgroundColor: 'rgba(54, 162, 235, 0.2)',
                  borderColor: 'rgba(54, 162, 235, 1)',
                  borderWidth: 2,
                  fill: true,
                  tension: 0.3
                }]
              },
              options: {
                responsive: true,
                plugins: {
                  legend: {
                    display: true,
                    position: 'top'
                  }
                },
                scales: {
                  y: {
                    beginAtZero: true,
                    title: {
                      display: true,
                      text: 'RD$'
                    }
                  },
                  x: {
                    title: {
                      display: true,
                      text: 'Fecha'
                    }
                  }
                }
              }
            });

            // Ventas por Cajero
            const ctxCajeros = document.getElementById('cajerosChart').getContext('2d');
            new Chart(ctxCajeros, {
              type: 'bar',
              data: {
                labels: <?= json_encode($cajeros) ?>,
                datasets: [{
                  label: 'Total Ventas por Cajero',
                  data: <?= json_encode($ventas_cajeros) ?>,
                  backgroundColor: 'rgba(75, 192, 192, 0.6)',
                  borderColor: 'rgba(75, 192, 192, 1)',
                  borderWidth: 1
                }]
              },
              options: {
                responsive: true,
                plugins: {
                  legend: {
                    display: false
                  }
                },
                scales: {
                  y: {
                    beginAtZero: true
                  }
                }
              }
            });

            // Ventas por Turno
            const ctxTurnos = document.getElementById('turnosChart').getContext('2d');
            new Chart(ctxTurnos, {
              type: 'pie',
              data: {
                labels: <?= json_encode($turnos) ?>,
                datasets: [{
                  label: 'Ventas por Turno',
                  data: <?= json_encode($ventas_turnos) ?>,
                  backgroundColor: ['#36A2EB', '#FFCE56', '#FF6384']
                }]
              },
              options: {
                responsive: true,
                plugins: {
                  legend: {
                    display: false
                  }
                },
                scales: {
                  y: {
                    beginAtZero: true
                  }
                }
              }
            });
          </script>
          <?php require_once('../partials/_footer.php'); ?>