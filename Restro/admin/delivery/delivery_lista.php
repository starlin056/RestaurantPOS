<?php
session_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/error.log');
include('../config/config.php');
include('../config/checklogin.php');
check_login();

// Obtener pedidos de delivery
$query = "SELECT d.*, s.staff_name as repartidor_nombre 
          FROM rpos_delivery_orders d 
          LEFT JOIN rpos_staff s ON d.repartidor_id = s.staff_id 
          ORDER BY d.created_at DESC";
$pedidos = $mysqli->query($query);

// Helper para color de badges (Bootstrap/AdminLTE)
function badgeClass($estado) {
    switch (trim($estado)) {
        case 'Recibido':         return 'warning';
        case 'En preparación':   return 'info';
        case 'En camino':        return 'primary';
        case 'Entregado':        return 'success';
        case 'Cancelado':        return 'danger';
        default:                 return 'secondary';
    }
}
?>

<?php require_once('../partials/_head.php'); ?>

<style>
  /* === FIX LAYOUT: Sidebar + Main Content === */
  :root{
    --sidebar-width: 260px;      /* ajusta si tu sidebar mide diferente */
    --topnav-height: 0px;        /* pon 56px si tu topnav es fixed */
  }

  /* Contenedor fijo del sidebar (envolviendo el partial) */
  #app-sidebar{
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    width: var(--sidebar-width);
    z-index: 1030;               /* por debajo del topnav si es fixed */
    overflow-y: auto;
    background: inherit;         /* deja el estilo que tenga tu partial */
  }

  /* Columna principal empujada a la derecha del sidebar */
  .main-content{
    margin-left: var(--sidebar-width);
    padding-top: var(--topnav-height);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    background-color: #f8f9fe;   /* opcional, luce como Argon */
  }

  /* Responsive: en móviles, sidebar oculto y contenido a ancho completo */
  @media (max-width: 991.98px){
    #app-sidebar{
      transform: translateX(-100%);
      transition: transform .3s ease;
    }
    body.sidebar-open #app-sidebar{
      transform: translateX(0);
    }
    .main-content{
      margin-left: 0;
    }
  }

  /* Tabla: garantizar que no rompa el layout */
  .table-responsive{
    overflow-x: auto;
  }

  /* Badges personalizados si no usas Bootstrap badges */
  .estado-badge {
      font-size: 0.9em;
      padding: 5px 10px;
      border-radius: 15px;
      display: inline-block;
  }
</style>

