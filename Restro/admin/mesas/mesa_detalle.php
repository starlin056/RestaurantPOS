<?php
if (session_status() === PHP_SESSION_NONE) session_start();

ob_start();
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/error.log');
error_reporting(E_ALL);

include('../config/config.php');
include('../config/checklogin.php');
check_login();

$usuario_es_mesero = ($_SESSION['rol'] == 'Mesero');
$usuario_es_cajero = ($_SESSION['rol'] == 'Cajero');
$usuario_es_admin  = ($_SESSION['rol'] == 'Administrador');

if (!isset($_GET['mesa'])) {
    $_SESSION['error'] = "Mesa no especificada";
    header("Location: ../mesas/mesas.php");
    exit;
}

$mesa_id = $_GET['mesa'];

// Manejo POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Agregar pedido
    if ($_POST['accion'] == 'agregar_pedido') {
        $prod_id  = $_POST['prod_id'];
        $cantidad = intval($_POST['cantidad'] ?? 1);
        $notas    = trim($_POST['notas'] ?? '');

        $stmt_producto = $mysqli->prepare("SELECT * FROM rpos_products WHERE prod_id=?");
        $stmt_producto->bind_param('s', $prod_id);
        $stmt_producto->execute();
        $producto = $stmt_producto->get_result()->fetch_object();

        if ($producto) {
            $order_id    = uniqid();
            $order_code  = strtoupper(substr(md5(time()), 0, 8));
            $customer_id = 'fe6bb69bdd29';
            $customer_name = "Mesa $mesa_id";

            $stmt_orden = $mysqli->prepare("INSERT INTO rpos_orders 
                (order_id, order_code, customer_id, customer_name, prod_id, prod_name, prod_price, prod_qty, order_status, notas)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente', ?)");
            $stmt_orden->bind_param(
                'sssssssis',
                $order_id,
                $order_code,
                $customer_id,
                $customer_name,
                $prod_id,
                $producto->prod_name,
                $producto->prod_price,
                $cantidad,
                $notas
            );

            if ($stmt_orden->execute()) {
                $orden_mesa_id = uniqid();
                $stmt_vinculo = $mysqli->prepare("INSERT INTO rpos_ordenes_mesas (orden_mesa_id, order_id, mesa_id, estado) VALUES (?, ?, ?, 'Activa')");
                $stmt_vinculo->bind_param('sss', $orden_mesa_id, $order_id, $mesa_id);
                $stmt_vinculo->execute();

                $stmt_check = $mysqli->prepare("SELECT COUNT(*) as total FROM rpos_ordenes_mesas WHERE mesa_id = ? AND estado='Activa'");
                $stmt_check->bind_param('s', $mesa_id);
                $stmt_check->execute();
                $total = $stmt_check->get_result()->fetch_object()->total;
                $stmt_check->close();

                if ($total == 1) {
                    $mesero_id = $_SESSION['user_id'];
                    $stmt_asignar = $mysqli->prepare("UPDATE rpos_mesas SET mesero_asignado = ?, estado='Ocupada' WHERE mesa_id = ?");
                    $stmt_asignar->bind_param('ss', $mesero_id, $mesa_id);
                    $stmt_asignar->execute();
                }

                $_SESSION['success'] = "Producto agregado correctamente";
            }
        }
        header("Location: mesa_detalle.php?mesa=$mesa_id");
        exit;
    }

    // Eliminar pedido
    if (isset($_POST['eliminar_pedido'])) {
        $order_id = $_POST['order_id'];
        $stmt_check = $mysqli->prepare("SELECT order_status FROM rpos_orders WHERE order_id = ?");
        $stmt_check->bind_param('s', $order_id);
        $stmt_check->execute();
        $orden = $stmt_check->get_result()->fetch_object();
        $stmt_check->close();

        if ($orden && $orden->order_status == 'Pendiente') {
            $mysqli->query("DELETE FROM rpos_orders WHERE order_id='$order_id'");
            $mysqli->query("DELETE FROM rpos_ordenes_mesas WHERE order_id='$order_id'");

            $stmt_checkMesa = $mysqli->prepare("SELECT COUNT(*) as total FROM rpos_ordenes_mesas WHERE mesa_id=? AND estado='Activa'");
            $stmt_checkMesa->bind_param('s', $mesa_id);
            $stmt_checkMesa->execute();
            $totalMesa = $stmt_checkMesa->get_result()->fetch_object()->total;
            $stmt_checkMesa->close();

            if ($totalMesa == 0) {
                $stmt_libera = $mysqli->prepare("UPDATE rpos_mesas SET estado='Disponible', mesero_asignado=NULL WHERE mesa_id=?");
                $stmt_libera->bind_param('s', $mesa_id);
                $stmt_libera->execute();
            }

            $_SESSION['success'] = "Pedido eliminado";
        }
        header("Location: mesa_detalle.php?mesa=$mesa_id");
        exit;
    }
}

