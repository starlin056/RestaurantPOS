<?php
session_start();
include('config/config.php');
include('config/check_login.php');
include('config/code-generator.php');

check_login();

if (isset($_POST['pay'])) {
  // Evitar valores en blanco
  if (empty($_POST["pay_code"]) || empty($_POST["pay_amt"]) || empty($_POST['pay_method'])) {
    $err = "No se permiten campos vacíos";
  } else {
    $pay_id = $_POST['pay_id'];
    $pay_code = $_POST['pay_code'];
    $order_code = $_GET['order_code'];
    $customer_id = $_GET['customer_id'];
    $pay_amt = $_POST['pay_amt'];
    $pay_method = $_POST['pay_method'];
    $order_status = $_GET['order_status'];

    // Insertar pago
    $insertPayment = "INSERT INTO rpos_payments (pay_id, pay_code, order_code, customer_id, pay_amt, pay_method) VALUES (?, ?, ?, ?, ?, ?)";
    $updateOrder = "UPDATE rpos_orders SET order_status = ? WHERE order_code = ?";

    $stmtInsert = $mysqli->prepare($insertPayment);
    $stmtUpdate = $mysqli->prepare($updateOrder);

    $stmtInsert->bind_param('ssssss', $pay_id, $pay_code, $order_code, $customer_id, $pay_amt, $pay_method);
    $stmtUpdate->bind_param('ss', $order_status, $order_code);

    $stmtInsert->execute();
    $stmtUpdate->execute();

    if ($stmtInsert && $stmtUpdate) {
      $success = "Pago realizado correctamente";
      header("refresh:1; url=receipts.php");
    } else {
      $err = "Error. Intente nuevamente más tarde.";
    }
  }
}

require_once('partials/_head.php');
?>

<body>
  <!-- Barra lateral -->
  <?php require_once('partials/_sidebar.php'); ?>

  <!-- Contenido principal -->
  <div class="main-content">
    <!-- Barra superior -->
    <?php require_once('partials/_topnav.php');

    $order_code = $_GET['order_code'];
    $customer_id = $_GET['customer_id'] ?? '';
    $order_status = $_GET['order_status'] ?? 'Pagado';

    // Obtener datos del pedido
    $query = "SELECT * FROM rpos_orders WHERE order_code = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('s', $order_code);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_object();

    if (!$order) {
      echo "<div class='container mt-5'><div class='alert alert-danger'>Pedido no encontrado.</div></div>";
      exit;
    }

    $total = $order->prod_price * $order->prod_qty;
    ?>

    <!-- Encabezado -->
    <div style="background-image: url(assets/img/theme/restro00.jpg); background-size: cover;" class="header pb-8 pt-5 pt-md-8">
      <span class="mask bg-gradient-dark opacity-8"></span>
      <div class="container-fluid">
        <div class="header-body">
          <!-- Puedes agregar contenido adicional aquí -->
        </div>
      </div>
    </div>

    <!-- Contenido -->
    <div class="container-fluid mt--8">
      <div class="row">
        <div class="col">
          <div class="card shadow-sm">
            <div class="card-header border-0">
              <h3>Por favor, complete todos los campos</h3>
            </div>
            <div class="card-body">
              <?php if (isset($err)) : ?>
                <div class="alert alert-danger"><?php echo $err; ?></div>
              <?php elseif (isset($success)) : ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
              <?php endif; ?>

              <form method="POST" enctype="multipart/form-data">
                <div class="form-row">
                  <div class="col-md-6 mb-3">
                    <label>ID de Pago</label>
                    <input type="text" name="pay_id" readonly value="<?php echo htmlspecialchars($payid ?? ''); ?>" class="form-control" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label>Código de Pago</label>
                    <input type="text" name="pay_code" value="<?php echo htmlspecialchars($mpesaCode ?? ''); ?>" class="form-control" required>
                  </div>
                </div>

                <hr>

                <div class="form-row">
                  <div class="col-md-6 mb-3">
                    <label>Monto ($)</label>
                    <input type="text" name="pay_amt" readonly value="<?php echo number_format($total, 2, ',', '.'); ?>" class="form-control" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label>Método de Pago</label>
                    <select class="form-control" name="pay_method" required>
                      <option value="Efectivo" selected>Efectivo</option>
                      <option value="Paypal">Paypal</option>
                    </select>
                  </div>
                </div>

                <button type="submit" name="pay" class="btn btn-success">Pagar Orden</button>
              </form>
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
