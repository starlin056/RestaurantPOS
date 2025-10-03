<?php
ob_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/error.log');
error_reporting(E_ALL);
session_start();
include('../config/config.php');
include('../config/checklogin.php');
check_login();

// Consulta para pedidos de comida
$query = "SELECT 
    COALESCE(CONCAT('DELIVERY_', d.delivery_id), m.mesa_id) as grupo_id,
    COALESCE(m.numero_mesa, 0) as numero_mesa,
    CASE 
        WHEN d.delivery_id IS NOT NULL THEN 'Delivery'
        ELSE COALESCE(m.estado, 'Pendiente') 
    END as estado_grupo,
    o.order_id,
    o.order_code,
    o.prod_id,
    o.prod_name,
    o.prod_price,
    o.prod_qty,
    o.order_status,
    o.created_at,
    o.notas,
    p.prod_img,
    p.prod_desc,
    p.tipo,
    d.delivery_id,
    d.estado as estado_delivery,
    d.customer_name as delivery_customer,
    d.order_code as delivery_order_code,
    COUNT(o.order_id) OVER (PARTITION BY COALESCE(CONCAT('DELIVERY_', d.delivery_id), m.mesa_id)) as total_pedidos_grupo
FROM rpos_orders o
JOIN rpos_products p ON o.prod_id = p.prod_id
LEFT JOIN rpos_ordenes_mesas om ON o.order_id = om.order_id
LEFT JOIN rpos_mesas m ON om.mesa_id = m.mesa_id
LEFT JOIN rpos_delivery_orders d ON o.order_code = d.order_code
WHERE o.order_status IN ('Pendiente','En preparación')
  AND p.tipo = 'Comida'
  AND (d.delivery_id IS NOT NULL OR m.mesa_id IS NOT NULL)

ORDER BY numero_mesa ASC, order_status DESC, created_at ASC";

$stmt = $mysqli->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

// Agrupar pedidos por grupo (mesa o delivery)
$grupos = [];
while ($pedido = $result->fetch_object()) {
  $grupo_key = $pedido->grupo_id;

  if (!isset($grupos[$grupo_key])) {
    $es_delivery = strpos($grupo_key, 'DELIVERY_') === 0;

    $grupos[$grupo_key] = [
      'numero_mesa' => $pedido->numero_mesa,
      'estado_grupo' => $pedido->estado_grupo,
      'total_pedidos' => $pedido->total_pedidos_grupo,
      'es_delivery' => $es_delivery,
      'delivery_id' => $pedido->delivery_id,
      'delivery_order_code' => $pedido->delivery_order_code,
      'estado_delivery' => $pedido->estado_delivery,
      'delivery_customer' => $pedido->delivery_customer,
      'pedidos' => []
    ];
  }
  $grupos[$grupo_key]['pedidos'][] = $pedido;
}

// Función para obtener la ruta correcta de la imagen
function obtenerRutaImagen($nombreImagen)
{
  if (empty($nombreImagen)) {
    return '../assets/img/theme/food-placeholder.jpg';
  }

  $rutaProductos = '../assets/img/products/' . $nombreImagen;

  if (file_exists($rutaProductos)) {
    return $rutaProductos;
  }

  return 'assets/img/theme/food-placeholder.jpg';
}

require_once('../partials/_head.php');
?>

