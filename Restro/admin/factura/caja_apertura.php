<?php
// caja_apertura.php
session_start();
include('../config/config.php');
include('../config/checklogin.php');
include_once('../auditoria/funciones.php');
check_login();




// Verificar si ya hay una caja abierta
$query = "SELECT * FROM rpos_caja WHERE estado = 'Abierta' AND usuario_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('s', $_SESSION['user_id']);
$stmt->execute();
$caja_abierta = $stmt->get_result()->fetch_object();
$stmt->close();

// Procesar apertura de caja
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['apertura'])) {
  $monto_inicial = floatval($_POST['monto_inicial']);
  $observaciones = trim($_POST['observaciones']);

  if ($caja_abierta) {
    $_SESSION['error'] = "Ya tienes una caja abierta";
    header("Location: /RestaurantPOS/Restro/admin/factura/mesa_pagos.php");
    exit;
  }

  $caja_id = uniqid("CAJA");

  $query = "INSERT INTO rpos_caja (caja_id, usuario_id, monto_inicial, observaciones) 
              VALUES (?, ?, ?, ?)";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param('ssds', $caja_id, $_SESSION['user_id'], $monto_inicial, $observaciones);

  if ($stmt->execute()) {
    $_SESSION['success'] = "Caja abierta correctamente con RD$ " . number_format($monto_inicial, 2);
    header("Location: /RestaurantPOS/Restro/admin/factura/mesa_pagos.php");
    exit;
  } else {
    $_SESSION['error'] = "Error al abrir la caja";
  }
  $stmt->close();
}

registrar_auditoria("Abrir caja", "Usuario abrió la caja número 1");

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
        <div class="col-md-6 mx-auto">
          <div class="card shadow">
            <div class="card-header border-0">
              <h3 class="mb-0"><?php echo $caja_abierta ? 'Caja Abierta' : 'Apertura de Caja'; ?></h3>
            </div>
            <div class="card-body">
              <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error'];
                                                unset($_SESSION['error']); ?></div>
              <?php endif; ?>

              <?php if ($caja_abierta): ?>
                <div class="alert alert-info">
                  <h4><i class="fas fa-cash-register"></i> Caja Actualmente Abierta</h4>
                  <p><strong>Monto Inicial:</strong> RD$ <?php echo number_format($caja_abierta->monto_inicial, 2); ?></p>
                  <p><strong>Fecha Apertura:</strong> <?php echo date('d/m/Y H:i', strtotime($caja_abierta->fecha_apertura)); ?></p>
                  <p><strong>Observaciones:</strong> <?php echo $caja_abierta->observaciones; ?></p>
                </div>
                <div class="text-center">
                  <a href="caja_cierre.php" class="btn btn-primary">Ir a Cierre de Caja</a>
                  <a href="mesa_pagos.php" class="btn btn-success">Ir a Pagos</a>
                </div>
              <?php else: ?>
                <form method="post">
                  <div class="form-group">
                    <label for="monto_inicial">Monto Inicial *</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">RD$</span>
                      </div>
                      <input type="number" step="0.01" class="form-control" name="monto_inicial" required
                        placeholder="0.00" min="0">
                    </div>
                  </div>

                  <div class="form-group">
                    <label for="observaciones">Observaciones</label>
                    <textarea class="form-control" name="observaciones" rows="3"
                      placeholder="Observaciones sobre el monto inicial"></textarea>
                  </div>

                  <div class="text-center">
                    <button type="submit" name="apertura" class="btn btn-primary">Abrir Caja</button>
                  </div>
                </form>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php require_once('../partials/_scripts.php'); ?>
  <?php require_once('../partials/_footer.php'); ?>
</body>

</html>