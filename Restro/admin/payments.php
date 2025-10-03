<?php
session_start();
include('config/config.php');
include('config/check_login.php');
check_login();

// Cancelar orden
if (isset($_GET['cancel'])) {
    $id = $_GET['cancel'];
    $query = "DELETE FROM rpos_orders WHERE order_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $stmt->close();
    if ($stmt) {
        $success = "Orden eliminada correctamente.";
        header("refresh:1; url=payments.php");
    } else {
        $err = "Inténtalo de nuevo más tarde.";
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
    <?php require_once('partials/_topnav.php'); ?>

    <!-- Encabezado -->
    <div style="background-image: url(assets/img/theme/restro00.jpg); background-size: cover;" class="header pb-8 pt-5 pt-md-8">
      <span class="mask bg-gradient-dark opacity-8"></span>
      <div class="container-fluid">
        <div class="header-body"></div>
      </div>
    </div>

    <!-- Contenido -->
    <div class="container-fluid mt--8">
      <!-- Tabla de órdenes pendientes -->
      <div class="row">
        <div class="col">
          <div class="card shadow">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
              <h3 class="mb-0">Órdenes Pendientes de Pago</h3>
              <a href="orders.php" class="btn btn-outline-success">
                <i class="fas fa-plus"></i> <i class="fas fa-utensils"></i> Hacer Nueva Orden
              </a>
            </div>

            <div class="table-responsive">
              <table class="table align-items-center table-flush">
                <thead class="thead-light">
                  <tr>
                    <th>Código</th>
                    <th>Cliente</th>
                    <th>Producto</th>
                    <th>Precio Total</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $query = "SELECT * FROM rpos_orders WHERE order_status = '' ORDER BY created_at DESC";
                  $stmt = $mysqli->prepare($query);
                  $stmt->execute();
                  $res = $stmt->get_result();
                  while ($order = $res->fetch_object()) {
                      $total = $order->prod_price * $order->prod_qty;
                  ?>
                    <tr>
                      <td class="text-success"><?php echo htmlspecialchars($order->order_code); ?></td>
                      <td><?php echo htmlspecialchars($order->customer_name); ?></td>
                      <td><?php echo htmlspecialchars($order->prod_name); ?></td>
                      <td>$ <?php echo number_format($total, 2, ',', '.'); ?></td>
                      <td><?php echo date('d/M/Y g:i a', strtotime($order->created_at)); ?></td>
                      <td>
                        <a href="pay_order.php?order_code=<?php echo urlencode($order->order_code); ?>&customer_id=<?php echo urlencode($order->customer_id); ?>&order_status=Paid">
                          <button class="btn btn-sm btn-success">
                            <i class="fas fa-handshake"></i> Pagar Orden
                          </button>
                        </a>
                        <a href="payments.php?cancel=<?php echo urlencode($order->order_id); ?>" onclick="return confirm('¿Estás seguro de cancelar esta orden?');">
                          <button class="btn btn-sm btn-danger">
                            <i class="fas fa-window-close"></i> Cancelar Orden
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

      <!-- Footer -->
      <?php require_once('partials/_footer.php'); ?>
    </div>
  </div>

  <!-- Scripts -->
  <?php require_once('partials/_scripts.php'); ?>
</body>
</html>
