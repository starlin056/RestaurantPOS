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

// Obtener repartidores activos
$repartidores = $mysqli->query("SELECT staff_id, staff_name FROM rpos_staff WHERE rol='Delivery' AND estado='Activo'");

// Badge helper
function badgeClass($estado) {
    switch (trim($estado)) {
        case 'Recibido': return 'warning';
        case 'En preparación': return 'info';
        case 'En camino': return 'primary';
        case 'Entregado': return 'success';
        case 'Cancelado': return 'danger';
        default: return 'secondary';
    }
}

// =======================================
// ACTUALIZAR EL PEDIDO
// =======================================
if (isset($_POST['update_order'])) {
    $cliente_nombre   = $_POST['cliente_nombre'];
    $cliente_telefono = $_POST['cliente_telefono'];
    $cliente_direccion = $_POST['cliente_direccion'];
    $estado           = $_POST['estado'];
    $repartidor_id    = $_POST['repartidor_id'];
    $notas            = $_POST['notas'];

    // Actualizar datos del cliente
    $updateCliente = "UPDATE rpos_customers 
                      SET customer_name=?, customer_phoneno=?, direccion_fiscal=? 
                      WHERE customer_id=?";
    $stmtCliente = $mysqli->prepare($updateCliente);
    $stmtCliente->bind_param(
        'ssss',
        $cliente_nombre,
        $cliente_telefono,
        $cliente_direccion,
        $pedido['cliente_id']
    );
    $stmtCliente->execute();

    // Actualizar pedido
    $updateOrder = "UPDATE rpos_delivery_orders 
                    SET estado=?, repartidor_id=?, notas=? 
                    WHERE delivery_id=?";
    $stmtOrder = $mysqli->prepare($updateOrder);
    $stmtOrder->bind_param('siss', $estado, $repartidor_id, $notas, $delivery_id);
    $stmtOrder->execute();

    if ($stmtCliente && $stmtOrder) {
        $success = "Pedido actualizado correctamente";
        // Recargar pedido actualizado
        header("refresh:2; url=delivery_editar.php?delivery_id=$delivery_id");
    } else {
        $err = "Error al actualizar pedido";
    }
}
?>

<?php require_once('../partials/_head.php'); ?>

<style>
  :root { --sidebar-width: 260px; }
  #app-sidebar { position: fixed; top: 0; left: 0; bottom: 0; width: var(--sidebar-width); z-index: 1030; overflow-y: auto; }
  .main-content { margin-left: var(--sidebar-width); min-height: 100vh; display: flex; flex-direction: column; background-color: #f8f9fe; padding-top: 0; }
  @media(max-width:991.98px){#app-sidebar{transform:translateX(-100%);transition:.3s;}body.sidebar-open #app-sidebar{transform:translateX(0);} .main-content{margin-left:0;}}
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
              <h1 class="text-white display-4">✏️ Editar Pedido Delivery</h1>
              <p class="text-white mb-0">Modifica datos del cliente, asigna repartidor y actualiza estado</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Contenido -->
    <section class="content">
      <div class="container-fluid py-4">
        <?php if(isset($success)) { ?>
          <div class="alert alert-success"><?= $success ?></div>
        <?php } ?>
        <?php if(isset($err)) { ?>
          <div class="alert alert-danger"><?= $err ?></div>
        <?php } ?>

        <div class="row">
          <!-- Información Cliente -->
          <div class="col-md-6">
            <div class="card shadow mb-3">
              <div class="card-header bg-primary text-white">Datos del Cliente</div>
              <div class="card-body">
                <form method="POST">
                  <div class="mb-3">
                    <label>Nombre:</label>
                    <input type="text" name="cliente_nombre" class="form-control" required value="<?= htmlspecialchars($pedido['customer_name']) ?>">
                  </div>
                  <div class="mb-3">
                    <label>Teléfono:</label>
                    <input type="text" name="cliente_telefono" class="form-control" required value="<?= htmlspecialchars($pedido['customer_phoneno']) ?>">
                  </div>
                  <div class="mb-3">
                    <label>Dirección:</label>
                    <textarea name="cliente_direccion" class="form-control" rows="2" required><?= htmlspecialchars($pedido['customer_address']) ?></textarea>
                  </div>
                  <div class="mb-3">
                    <label>Estado del Pedido:</label>
                    <select name="estado" class="form-control" required>
                      <?php
                      $estados = ['Recibido','En preparación','En camino','Entregado','Cancelado','Pendiente'];
                      foreach($estados as $e){
                        $sel = ($pedido['estado']==$e)?'selected':'';
                        echo "<option value='$e' $sel>$e</option>";
                      }
                      ?>
                    </select>
                  </div>
                  <div class="mb-3">
                    <label>Repartidor:</label>
                    <select name="repartidor_id" class="form-control">
                      <option value="">Sin asignar</option>
                      <?php while($r = $repartidores->fetch_assoc()): ?>
                        <option value="<?= $r['staff_id'] ?>" <?= ($pedido['repartidor_id']==$r['staff_id'])?'selected':'' ?>><?= htmlspecialchars($r['staff_name']) ?></option>
                      <?php endwhile; ?>
                    </select>
                  </div>
                  <div class="mb-3">
                    <label>Notas:</label>
                    <textarea name="notas" class="form-control" rows="2"><?= htmlspecialchars($pedido['notas']) ?></textarea>
                  </div>
                  <button type="submit" name="update_order" class="btn btn-success">Actualizar Pedido</button>
                  <button type="button" class="btn btn-secondary" onclick="history.back();">Volver Atrás</button>

                </form>
              </div>
            </div>
          </div>

          <!-- Productos -->
          <div class="col-md-6">
            <div class="card shadow mb-3">
              <div class="card-header bg-success text-white">Productos del Pedido</div>
              <div class="card-body">
                <?php if(count($productos_pedido)==0): ?>
                  <p>No hay productos agregados al pedido</p>
                <?php else: ?>
                  <?php foreach($productos_pedido as $p): ?>
                    <div class="d-flex mb-2 align-items-center border p-2 rounded">
                      <img src="../assets/img/products/<?= $p['prod_img'] ?>" width="50" class="me-2">
                      <div class="flex-grow-1">
                        <strong><?= $p['prod_name'] ?></strong><br>
                        <small>$<?= number_format($p['prod_price'],2) ?> x <?= $p['cantidad'] ?></small><br>
                        <?php if($p['notas']): ?><small>Notas: <?= htmlspecialchars($p['notas']) ?></small><?php endif; ?>
                      </div>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <?php require_once('../partials/_footer.php'); ?>
  </div>
</div>

<?php require_once('../partials/_scripts.php'); ?>
</body>
</html>
