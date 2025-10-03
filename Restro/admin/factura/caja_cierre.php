<?php
// caja_cierre.php
session_start();
include('../config/config.php');
include('../config/checklogin.php');
include_once('../auditoria/funciones.php');
check_login();

// -----------------------------
// 1. Verificar si hay pedidos abiertos (mesas o delivery)
// -----------------------------
$query_pedidos_abiertos = "SELECT 
    (SELECT COUNT(*) FROM rpos_mesas WHERE estado = 'Ocupada') as mesas_ocupadas,
    (SELECT COUNT(*) FROM rpos_delivery_orders WHERE estado IN ('Pendiente', 'En preparación', 'En camino')) as delivery_pendientes";
$stmt_pedidos = $mysqli->prepare($query_pedidos_abiertos);
$stmt_pedidos->execute();
$pedidos_abiertos = $stmt_pedidos->get_result()->fetch_object();
$stmt_pedidos->close();

// Si hay pedidos abiertos, mostrar error
if ($pedidos_abiertos->mesas_ocupadas > 0 || $pedidos_abiertos->delivery_pendientes > 0) {
    $_SESSION['error'] = "No se puede cerrar la caja porque hay pedidos abiertos: " . 
                        ($pedidos_abiertos->mesas_ocupadas > 0 ? $pedidos_abiertos->mesas_ocupadas . " mesa(s) ocupada(s). " : "") .
                        ($pedidos_abiertos->delivery_pendientes > 0 ? $pedidos_abiertos->delivery_pendientes . " delivery(s) pendiente(s)." : "");
    header("Location: caja_apertura.php");
    exit;
}

// -----------------------------
// 2. Obtener la caja abierta
// -----------------------------
if ($_SESSION['rol'] === 'admin' && isset($_GET['caja_id'])) {
    $caja_id = $_GET['caja_id'];
    $query = "SELECT c.*, COALESCE(a.admin_name, s.staff_name) AS cajero
              FROM rpos_caja c
              LEFT JOIN rpos_admin a ON c.usuario_id = a.admin_id
              LEFT JOIN rpos_staff s ON c.usuario_id = s.staff_id
              WHERE c.estado = 'Abierta' AND c.caja_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('s', $caja_id);
} else {
    $user_id = $_SESSION['user_id'];
    $query = "SELECT c.*, COALESCE(a.admin_name, s.staff_name) AS cajero
              FROM rpos_caja c
              LEFT JOIN rpos_admin a ON c.usuario_id = a.admin_id
              LEFT JOIN rpos_staff s ON c.usuario_id = s.staff_id
              WHERE c.estado = 'Abierta' AND c.usuario_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('s', $user_id);
}

$stmt->execute();
$caja_abierta = $stmt->get_result()->fetch_object();
$stmt->close();

if (!$caja_abierta) {
    $_SESSION['error'] = "No hay caja abierta para cerrar.";
    header("Location: caja_apertura.php");
    exit;
}

// -----------------------------
// 3. Obtener movimientos
// -----------------------------
$query = "SELECT * FROM rpos_movimientos_caja WHERE caja_id = ? ORDER BY fecha_movimiento DESC";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('s', $caja_abierta->caja_id);
$stmt->execute();
$movimientos_result = $stmt->get_result();
$movimientos = [];
while ($row = $movimientos_result->fetch_object()) {
    $movimientos[] = $row;
}
$stmt->close();