<body>
  <?php require_once('../partials/_sidebar.php'); ?>
  <div class="main-content">
    <?php require_once('../partials/_topnav.php'); ?>

    <!-- Header -->
    <div style="background-image: url(../assets/img/theme/kitchen-bg.jpg); background-size: cover;" class="header pb-8 pt-5 pt-md-8">
      <span class="mask bg-gradient-dark opacity-8"></span>
      <div class="container-fluid">
        <div class="header-body">
          <div class="row align-items-center py-4">
            <div class="col-lg-6 col-7">
              <h1 class="display-2 text-white">Cocina</h1>
              <p class="text-white mb-0">Pedidos de comida</p>
            </div>
            <div class="col-lg-6 col-5 text-right">
              <span class="badge badge-danger">
                Pendientes: <?php echo count($grupos); ?> grupos
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Contenido -->
    <div class="container-fluid mt--7">
      <?php if (empty($grupos)): ?>
        <div class="alert alert-info">No hay pedidos pendientes en cocina.</div>
      <?php else: ?>
        <div class="row">
          <?php foreach ($grupos as $grupo_id => $grupo): ?>
            <div class="col-xl-6 mb-4">
              <div class="card shadow">
                
                <!-- Encabezado del grupo -->
                <div class="card-header bg-<?php echo $grupo['es_delivery'] ? 'info' : ($grupo['estado_grupo'] == 'Ocupada' ? 'danger' : 'warning'); ?>">
                  <div class="row align-items-center">
                    <div class="col">
                      <h3 class="mb-0 text-white">
                        <?php if ($grupo['es_delivery']): ?>
                          Delivery #<?php
                                    // Obtener los últimos 4 dígitos del order_code
                                    $order_code = $grupo['delivery_order_code'];
                                    $ultimos_4_digitos = '';

                                    // Buscar los últimos 4 dígitos numéricos en el order_code
                                    if (preg_match_all('/\d/', $order_code, $digitos)) {
                                      $todos_digitos = implode('', $digitos[0]);
                                      $ultimos_4_digitos = substr($todos_digitos, -4);
                                    }

                                    // Si no hay 4 dígitos, mostrar últimos 4 caracteres
                                    if (strlen($ultimos_4_digitos) < 4) {
                                      $ultimos_4_digitos = substr($order_code, -4);
                                    }

                                    echo $ultimos_4_digitos;
                                    ?>
                        <?php else: ?>
                          Mesa #<?php echo $grupo['numero_mesa']; ?>
                        <?php endif; ?>
                      </h3>
                      <small class="text-white">
                        <?php echo $grupo['total_pedidos']; ?> pedidos
                        <?php if ($grupo['es_delivery'] && !empty($grupo['delivery_customer'])): ?>
                          <br>Cliente: <?php echo $grupo['delivery_customer']; ?>
                        <?php endif; ?>
                      </small>
                    </div>
                    <div class="col-auto">
                      <span class="badge badge-light">
                        <?php echo $grupo['es_delivery'] ? $grupo['estado_delivery'] : $grupo['estado_grupo']; ?>
                      </span>
                    </div>
                  </div>
                </div>

                <!-- Lista de pedidos -->
                <div class="list-group list-group-flush">
                  <?php foreach ($grupo['pedidos'] as $pedido): ?>
                    <div class="list-group-item">
                      <div class="row align-items-center">
                        <!-- Imagen del producto -->
                        <div class="col-auto">
                          <img src="<?php echo obtenerRutaImagen($pedido->prod_img); ?>"
                            class="rounded" style="width: 64px; height: 64px; object-fit: cover;"
                            onerror="this.src='../assets/img/theme/food-placeholder.jpg'">
                        </div>

                        <!-- Detalles del pedido -->
                        <div class="col">
                          <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0 text-sm"><?php echo $pedido->prod_name; ?></h4>
                            <span class="badge badge-<?php echo $pedido->order_status == 'Pendiente' ? 'warning' : 'primary'; ?>">
                              <?php echo $pedido->order_status; ?>
                            </span>
                          </div>
                          <div class="d-flex justify-content-between">
                            <p class="text-sm text-muted mb-0">Cantidad: <?php echo $pedido->prod_qty; ?></p>
                            <p class="text-sm text-muted mb-0"><?php echo date('H:i', strtotime($pedido->created_at)); ?></p>
                          </div>

                          <!-- Notas -->
                          <?php if (!empty($pedido->notas)): ?>
                            <div class="mt-2">
                              <a class="text-sm text-primary" data-toggle="collapse" href="#notas<?php echo $pedido->order_id; ?>" role="button">
                                <i class="fas fa-sticky-note"></i> Notas
                              </a>
                              <div class="collapse mt-1" id="notas<?php echo $pedido->order_id; ?>">
                                <div class="card card-body p-2 bg-light">
                                  <?php echo nl2br(htmlspecialchars($pedido->notas)); ?>
                                </div>
                              </div>
                            </div>
                          <?php endif; ?>
                        </div>

                        <!-- Botones de acción -->
                        <div class="col-auto">
                          <form action="../cocina/actualizar_estado_cocina.php" method="post">
                            <input type="hidden" name="order_id" value="<?php echo $pedido->order_id; ?>">
                            <input type="hidden" name="delivery_id" value="<?php echo $pedido->delivery_id; ?>">
                            <input type="hidden" name="es_delivery" value="<?php echo $grupo['es_delivery'] ? '1' : '0'; ?>">

                            <?php if ($pedido->order_status == 'Pendiente'): ?>
                              <button type="submit" name="estado" value="En preparación" class="btn btn-sm btn-info">
                                <i class="fas fa-utensils"></i> Preparar
                              </button>
                            <?php else: ?>
                              <button type="submit" name="estado" value="Listo" class="btn btn-sm btn-success">
                                <i class="fas fa-check"></i> Listo
                              </button>
                            <?php endif; ?>
                          </form>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>

                <!-- Pie de tarjeta - Solo para mesas, no para delivery -->
                <?php if (!$grupo['es_delivery']): ?>
                  <div class="card-footer py-2">
                    <form action="../cocina/marcar_mesa_lista_cocina.php" method="post">
                      <input type="hidden" name="mesa_id" value="<?php echo $grupo_id; ?>">
                      <input type="hidden" name="es_delivery" value="0">
                      <button type="submit" class="btn btn-sm btn-block btn-success">
                        <i class="fas fa-check-double"></i> Marcar toda la mesa como lista
                      </button>
                    </form>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php require_once('../partials/_footer.php'); ?>
    </div>
  </div>

  <?php require_once('../partials/_scripts.php'); ?>
  <script>
    setTimeout(function() {
      window.location.reload();
    }, 60000);
  </script>
</body>

</html>