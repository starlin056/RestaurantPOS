<?php
session_start();
include('config/config.php');
include('config/check_login.php');
check_login();
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
        <div class="header-body">
          <!-- Aquí puedes poner contenido adicional -->
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
              Reportes de Pagos
            </div>
            <div class="table-responsive">
              <table class="table align-items-center table-flush">
                <thead class="thead-light">
                  <tr>
                    <th class="text-success" scope="col">Código de Pago</th>
                    <th scope="col">Método de Pago</th>
                    <th class="text-success" scope="col">Código de Orden</th>
                    <th scope="col">Monto Pagado</th>
                    <th class="text-success" scope="col">Fecha de Pago</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $query = "SELECT * FROM rpos_payments ORDER BY created_at DESC";
                  $stmt = $mysqli->prepare($query);
                  $stmt->execute();
                  $result = $stmt->get_result();

                  while ($payment = $result->fetch_object()) {
                  ?>
                    <tr>
                      <th class="text-success" scope="row"><?php echo htmlspecialchars($payment->pay_code); ?></th>
                      <td><?php echo htmlspecialchars($payment->pay_method); ?></td>
                      <td class="text-success"><?php echo htmlspecialchars($payment->order_code); ?></td>
                      <td>$ <?php echo number_format($payment->pay_amt, 2, ',', '.'); ?></td>
                      <td class="text-success"><?php echo date('d/M/Y g:i a', strtotime($payment->created_at)); ?></td>
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