// -----------------------------
// 4. Obtener ventas reales (mesas + delivery)
// -----------------------------
$query_ventas = "SELECT 
    -- Ventas Mesas
    COALESCE(SUM(CASE WHEN f.mesa_id != 'DELIVERY' AND p.pay_method = 'Efectivo' THEN p.pay_amt ELSE 0 END),0) AS ventas_mesa_efectivo,
    COALESCE(SUM(CASE WHEN f.mesa_id != 'DELIVERY' AND p.pay_method = 'Tarjeta Débito' THEN p.pay_amt ELSE 0 END),0) AS ventas_mesa_debito,
    COALESCE(SUM(CASE WHEN f.mesa_id != 'DELIVERY' AND p.pay_method = 'Tarjeta Crédito' THEN p.pay_amt ELSE 0 END),0) AS ventas_mesa_credito,
    COALESCE(SUM(CASE WHEN f.mesa_id != 'DELIVERY' AND p.pay_method = 'Transferencia' THEN p.pay_amt ELSE 0 END),0) AS ventas_mesa_transferencia,
    COALESCE(SUM(CASE WHEN f.mesa_id != 'DELIVERY' AND p.pay_method NOT IN ('Efectivo','Tarjeta Débito','Tarjeta Crédito','Transferencia') THEN p.pay_amt ELSE 0 END),0) AS ventas_mesa_otros,
    COALESCE(SUM(CASE WHEN f.mesa_id != 'DELIVERY' THEN p.pay_amt ELSE 0 END),0) AS total_ventas_mesa,
    
    -- Ventas Delivery
    COALESCE(SUM(CASE WHEN f.mesa_id = 'DELIVERY' AND p.pay_method = 'Efectivo' THEN p.pay_amt ELSE 0 END),0) AS ventas_delivery_efectivo,
    COALESCE(SUM(CASE WHEN f.mesa_id = 'DELIVERY' AND p.pay_method = 'Tarjeta Débito' THEN p.pay_amt ELSE 0 END),0) AS ventas_delivery_debito,
    COALESCE(SUM(CASE WHEN f.mesa_id = 'DELIVERY' AND p.pay_method = 'Tarjeta Crédito' THEN p.pay_amt ELSE 0 END),0) AS ventas_delivery_credito,
    COALESCE(SUM(CASE WHEN f.mesa_id = 'DELIVERY' AND p.pay_method = 'Transferencia' THEN p.pay_amt ELSE 0 END),0) AS ventas_delivery_transferencia,
    COALESCE(SUM(CASE WHEN f.mesa_id = 'DELIVERY' AND p.pay_method NOT IN ('Efectivo','Tarjeta Débito','Tarjeta Crédito','Transferencia') THEN p.pay_amt ELSE 0 END),0) AS ventas_delivery_otros,
    COALESCE(SUM(CASE WHEN f.mesa_id = 'DELIVERY' THEN p.pay_amt ELSE 0 END),0) AS total_ventas_delivery,
    
    -- Totales Generales
    COALESCE(SUM(p.pay_amt),0) AS total_ventas_real,
    COALESCE(SUM(CASE WHEN p.pay_method = 'Efectivo' THEN p.pay_amt ELSE 0 END),0) AS ventas_efectivo_real,
    COALESCE(SUM(CASE WHEN p.pay_method = 'Tarjeta Débito' THEN p.pay_amt ELSE 0 END),0) AS ventas_debito_real,
    COALESCE(SUM(CASE WHEN p.pay_method = 'Tarjeta Crédito' THEN p.pay_amt ELSE 0 END),0) AS ventas_credito_real,
    COALESCE(SUM(CASE WHEN p.pay_method = 'Transferencia' THEN p.pay_amt ELSE 0 END),0) AS ventas_transferencia_real,
    COALESCE(SUM(CASE WHEN p.pay_method NOT IN ('Efectivo','Tarjeta Débito','Tarjeta Crédito','Transferencia') THEN p.pay_amt ELSE 0 END),0) AS ventas_otros_real
FROM rpos_payments p
JOIN rpos_facturas f ON p.order_code = f.factura_code
WHERE p.pay_code LIKE 'PAY-%' AND p.created_at >= ?";
$stmt_ventas = $mysqli->prepare($query_ventas);
$stmt_ventas->bind_param('s', $caja_abierta->fecha_apertura);
$stmt_ventas->execute();
$ventas_reales = $stmt_ventas->get_result()->fetch_object();
$stmt_ventas->close();