<body>
  <div class="wrapper">
    <!-- Sidebar (envuelto para poder fijarlo sin tocar tu partial) -->
    <aside id="app-sidebar">
      <?php require_once('../partials/_sidebar.php'); ?>
    </aside>

    <!-- Contenido principal -->
    <div class="main-content">
      <!-- Navbar -->
      <?php require_once('../partials/_topnav.php'); ?>

      <!-- Header -->
      <div class="header bg-gradient-primary pb-6 pt-5 pt-md-6">
        <div class="container-fluid">
          <div class="header-body">
            <div class="row align-items-center py-4">
              <div class="col-lg-6 col-7">
                <h1 class="text-white display-4">🛵 Resumenes de órdenes delivery</h1>
                <p class="text-white mb-0">Gestión de los pedidos para llevar</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main content -->
      <section class="content">
        <div class="container-fluid py-4">
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                  <h3 class="card-title mb-0">Lista de Pedidos de Delivery</h3>
                  <div class="card-tools">
                    <a href="index.php" class="btn btn-primary btn-sm">
                      <i class="fas fa-plus-circle mr-1"></i> Nuevo Pedido
                    </a>
                  </div>
                </div>

                <div class="card-body">
                  <div class="table-responsive">
                    <table id="tablaPedidos" class="table table-bordered table-striped table-hover w-100">
                      <thead>
                        <tr>
                          <th># Orden</th>
                          <th>Cliente</th>
                          <th>Teléfono</th>
                          <th>Dirección</th>
                          <th>Repartidor</th>
                          <th>Total</th>
                          <th>Estado</th>
                          <th>Fecha</th>
                          <th>Acciones</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php while ($pedido = $pedidos->fetch_assoc()): ?>
                          <tr>
                            <td><?= $pedido['order_code'] ?></td>
                            <td><?= htmlspecialchars($pedido['customer_name']) ?></td>
                            <td><?= htmlspecialchars($pedido['customer_phone']) ?></td>
                            <td><?= htmlspecialchars(mb_strimwidth($pedido['customer_address'] ?? '', 0, 30, '...')) ?></td>
                            <td><?= htmlspecialchars($pedido['repartidor_nombre'] ?? 'Sin asignar') ?></td>
                            <td>$<?= number_format((float)$pedido['total'], 2) ?></td>
                            <td>
                              <?php $badge = badgeClass($pedido['estado']); ?>
                              <span class="badge badge-<?= $badge ?> estado-badge"><?= htmlspecialchars($pedido['estado']) ?></span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($pedido['created_at'])) ?></td>
                            <td>
                              <div class="btn-group">
                                <a href="delivery_detalle.php?delivery_id=<?= $pedido['delivery_id'] ?>" class="btn btn-info btn-sm" title="Ver detalle">
                                  <i class="fas fa-eye"></i>
                                </a>
                                <?php if ($pedido['estado'] == 'Recibido' || $pedido['estado'] == 'En preparación'): ?>
                                  <a href="delivery_editar.php?delivery_id=<?= $pedido['delivery_id'] ?>" class="btn btn-warning btn-sm" title="Editar">
                                    <i class="fas fa-edit"></i>
                                  </a>
                                <?php endif; ?>
                                <?php if ($pedido['estado'] == 'Recibido'): ?>
                                  <button onclick="cancelarPedido('<?= $pedido['delivery_id'] ?>')" class="btn btn-danger btn-sm" title="Cancelar">
                                    <i class="fas fa-times"></i>
                                  </button>
                                <?php endif; ?>
                                <?php if ($pedido['estado'] == 'Entregado' && (int)$pedido['facturado'] === 0): ?>
                                  <a href="delivery_factura.php?delivery_id=<?= $pedido['delivery_id'] ?>" class="btn btn-success btn-sm" title="Generar Factura">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                  </a>
                                <?php endif; ?>
                              </div>
                            </td>
                          </tr>
                        <?php endwhile; ?>
                      </tbody>
                    </table>
                  </div><!-- /.table-responsive -->
                </div><!-- /.card-body -->
              </div><!-- /.card -->
            </div><!-- /.col-12 -->
          </div><!-- /.row -->
        </div><!-- /.container-fluid -->
      </section>

      <?php require_once('../partials/_footer.php'); ?>
    </div><!-- /.main-content -->
  </div><!-- /.wrapper -->

  <?php require_once('../partials/_scripts.php'); ?>

  <script>
    // Si tu topnav es fixed (tiene clase .fixed-top), ajusta el padding-top dinámicamente
    (function(){
      var topnav = document.querySelector('.navbar.fixed-top, .main-header.navbar.navbar-expand.navbar-white.navbar-light.fixed-top');
      if(topnav){
        var h = topnav.offsetHeight || 56;
        document.querySelector('.main-content').style.paddingTop = h + 'px';
      }
    })();

    // DataTables
    $(document).ready(function() {
      $('#tablaPedidos').DataTable({
        language: { url: "//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json" },
        order: [[7, "desc"]],
        responsive: true,
        autoWidth: false
      });
    });

    // Cancelar pedido
    function cancelarPedido(delivery_id) {
      if (confirm('¿Está seguro de que desea cancelar este pedido?')) {
        fetch('delivery_acciones.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'accion=cancelar&delivery_id=' + encodeURIComponent(delivery_id)
        })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            alert('Pedido cancelado correctamente');
            location.reload();
          } else {
            alert('Error: ' + (data.message || 'No se pudo cancelar'));
          }
        })
        .catch(err => {
          console.error(err);
          alert('Error al cancelar el pedido');
        });
      }
    }
  </script>
</body>
</html>
