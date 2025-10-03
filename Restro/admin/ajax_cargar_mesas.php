<?php
session_start();
include('config/config.php');
include('config/check_login.php');
check_login();

// Verificar que hay caja abierta para el usuario actual
$query = "SELECT * FROM rpos_caja WHERE estado = 'Abierta' AND usuario_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('s', $_SESSION['user_id']);
$stmt->execute();
$caja_abierta = $stmt->get_result()->fetch_object();
$stmt->close();

// Obtener configuración de la empresa
$query = "SELECT * FROM rpos_configuracion WHERE config_id = 1";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$config = $stmt->get_result()->fetch_object();
$stmt->close();

$query = "SELECT m.mesa_id, m.numero_mesa, m.ubicacion, m.num_personas, 
          COUNT(o.order_id) as num_ordenes,
          SUM(o.prod_price * o.prod_qty) as subtotal
          FROM rpos_mesas m
          JOIN rpos_ordenes_mesas om ON m.mesa_id = om.mesa_id
          JOIN rpos_orders o ON om.order_id = o.order_id
          WHERE m.estado = 'Lista para facturar' AND om.estado = 'Activa'
          GROUP BY m.mesa_id
          ORDER BY m.numero_mesa";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$mesas = $stmt->get_result();

while ($mesa = $mesas->fetch_object()):
    $subtotal = $mesa->subtotal;
    $itebis = $subtotal * ($config->itebis_porcentaje / 100);
    $servicio = $subtotal * ($config->servicio_porcentaje / 100);
    $total = $subtotal + $itebis + $servicio;
?>
    <div class="col-xl-3 col-md-4 col-sm-6 mb-4">
        <div class="card card-lift--hover shadow border-0 mesa-card">
            <div class="card-header bg-gradient-primary">
                <h4 class="text-white text-center mb-0">Mesa #<?php echo $mesa->numero_mesa; ?></h4>
            </div>
            <div class="card-body py-3">
                <div class="text-center">
                    <span class="badge badge-info mb-2"><?php echo $mesa->ubicacion; ?></span>
                    <div class="d-flex justify-content-around mb-3">
                        <small class="text-muted">👥 <?php echo $mesa->num_personas; ?> personas</small>
                        <small class="text-muted">📦 <?php echo $mesa->num_ordenes; ?> órdenes</small>
                    </div>
                    <h3 class="text-success mb-3">RD$ <?php echo number_format($total, 2); ?></h3>

                    <div class="btn-group w-100" role="group">
                        <a class="btn btn-sm btn-info vista-previa-btn"
                            href="factura/generar_prefactura.php?mesa=<?php echo $mesa->mesa_id; ?>"
                            target="_blank"
                            title="Ver prefactura">
                            <i class="fas fa-eye"></i> Ver prefactura
                        </a>

                        <button class="btn btn-sm btn-success facturar-btn"
                            data-mesa-id="<?php echo $mesa->mesa_id; ?>"
                            data-mesa-numero="<?php echo $mesa->numero_mesa; ?>"
                            data-mesa-total="<?php echo $total; ?>"
                            title="Facturar mesa"
                            <?php echo !$caja_abierta ? 'disabled' : ''; ?>>
                            <i class="fas fa-cash-register"></i> Facturar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endwhile; ?>
<?php $stmt->close(); ?>