// -----------------------------
// 5. Calcular totales e ingresos/egresos
// -----------------------------
$total_ingresos = $total_egresos = 0;
foreach ($movimientos as $mov) {
    if ($mov->tipo == 'Ingreso') $total_ingresos += floatval($mov->monto);
    else $total_egresos += floatval($mov->monto);
}

$total_caja = floatval($caja_abierta->monto_inicial)
            + floatval($ventas_reales->ventas_efectivo_real)
            + floatval($ventas_reales->ventas_debito_real)
            + floatval($ventas_reales->ventas_credito_real)
            + floatval($ventas_reales->ventas_transferencia_real)
            + floatval($ventas_reales->ventas_otros_real)
            + $total_ingresos
            - floatval($caja_abierta->gastos)
            - $total_egresos;

// -----------------------------
// 6. Procesar cierre de caja
// -----------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ventas_tarjeta_total = floatval($ventas_reales->ventas_debito_real) + floatval($ventas_reales->ventas_credito_real);

    if (isset($_POST['cierre'])) {
        $monto_final = floatval($_POST['monto_final']);
        $observaciones = trim($_POST['observaciones']);
        $diferencia = $monto_final - $total_caja;

        $query_update = "UPDATE rpos_caja 
                         SET fecha_cierre = NOW(),
                             monto_final = ?,
                             ventas_efectivo = ?,
                             ventas_tarjeta = ?,
                             ventas_transferencia = ?,
                             ventas_app = ?,
                             total_ventas = ?,
                             observaciones = CONCAT(COALESCE(observaciones,''),' | CIERRE: ',?),
                             estado = 'Cerrada'
                         WHERE caja_id = ?";
        $stmt = $mysqli->prepare($query_update);
        $stmt->bind_param(
            'ddddddds',
            $monto_final,
            $ventas_reales->ventas_efectivo_real,
            $ventas_tarjeta_total,
            $ventas_reales->ventas_transferencia_real,
            $ventas_reales->ventas_otros_real,
            $ventas_reales->total_ventas_real,
            $observaciones,
            $caja_abierta->caja_id
        );
        if ($stmt->execute()) {
            $_SESSION['success'] = "Caja cerrada correctamente. Diferencia: RD$ " . number_format($diferencia,2);
            $stmt->close();
            header("Location: caja_reportes.php");
            exit;
        } else {
            $_SESSION['error'] = "Error al cerrar caja: " . $stmt->error;
        }
    }

    if (isset($_POST['actualizar_totales'])) {
        $query_update_totales = "UPDATE rpos_caja
                                 SET ventas_efectivo = ?,
                                     ventas_tarjeta = ?,
                                     ventas_transferencia = ?,
                                     ventas_app = ?,
                                     total_ventas = ?
                                 WHERE caja_id = ?";
        $stmt = $mysqli->prepare($query_update_totales);
        $stmt->bind_param(
            'ddddds',
            $ventas_reales->ventas_efectivo_real,
            $ventas_tarjeta_total,
            $ventas_reales->ventas_transferencia_real,
            $ventas_reales->ventas_otros_real,
            $ventas_reales->total_ventas_real,
            $caja_abierta->caja_id
        );
        if ($stmt->execute()) {
            $_SESSION['success'] = "Totales actualizados correctamente.";
            header("Location: caja_cierre.php");
            exit;
        } else {
            $_SESSION['error'] = "Error al actualizar totales: " . $stmt->error;
        }
    }
}

registrar_auditoria("Cerrar caja", "Usuario cerro la caja.");
require_once(__DIR__ . '/../partials/_head.php');
?>