// Datos mesa
$stmt_mesa = $mysqli->prepare("SELECT m.*, s.staff_name as mesero_nombre 
                               FROM rpos_mesas m 
                               LEFT JOIN rpos_staff s ON m.mesero_asignado = s.staff_id 
                               WHERE m.mesa_id=?");
$stmt_mesa->bind_param('s', $mesa_id);
$stmt_mesa->execute();
$mesa = $stmt_mesa->get_result()->fetch_object();
$stmt_mesa->close();

// Pedidos
$stmt_pedidos = $mysqli->prepare("SELECT o.*, p.prod_img, c.nombre_categoria 
  FROM rpos_orders o
  JOIN rpos_products p ON o.prod_id=p.prod_id
  LEFT JOIN rpos_categorias_productos c ON p.categoria_id=c.categoria_id
  JOIN rpos_ordenes_mesas om ON o.order_id=om.order_id
  WHERE om.mesa_id=? AND om.estado='Activa'
  ORDER BY o.order_status ASC, o.created_at DESC");
$stmt_pedidos->bind_param('s', $mesa_id);
$stmt_pedidos->execute();
$pedidos = $stmt_pedidos->get_result();

require_once('../partials/_head.php');
?>

<body>
<?php require_once('../partials/_sidebar.php'); ?>
<div class="main-content">
<?php require_once('../partials/_topnav.php'); ?>

<!-- Header / Encabezado de Mesa -->
<div class="header bg-gradient-primary pb-8 pt-5 pt-md-8">
  <div class="container-fluid">
    <div class="header-body">

      <!-- Breadcrumb y título -->
      <div class="row align-items-center py-4">
        <div class="col-lg-6 col-7">
          <h6 class="h2 text-white d-inline-block mb-0">Detalle de Mesa</h6>
          <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
            <ol class="breadcrumb breadcrumb-links breadcrumb-dark mb-0">
              <li class="breadcrumb-item"><a href="../dashboard.php"><i class="fas fa-home"></i></a></li>
              <li class="breadcrumb-item"><a href="mesas.php">Mesas</a></li>
              <li class="breadcrumb-item active" aria-current="page">Detalle</li>
            </ol>
          </nav>
        </div>

        <!-- Resumen de la mesa -->
        <div class="col-lg-6 col-5 text-right">
          <h3 class="text-white mb-0">Mesa #<?= htmlspecialchars($mesa->numero_mesa) ?></h3>
          <small class="text-white">Estado: <strong><?= htmlspecialchars($mesa->estado) ?></strong></small><br>
          <?php if (!empty($mesa->mesero_nombre)): ?>
            <small class="text-white">Mesero: <strong><?= htmlspecialchars($mesa->mesero_nombre) ?></strong></small>
          <?php endif; ?>
        </div>
      </div>

      <!-- Badges de pedidos -->
      <div class="row mt-3">
        <div class="col">
          <?php
          $pedidos->data_seek(0);
          $pendientes = $en_preparacion = $listos = 0;
          while ($p = $pedidos->fetch_object()) {
              if ($p->order_status=='Pendiente') $pendientes++;
              elseif ($p->order_status=='En preparación') $en_preparacion++;
              elseif ($p->order_status=='Listo') $listos++;
          }
          $pedidos->data_seek(0);
          ?>
          <span class="badge badge-pill badge-warning mr-2">Pendientes: <?= $pendientes ?></span>
          <span class="badge badge-pill badge-primary mr-2">En preparación: <?= $en_preparacion ?></span>
          <span class="badge badge-pill badge-info mr-3">Listos: <?= $listos ?></span>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Contenedor principal -->
<div class="container-fluid mt--7">


    <!-- Alertas -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-xl-8 mb-4">
            <div class="card shadow">
                <div class="card-header"><h3 class="mb-0">Pedidos de la mesa</h3></div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Img</th>
                                <th>Cant</th>
                                <th>Precio</th>
                                <th>Estado</th>
                                <th>Categoría</th>
                                <th>Notas</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($pedidos->num_rows > 0): while ($pedido = $pedidos->fetch_object()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($pedido->prod_name) ?></td>
                                    <td><img src="../assets/img/products/<?= $pedido->prod_img ?: 'default.jpg' ?>" style="width:50px;height:50px;object-fit:cover;" class="rounded"></td>
                                    <td><?= $pedido->prod_qty ?></td>
                                    <td>RD$ <?= number_format($pedido->prod_price,2) ?></td>
                                    <td>
                                        <?php
                                        $cls = 'badge-secondary';
                                        if ($pedido->order_status=='Pendiente') $cls='badge-warning';
                                        elseif ($pedido->order_status=='En preparación') $cls='badge-primary';
                                        elseif ($pedido->order_status=='Listo') $cls='badge-success';
                                        ?>
                                        <span class="badge <?= $cls ?>"><?= $pedido->order_status ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($pedido->nombre_categoria ?? '-') ?></td>
                                    <td><?= $pedido->notas ?: '-' ?></td>
                                    <td>
                                        <?php if ($pedido->order_status=='Pendiente'): ?>
                                            <form method="post">
                                                <input type="hidden" name="order_id" value="<?= $pedido->order_id ?>">
                                                <button type="submit" name="eliminar_pedido" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; else: ?>
                                <tr><td colspan="8" class="text-center">No hay pedidos</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-4 mb-4">
            <div class="card shadow">
                <div class="card-header"><h3>Acciones</h3></div>
                <div class="card-body">
                    <button class="btn btn-block btn-primary mb-3" data-toggle="modal" data-target="#modalAgregarPedido">
                        <i class="fas fa-plus"></i> Agregar productos
                    </button>
                    <a href="mesas.php" class="btn btn-block btn-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Modal Agregar Pedido -->
<div class="modal fade" id="modalAgregarPedido" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar producto</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Categoría</label>
                    <select id="filtroCategoria" class="form-control">
                        <option value="Todas">Todas</option>
                        <?php
                        $cats = $mysqli->query("SELECT nombre_categoria FROM rpos_categorias_productos ORDER BY nombre_categoria");
                        while ($cat = $cats->fetch_object()):
                        ?>
                        <option value="<?= htmlspecialchars($cat->nombre_categoria) ?>"><?= htmlspecialchars($cat->nombre_categoria) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <input type="text" id="buscarProducto" class="form-control" placeholder="Buscar producto...">
                </div>
                <div class="row" id="contenedorProductos"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cantidad -->
<div class="modal fade" id="modalCantidad" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formAgregarPedido" method="post">
                <div class="modal-header">
                    <h5 id="tituloProducto">Agregar producto</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="accion" value="agregar_pedido">
                    <input type="hidden" name="prod_id" id="prod_id">
                    <div class="text-center mb-3"><img id="imgProductoSeleccionado" src="" style="width:100px;height:100px;object-fit:cover;" class="rounded"></div>
                    <div class="form-group"><label>Cantidad</label><input type="number" name="cantidad" class="form-control" value="1" min="1" required></div>
                    <div class="form-group"><label>Notas</label><textarea name="notas" class="form-control"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Agregar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once('../partials/_footer.php'); ?>
<?php require_once('../partials/_scripts.php'); ?>

<script>
async function cargarProductos() {
    const categoria = document.getElementById("filtroCategoria").value;
    const busqueda = document.getElementById("buscarProducto").value;
    const res = await fetch(`productos_ajax.php?categoria=${encodeURIComponent(categoria)}&busqueda=${encodeURIComponent(busqueda)}`);
    const productos = await res.json();
    const cont = document.getElementById("contenedorProductos");
    cont.innerHTML = "";
    if (productos.length === 0) {
        cont.innerHTML = '<div class="col-12 text-center"><p>No hay productos</p></div>';
        return;
    }
    productos.forEach(p => {
        cont.innerHTML += `
      <div class="col-md-4 mb-3">
        <div class="card h-100 shadow-sm">
          <img src="../assets/img/products/${p.prod_img || 'default.jpg'}" class="card-img-top" style="height:160px;object-fit:cover;">
          <div class="card-body text-center">
            <h6 class="card-title mb-1">${p.prod_name}</h6>
            <p class="text-success font-weight-bold">RD$ ${parseFloat(p.prod_price).toFixed(2)}</p>
            <small class="text-muted">${p.nombre_categoria || ''}</small>
          </div>
          <div class="card-footer bg-transparent">
            <button class="btn btn-sm btn-primary btn-block" onclick="seleccionarProducto('${p.prod_id}','${p.prod_name}',${p.prod_price},'${p.prod_img || 'default.jpg'}')">
              <i class="fas fa-plus"></i> Agregar
            </button>
          </div>
        </div>
      </div>`;
    });
}
document.getElementById("filtroCategoria").addEventListener("change", cargarProductos);
document.getElementById("buscarProducto").addEventListener("keyup", cargarProductos);
$('#modalAgregarPedido').on('shown.bs.modal', cargarProductos);

function seleccionarProducto(id,nombre,precio,img){
    document.getElementById("prod_id").value = id;
    document.getElementById("tituloProducto").textContent = "Agregar: " + nombre;
    document.getElementById("imgProductoSeleccionado").src = "../assets/img/products/" + img;
    $('#modalAgregarPedido').modal('hide');
    $('#modalCantidad').modal('show');
}
document.getElementById("formAgregarPedido").addEventListener("submit", async function(e){
    e.preventDefault();
    const formData = new FormData(this);
    const res = await fetch("mesa_detalle.php?mesa=<?= $mesa_id; ?>", {method:"POST", body:formData});
    if (res.redirected) window.location.href = res.url;
});
</script>
</div>
</body>
</html>
