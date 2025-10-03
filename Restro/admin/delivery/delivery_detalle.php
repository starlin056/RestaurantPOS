<?php
session_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/error.log');

include('../config/config.php');
include('../config/checklogin.php');
check_login();

if (!isset($_GET['delivery_id'])) {
  header("Location: delivery_listado.php");
  exit;
}

$delivery_id = $_GET['delivery_id'];

// Obtener pedido y cliente
$stmt = $mysqli->prepare("
    SELECT d.*, 
           c.customer_name, c.customer_phoneno, c.direccion_fiscal AS customer_address,
           s.staff_name AS repartidor_nombre
    FROM rpos_delivery_orders d
    LEFT JOIN rpos_customers c ON d.cliente_id = c.customer_id
    LEFT JOIN rpos_staff s ON d.repartidor_id = s.staff_id
    WHERE d.delivery_id = ?
");
$stmt->bind_param("s", $delivery_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
  die("Pedido no encontrado");
}
$pedido = $result->fetch_assoc();
$stmt->close();

// Obtener productos del pedido
$productos_pedido = [];
$pp_query = $mysqli->query("SELECT di.*, p.prod_img 
                            FROM rpos_delivery_items di
                            LEFT JOIN rpos_products p ON di.prod_id = p.prod_id
                            WHERE di.delivery_id = '$delivery_id'");
while ($row = $pp_query->fetch_assoc()) {
  $productos_pedido[] = [
    'prod_id' => $row['prod_id'],
    'prod_name' => $row['prod_name'],
    'prod_price' => floatval($row['prod_price']),
    'cantidad' => intval($row['prod_qty']),
    'notas' => $row['notas'],
    'prod_img' => $row['prod_img'] ?: 'default.jpg'
  ];
}

// Badge helper
function badgeClass($estado)
{
  switch (trim($estado)) {
    case 'Recibido':
      return 'warning';
    case 'En preparación':
      return 'info';
    case 'En camino':
      return 'primary';
    case 'Entregado':
      return 'success';
    case 'Cancelado':
      return 'danger';
    default:
      return 'secondary';
  }
}
?>

<?php require_once('../partials/_head.php'); ?>

<style>
  /* Sidebar + Main content layout igual que listado */
  :root {
    --sidebar-width: 260px;
  }

  #app-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    width: var(--sidebar-width);
    z-index: 1030;
    overflow-y: auto;
  }

  .main-content {
    margin-left: var(--sidebar-width);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    background-color: #f8f9fe;
    padding-top: 0;
  }

  @media(max-width:991.98px) {
    #app-sidebar {
      transform: translateX(-100%);
      transition: .3s;
    }

    body.sidebar-open #app-sidebar {
      transform: translateX(0);
    }

    .main-content {
      margin-left: 0;
    }
  }
</style>

<body>
  <div class="wrapper">
    <aside id="app-sidebar">
      <?php require_once('../partials/_sidebar.php'); ?>
    </aside>

    <div class="main-content">
      <?php require_once('../partials/_topnav.php'); ?>

      <!-- Header morado -->
      <div class="header bg-gradient-primary pb-6 pt-5 pt-md-6">
        <div class="container-fluid">
          <div class="header-body">
            <div class="row align-items-center py-4">
              <div class="col-lg-6 col-7">
                <h1 class="text-white display-4">📄 Detalle de Pedido</h1>
                <p class="text-white mb-0">Visualiza la información del cliente y los productos del pedido</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Contenido -->
      <section class="content">
        <div class="container-fluid py-4">
          <div class="row">
            <!-- Información Cliente -->
            <div class="col-md-6">
              <div class="card shadow mb-3">
                <div class="card-header bg-primary text-white">Datos del Cliente</div>
                <div class="card-body">
                  <p><strong>Nombre:</strong> <?= htmlspecialchars($pedido['customer_name']) ?></p>
                  <p><strong>Teléfono:</strong> <?= htmlspecialchars($pedido['customer_phoneno']) ?></p>
                  <p><strong>Dirección:</strong> <?= htmlspecialchars($pedido['customer_address']) ?></p>
                  <p><strong>Repartidor:</strong> <?= htmlspecialchars($pedido['repartidor_nombre'] ?? 'Sin asignar') ?></p>
                  <p><strong>Estado:</strong>
                    <span class="badge badge-<?= badgeClass($pedido['estado']) ?>"><?= htmlspecialchars($pedido['estado']) ?></span>
                  </p>
                  <p><strong>Notas:</strong> <?= htmlspecialchars($pedido['notas']) ?></p>
                </div>
              </div>
            </div>

            <!-- Productos -->
            <div class="col-md-6">
              <div class="card shadow mb-3">
                <div class="card-header bg-success text-white">Productos del Pedido</div>
                <div class="card-body">
                  <?php if (count($productos_pedido) == 0): ?>
                    <p>No hay productos agregados al pedido</p>
                  <?php else: ?>
                    <?php foreach ($productos_pedido as $p): ?>
                      <div class="d-flex mb-2 align-items-center border p-2 rounded">
                        <img src="../assets/img/products/<?= $p['prod_img'] ?>" width="50" class="me-2">
                        <div class="flex-grow-1">
                          <strong><?= $p['prod_name'] ?></strong><br>
                          <small>$<?= number_format($p['prod_price'], 2) ?> x <?= $p['cantidad'] ?></small><br>
                          <?php if ($p['notas']): ?><small>Notas: <?= htmlspecialchars($p['notas']) ?></small><?php endif; ?>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
              </div>

              <!-- Acciones: Descargar PDF / Vista previa -->
              <div class="card mt-3">
                <div class="card-header">
                  <h3 class="card-title">Acciones</h3>
                </div>
                <div class="card-body">
                  <a href="delivery_conduce.php?delivery_id=<?= $delivery_id ?>"
                    target="_blank" class="btn btn-info btn-lg btn-block mb-2">
                    <i class="fas fa-download me-2"></i>
                    Descargar PDF Conduce
                  </a>
                
                </div>
              </div>
            </div>


            <?php require_once('../partials/_footer.php'); ?>
          </div>
        </div>

        <?php require_once('../partials/_scripts.php'); ?>
</body>

</html>