<body>
  <?php require_once('../partials/_sidebar.php'); ?>
  <div class="main-content">
    <?php require_once('../partials/_topnav.php'); ?>

    <div class="header bg-gradient-primary pb-8 pt-5 pt-md-8">
      <div class="container-fluid">
        <div class="header-body"></div>
      </div>
    </div>

    <div class="container-fluid mt--7">
      <div class="row">
        <div class="col-md-12">
          <div class="card shadow">
            <div class="card-header border-0">
              <h3 class="mb-0">Cierre de Caja</h3>
              <p class="text-muted">Caja abierta el: <?php echo date('d/m/Y H:i', strtotime($caja_abierta->fecha_apertura)); ?></p>
              <p class="text-muted">Cajero: <?php echo $caja_abierta->cajero; ?></p>
            </div>
            <div class="card-body">
              <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error'];
                                                unset($_SESSION['error']); ?></div>
              <?php endif; ?>
              <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success'];
                                                  unset($_SESSION['success']); ?></div>
              <?php endif; ?>

              <!-- Alerta de problema -->
              <div class="alert alert-warning">
                <h5><i class="fas fa-exclamation-triangle"></i> Importante</h5>
                <p class="mb-0">Se detectó que las ventas no se estaban registrando automáticamente en la caja. Se han recuperado los datos reales de la base de datos.</p>
              </div>

              <div class="row mb-4">
                <div class="col-md-6">
                  <div class="card bg-light">
                    <div class="card-header bg-primary text-white">
                      <h5 class="mb-0">Resumen de Caja</h5>
                    </div>
                    <div class="card-body">
                      <p><strong>Monto Inicial:</strong> RD$ <?php echo number_format($caja_abierta->monto_inicial, 2); ?></p>

                      <h6 class="mt-3">Ventas por Tipo de Pedido:</h6>
                      <table class="table table-sm">
                        <tr>
                          <td>Ventas Mesas:</td>
                          <td class="text-right">RD$ <?php echo number_format($ventas_reales->total_ventas_mesa, 2); ?></td>
                        </tr>
                        <tr>
                          <td>Ventas Delivery:</td>
                          <td class="text-right">RD$ <?php echo number_format($ventas_reales->total_ventas_delivery, 2); ?></td>
                        </tr>
                        <tr class="table-info">
                          <td><strong>Total Ventas:</strong></td>
                          <td class="text-right"><strong>RD$ <?php echo number_format($ventas_reales->total_ventas_real, 2); ?></strong></td>
                        </tr>
                      </table>

                      <h6 class="mt-3">Ventas por Método de Pago:</h6>
                      <table class="table table-sm">
                        <tr>
                          <td>Efectivo:</td>
                          <td class="text-right">RD$ <?php echo number_format($ventas_reales->ventas_efectivo_real, 2); ?></td>
                        </tr>
                        <tr>
                          <td>Tarjeta Débito:</td>
                          <td class="text-right">RD$ <?php echo number_format($ventas_reales->ventas_debito_real, 2); ?></td>
                        </tr>
                        <tr>
                          <td>Tarjeta Crédito:</td>
                          <td class="text-right">RD$ <?php echo number_format($ventas_reales->ventas_credito_real, 2); ?></td>
                        </tr>
                        <tr>
                          <td>Transferencia:</td>
                          <td class="text-right">RD$ <?php echo number_format($ventas_reales->ventas_transferencia_real, 2); ?></td>
                        </tr>
                        <tr>
                          <td>Otros métodos:</td>
                          <td class="text-right">RD$ <?php echo number_format($ventas_reales->ventas_otros_real, 2); ?></td>
                        </tr>
                      </table>

                      <p><strong>Ingresos Adicionales:</strong> RD$ <?php echo number_format($total_ingresos, 2); ?></p>
                      <p><strong>Gastos:</strong> RD$ <?php echo number_format($caja_abierta->gastos, 2); ?></p>
                      <p><strong>Egresos Adicionales:</strong> RD$ <?php echo number_format($total_egresos, 2); ?></p>
                      <hr>
                      <p class="lead"><strong>Total de Caja (Esperado):</strong> RD$ <?php echo number_format($total_caja, 2); ?></p>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="card">
                    <div class="card-header bg-success text-white">
                      <h5 class="mb-0">Verificación de Cierre</h5>
                    </div>
                    <div class="card-body">
                      <form method="post">
                        <div class="form-group">
                          <label for="monto_final">Monto Final en Caja *</label>
                          <div class="input-group">
                            <div class="input-group-prepend">
                              <span class="input-group-text">RD$</span>
                            </div>
                            <input type="number" step="0.01" class="form-control" name="monto_final" required
                              placeholder="0.00" min="0" id="montoFinal">
                          </div>
                        </div>

                        <div class="form-group">
                          <label for="observaciones">Observaciones de Cierre</label>
                          <textarea class="form-control" name="observaciones" rows="3"
                            placeholder="Observaciones sobre el cierre de caja"></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                          <button type="submit" name="actualizar_totales" class="btn btn-info">
                            <i class="fas fa-sync-alt"></i> Actualizar Totales
                          </button>
                          <button type="submit" name="cierre" class="btn btn-primary">
                            <i class="fas fa-lock"></i> Cerrar Caja
                          </button>
                        </div>
                      </form>

                      <!-- Sección de diferencia -->
                      <div class="mt-3 p-3 border rounded" id="diferenciaSection" style="display: none;">
                        <h6 class="text-center">Diferencia</h6>
                        <h4 class="text-center mb-0" id="diferenciaMonto">RD$ 0.00</h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12">
                  <div class="card">
                    <div class="card-header">
                      <h5 class="mb-0">Movimientos de Caja</h5>
                    </div>
                    <div class="card-body">
                      <?php if (count($movimientos) > 0): ?>
                        <div class="table-responsive">
                          <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                              <tr>
                                <th>Fecha</th>
                                <th>Concepto</th>
                                <th>Tipo</th>
                                <th>Método</th>
                                <th class="text-right">Monto</th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php foreach ($movimientos as $movimiento): ?>
                                <tr>
                                  <td><?php echo date('d/m/Y H:i', strtotime($movimiento->fecha_movimiento)); ?></td>
                                  <td><?php echo $movimiento->concepto; ?></td>
                                  <td>
                                    <span class="badge badge-<?php echo $movimiento->tipo == 'Ingreso' ? 'success' : 'danger'; ?>">
                                      <?php echo $movimiento->tipo; ?>
                                    </span>
                                  </td>
                                  <td><?php echo $movimiento->metodo_pago; ?></td>
                                  <td class="text-right <?php echo $movimiento->tipo == 'Ingreso' ? 'text-success' : 'text-danger'; ?>">
                                    RD$ <?php echo number_format($movimiento->monto, 2); ?>
                                  </td>
                                </tr>
                              <?php endforeach; ?>
                            </tbody>
                          </table>
                        </div>
                      <?php else: ?>
                        <div class="alert alert-info">
                          <i class="fas fa-info-circle"></i> No hay movimientos registrados en esta caja.
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>

    <script>
      $(document).ready(function() {
        // Calcular diferencia en tiempo real
        $('#montoFinal').on('input', function() {
          var montoFinal = parseFloat($(this).val()) || 0;
          var totalEsperado = <?php echo json_encode($total_caja); ?>;
          var diferencia = montoFinal - totalEsperado;

          $('#diferenciaMonto').text('RD$ ' + diferencia.toFixed(2));

          // Colorear según si hay sobrante o faltante
          if (diferencia < 0) {
            $('#diferenciaSection').removeClass('border-success').addClass('border-danger');
            $('#diferenciaMonto').removeClass('text-success').addClass('text-danger');
          } else if (diferencia > 0) {
            $('#diferenciaSection').removeClass('border-danger').addClass('border-success');
            $('#diferenciaMonto').removeClass('text-danger').addClass('text-success');
          } else {
            $('#diferenciaSection').removeClass('border-danger border-success');
            $('#diferenciaMonto').removeClass('text-danger text-success');
          }

          $('#diferenciaSection').show();
        });
      });
    </script>

    <?php require_once('../partials/_scripts.php'); ?>
    <?php require_once('../partials/_footer.php'); ?>
</body>

</html>