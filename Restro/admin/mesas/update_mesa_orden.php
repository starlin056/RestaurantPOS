<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ob_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/error.log');
error_reporting(E_ALL);
include('../config/config.php');
include('../config/checklogin.php');
check_login();

// Obtener mesa y orden si se pasa mesa_id
if (isset($_GET['mesa_id'])) {
    $mesa_id = $_GET['mesa_id'];

    // Obtener datos de la mesa
    $query = "SELECT * FROM rpos_mesas WHERE mesa_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('s', $mesa_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $mesa = $res->fetch_object();
    $stmt->close();

    // Obtener órdenes de esta mesa
    $query2 = "SELECT * FROM rpos_orders WHERE mesa_id = ? ORDER BY order_id DESC";
    $stmt2 = $mysqli->prepare($query2);
    $stmt2->bind_param('s', $mesa_id);
    $stmt2->execute();
    $orders = $stmt2->get_result();
    $stmt2->close();
}

// ACTUALIZAR MESA
if (isset($_POST['update_mesa'])) {
    $numero_mesa = intval($_POST['numero_mesa']);
    $capacidad = intval($_POST['capacidad']);
    $ubicacion = $_POST['ubicacion'];
    $estado = $_POST['estado'];

    $check = "SELECT * FROM rpos_mesas WHERE numero_mesa = ? AND mesa_id != ?";
    $stmt = $mysqli->prepare($check);
    $stmt->bind_param('is', $numero_mesa, $mesa_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $stmt->close();

    if ($res->num_rows > 0) {
        $err = "El número de mesa ya está en uso por otra mesa";
    } else {
        $query = "UPDATE rpos_mesas SET numero_mesa = ?, capacidad = ?, ubicacion = ?, estado = ? WHERE mesa_id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('iisss', $numero_mesa, $capacidad, $ubicacion, $estado, $mesa_id);
        $stmt->execute();
        $stmt->close();
        if ($stmt) {
            $success = "Mesa actualizada correctamente";
            header("refresh:1; url=update_mesa_orden.php?mesa_id=$mesa_id");
        } else {
            $err = "Error al actualizar mesa";
        }
    }
}

// AGREGAR PRODUCTO A ORDEN
if(isset($_POST['agregar_producto'])){
    $order_id = $_POST['order_id'];
    $prod_id = $_POST['prod_id'];
    $prod_name = $_POST['prod_name'];
    $prod_price = $_POST['prod_price'];
    $prod_qty = $_POST['prod_qty'];
    $notas = $_POST['notas'] ?? null;

    $query = "INSERT INTO rpos_order_items (order_id, prod_id, prod_name, prod_price, prod_qty, notas) 
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('sssdis', $order_id, $prod_id, $prod_name, $prod_price, $prod_qty, $notas);
    $stmt->execute();
    $stmt->close();
    $success = "Producto agregado a la orden";
}

// ENVIAR ORDEN A PREPARAR Y MESA OCUPADA
if(isset($_POST['enviar_preparar'])){
    $order_id = $_POST['order_id'];

    // Cambiar estado de orden
    $query1 = "UPDATE rpos_orders SET estado = 'Preparación' WHERE order_id = ?";
    $stmt1 = $mysqli->prepare($query1);
    $stmt1->bind_param('s', $order_id);
    $stmt1->execute();
    $stmt1->close();

    // Cambiar estado de mesa
    $query2 = "UPDATE rpos_mesas SET estado = 'Ocupada' WHERE mesa_id = ?";
    $stmt2 = $mysqli->prepare($query2);
    $stmt2->bind_param('s', $mesa_id);
    $stmt2->execute();
    $stmt2->close();

    $success = "Orden enviada a preparar y mesa marcada como ocupada";
}

// OBTENER DETALLE DE ORDENES POR MESA
$detalle_ordenes = [];
if(isset($mesa_id)){
    $query = "SELECT o.order_id, o.estado AS estado_orden, i.prod_name, i.prod_qty, i.prod_price, i.notas
              FROM rpos_orders o
              LEFT JOIN rpos_order_items i ON o.order_id = i.order_id
              WHERE o.mesa_id = ?
              ORDER BY o.order_id, i.item_id";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('s', $mesa_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while($row = $res->fetch_assoc()){
        $detalle_ordenes[] = $row;
    }
    $stmt->close();
}

require_once('../partials/_head.php');
?>

<body>
<?php require_once('../partials/_sidebar.php'); ?>
<div class="main-content">
<?php require_once('../partials/_topnav.php'); ?>

<div class="container-fluid mt--8">
    <div class="row">
        <div class="col-md-12">

            <!-- FORMULARIO MESA -->
            <div class="card shadow">
                <div class="card-header">
                    <h5>Editar Mesa #<?php echo $mesa->numero_mesa ?? ''; ?></h5>
                </div>
                <div class="card-body">
                    <?php if(isset($err)){ echo "<div class='alert alert-danger'>$err</div>"; } ?>
                    <?php if(isset($success)){ echo "<div class='alert alert-success'>$success</div>"; } ?>
                    <form method="post">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Número de Mesa</label>
                                <input type="number" class="form-control" name="numero_mesa" value="<?php echo $mesa->numero_mesa ?? ''; ?>" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Capacidad</label>
                                <input type="number" class="form-control" name="capacidad" min="1" value="<?php echo $mesa->capacidad ?? ''; ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Ubicación</label>
                            <select class="form-control" name="ubicacion" required>
                                <option value="Interior" <?= ($mesa->ubicacion=='Interior')?'selected':''; ?>>Interior</option>
                                <option value="Terraza" <?= ($mesa->ubicacion=='Terraza')?'selected':''; ?>>Terraza</option>
                                <option value="Barra" <?= ($mesa->ubicacion=='Barra')?'selected':''; ?>>Barra</option>
                                <option value="Sala VIP" <?= ($mesa->ubicacion=='Sala VIP')?'selected':''; ?>>Sala VIP</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Estado</label>
                            <select class="form-control" name="estado" required>
                                <option value="Disponible" <?= ($mesa->estado=='Disponible')?'selected':''; ?>>Disponible</option>
                                <option value="Ocupada" <?= ($mesa->estado=='Ocupada')?'selected':''; ?>>Ocupada</option>
                                <option value="Reservada" <?= ($mesa->estado=='Reservada')?'selected':''; ?>>Reservada</option>
                                <option value="Mantenimiento" <?= ($mesa->estado=='Mantenimiento')?'selected':''; ?>>Mantenimiento</option>
                            </select>
                        </div>
                        <button type="submit" name="update_mesa" class="btn btn-primary">Actualizar Mesa</button>
                    </form>
                </div>
            </div>

            <!-- LISTADO ORDENES -->
            <div class="card shadow mt-4">
                <div class="card-header">
                    <h5>Órdenes de la Mesa</h5>
                </div>
                <div class="card-body">
                    <?php if(!empty($detalle_ordenes)): ?>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Orden</th>
                                    <th>Estado</th>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio</th>
                                    <th>Notas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($detalle_ordenes as $d): ?>
                                    <tr>
                                        <td><?php echo $d['order_id']; ?></td>
                                        <td><?php echo $d['estado_orden']; ?></td>
                                        <td><?php echo $d['prod_name']; ?></td>
                                        <td><?php echo $d['prod_qty']; ?></td>
                                        <td><?php echo $d['prod_price']; ?></td>
                                        <td><?php echo $d['notas']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No hay órdenes para esta mesa</p>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once('../partials/_footer.php'); ?>
</div>
<?php require_once('../partials/_scripts.php'); ?>
</body>
</html>
