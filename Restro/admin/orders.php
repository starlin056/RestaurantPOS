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

    <!-- Encabezado con imagen -->
    <div style="background-image: url(assets/img/theme/restro00.jpg); background-size: cover;" class="header pb-8 pt-5 pt-md-8">
      <span class="mask bg-gradient-dark opacity-8"></span>
      <div class="container-fluid">
        <div class="header-body">
          <!-- Aquí puedes agregar más contenido si deseas -->
        </div>
      </div>
    </div>

    <!-- Contenido principal -->
    <div class="container-fluid mt--8">
      <div class="row">
        <div class="col">
          <div class="card shadow-sm">
            <div class="card-header border-0">
              <h3 class="mb-0">Seleccione cualquier producto para realizar un pedido</h3>
            </div>
            <div class="table-responsive">
              <table class="table align-items-center table-flush">
                <thead class="thead-light">
                  <tr>
                    <th scope="col"><b>Imagen</b></th>
                    <th scope="col"><b>Código del Producto</b></th>
                    <th scope="col"><b>Nombre</b></th>
                    <th scope="col"><b>Precio</b></th>
                    <th scope="col"><b>Acción</b></th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $ret = "SELECT * FROM rpos_products";
                  $stmt = $mysqli->prepare($ret);
                  $stmt->execute();
                  $res = $stmt->get_result();
                  while ($prod = $res->fetch_object()) {
                  ?>
                    <tr>
                      <td>
                        <?php
                        if ($prod->prod_img) {
                          echo "<img src='assets/img/products/$prod->prod_img' height='60' width='60' class='img-thumbnail'>";
                        } else {
                          echo "<img src='assets/img/products/default.jpg' height='60' width='60' class='img-thumbnail'>";
                        }
                        ?>
                      </td>
                      <td><?php echo htmlspecialchars($prod->prod_code); ?></td>
                      <td><?php echo htmlspecialchars($prod->prod_name); ?></td>
                      <td>$ <?php echo number_format($prod->prod_price, 2, ',', '.'); ?></td>
                      <td>
                        <a href="make_order.php?prod_id=<?php echo $prod->prod_id; ?>&prod_name=<?php echo urlencode($prod->prod_name); ?>&prod_price=<?php echo $prod->prod_price; ?>">
                          <button class="btn btn-sm btn-warning">
                            <i class="fas fa-cart-plus"></i> Realizar Pedido
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
