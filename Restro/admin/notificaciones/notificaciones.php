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

$err = null;
$success = null;

// Obtener notificaciones pendientes
$query = "SELECT n.*, m.numero_mesa 
          FROM rpos_notificaciones n 
          LEFT JOIN rpos_mesas m ON n.mesa_id = m.mesa_id 
          WHERE n.estado IN ('pendiente', 'vista')
          ORDER BY n.created_at DESC";
$notificaciones = $mysqli->query($query);

require_once('../partials/_head.php');
?>

<style>
.notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #f5365c;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
}
.notification-item {
    border-left: 4px solid #5e72e4;
    transition: all 0.3s ease;
}
.notification-item.vista {
    border-left-color: #8392ab;
    opacity: 0.8;
}
.notification-item.pendiente {
    background-color: #f7fafc;
}
.notification-time {
    font-size: 0.8rem;
    color: #6c757d;
}
</style>

<body>
  <?php require_once('../partials/_sidebar.php'); ?>
  <div class="main-content">
    <?php require_once('../partials/_topnav.php'); ?>

    <!-- Header -->
    <div class="header pb-8 pt-5 pt-md-8" style="background: linear-gradient(135deg, #5e72e4 0%, #825ee4 100%);">
      <div class="container-fluid">
        <div class="header-body">
          <div class="row align-items-center py-4">
            <div class="col-lg-6 col-7">
              <h1 class="display-2 text-white">🔔 Notificaciones</h1>
              <p class="text-white mb-0">Gestión de alertas del sistema</p>
            </div>
            <div class="col-lg-6 col-5 text-right">
              <span class="badge badge-warning" id="contadorNotif">
                <?php echo $notificaciones->num_rows; ?> notificaciones
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Contenido -->
    <div class="container-fluid mt--7">
      <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show shadow">
          <strong>✔</strong> <?php echo $success; ?>
          <button type="button" class="btn-close" data-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <?php if ($err): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow">
          <strong>✘</strong> <?php echo $err; ?>
          <button type="button" class="btn-close" data-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <div class="row">
        <div class="col">
          <div class="card shadow">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
              <h3 class="mb-0">Notificaciones del Sistema</h3>
              <div>
                <button class="btn btn-sm btn-outline-primary" id="marcarTodasLeidas">
                  <i class="fas fa-check-double"></i> Marcar todas como leídas
                </button>
                <button class="btn btn-sm btn-outline-danger" id="limpiarNotificaciones">
                  <i class="fas fa-trash"></i> Limpiar notificaciones
                </button>
              </div>
            </div>
            <div class="list-group list-group-flush" id="listaNotificaciones">
              <?php if ($notificaciones->num_rows > 0): ?>
                <?php while ($notif = $notificaciones->fetch_object()): ?>
                  <div class="list-group-item notification-item <?php echo $notif->estado; ?>">
                    <div class="row align-items-center">
                      <div class="col">
                        <div class="d-flex justify-content-between align-items-center">
                          <h4 class="mb-1 text-sm"><?php echo $notif->mensaje; ?></h4>
                          <span class="badge badge-<?php echo $notif->estado == 'pendiente' ? 'danger' : 'secondary'; ?>">
                            <?php echo ucfirst($notif->estado); ?>
                          </span>
                        </div>
                        <p class="text-sm text-muted mb-0 notification-time">
                          <i class="far fa-clock"></i> 
                          <?php echo date('d/m/Y H:i', strtotime($notif->created_at)); ?>
                          <?php if ($notif->tipo_pedido): ?>
                            • Tipo: <?php echo ucfirst($notif->tipo_pedido); ?>
                          <?php endif; ?>
                        </p>
                      </div>
                      <div class="col-auto">
                        <div class="btn-group">
                          <?php if ($notif->estado == 'pendiente'): ?>
                            <button class="btn btn-sm btn-success marcar-leido" data-id="<?php echo $notif->notificacion_id; ?>">
                              <i class="fas fa-check"></i>
                            </button>
                          <?php endif; ?>
                          <button class="btn btn-sm btn-info marcar-atendido" data-id="<?php echo $notif->notificacion_id; ?>">
                            <i class="fas fa-user-check"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php endwhile; ?>
              <?php else: ?>
                <div class="list-group-item text-center py-5">
                  <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                  <h4 class="text-muted">No hay notificaciones</h4>
                  <p class="text-muted">Todas las notificaciones están al día</p>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

     <!-- Pie de página -->
      <?php require_once('../partials/_footer.php'); ?>
    </div>
  </div>

  <!-- Sonido para notificaciones -->
  <audio id="notifSound" src="/RestaurantPOS/Restro/admin/assets/sounds/notification.mp3" preload="auto"></audio>

  <!-- Scripts -->
  <?php require_once('../partials/_scripts.php'); ?>
  <script>
  $(document).ready(function() {
    let prevCount = parseInt($('#contadorNotif').text()) || 0;
    const notifSound = document.getElementById('notifSound');

    // 🔓 Desbloquear sonido en el primer clic
    $(document).one('click', function() {
      notifSound.play().then(() => {
        notifSound.pause();
        notifSound.currentTime = 0;
      }).catch(err => console.log("Autoplay bloqueado hasta interacción:", err));
    });

    // Marcar como leído
    $('.marcar-leido').click(function() {
      var notificacion_id = $(this).data('id');
      var $item = $(this).closest('.notification-item');
      
      $.post('notificaciones_procesar.php', {marcar_leido: 1, notificacion_id: notificacion_id}, function(response) {
        if (response.success) {
          $item.removeClass('pendiente').addClass('vista');
          $item.find('.badge').removeClass('badge-danger').addClass('badge-secondary').text('vista');
          actualizarContador();
        }
      });
    });

    // Marcar como atendido
    $('.marcar-atendido').click(function() {
      var notificacion_id = $(this).data('id');
      var $item = $(this).closest('.notification-item');
      
      $.post('notificaciones_procesar.php', {marcar_atendido: 1, notificacion_id: notificacion_id}, function(response) {
        if (response.success) {
          $item.slideUp(300, function() {
            $(this).remove();
            if ($('#listaNotificaciones .notification-item').length === 0) {
              $('#listaNotificaciones').html(`
                <div class="list-group-item text-center py-5">
                  <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                  <h4 class="text-muted">No hay notificaciones</h4>
                  <p class="text-muted">Todas las notificaciones están al día</p>
                </div>
              `);
            }
            actualizarContador();
          });
        }
      });
    });

    // Marcar todas como leídas
    $('#marcarTodasLeidas').click(function() {
      $('#listaNotificaciones .marcar-leido').each(function() {
        $(this).click();
      });
    });

    // Limpiar todas las notificaciones
    $('#limpiarNotificaciones').click(function() {
      if (confirm('¿Estás seguro de que quieres limpiar todas las notificaciones?')) {
        $('#listaNotificaciones .marcar-atendido').each(function() {
          $(this).click();
        });
      }
    });

    function actualizarContador() {
      var count = $('#listaNotificaciones .notification-item.pendiente').length;
      $('#contadorNotif').text(count + ' notificaciones');

      // Si hay más notificaciones que antes -> sonar
      if (count > prevCount) {
        notifSound.play().catch(err => console.log("El navegador bloqueó el sonido:", err));
      }
      prevCount = count;
    }

    // Actualizar notificaciones automáticamente cada 30 segundos
    setInterval(function() {
      $.get('notificaciones_procesar.php?obtener_notificaciones=1', function(notificaciones) {
        // Aquí podrías reemplazar lista en tiempo real si quieres
        actualizarContador();
      });
    }, 30000);
  });
  </script>
</body>
</html>
