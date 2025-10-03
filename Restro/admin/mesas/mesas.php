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

// =====================
// Detectar rol del usuario
// =====================
$usuario_es_admin = false;
$usuario_es_supervisor = false;
$usuario_es_cajero = false;
$usuario_es_mesero = false;

// Si el login es de la tabla admin → es administrador global
if (isset($_SESSION['admin_id'])) {
    $usuario_es_admin = true;
} elseif (isset($_SESSION['staff_id'])) {
    $staff_id = $_SESSION['staff_id'];
    $stmt = $mysqli->prepare("SELECT rol FROM rpos_staff WHERE staff_id = ?");
    $stmt->bind_param('s', $staff_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        switch ($row['rol']) {
            case 'Administrador':
                $usuario_es_admin = true;
                break;
            case 'Supervisor':
                $usuario_es_supervisor = true;
                break;
            case 'Cajero':
                $usuario_es_cajero = true;
                break;
            case 'Mesero':
                $usuario_es_mesero = true;
                break;
        }
    }
    $stmt->close();
}

// =====================
// Eliminar mesa solo si no es Mesero
// =====================
if (isset($_GET['delete']) && !empty($_GET['delete']) && !$usuario_es_mesero) {
    $id = $_GET['delete'];

    try {
        // Eliminar órdenes relacionadas
        $query1 = "DELETE FROM rpos_ordenes_mesas WHERE mesa_id = ?";
        $stmt1 = $mysqli->prepare($query1);
        $stmt1->bind_param('s', $id);
        $stmt1->execute();
        $stmt1->close();

        // Eliminar la mesa
        $query2 = "DELETE FROM rpos_mesas WHERE mesa_id = ?";
        $stmt2 = $mysqli->prepare($query2);
        $stmt2->bind_param('s', $id);
        $stmt2->execute();
        $stmt2->close();

        $success = "Mesa eliminada correctamente";
        header("Location: ../mesas/mesas.php?msg=deleted");
        exit();
    } catch (Exception $e) {
        $err = "Error al eliminar mesa.";
    }
}

require_once('../partials/_head.php');
?>

<body>
  <?php require_once('../partials/_sidebar.php'); ?>
  <div class="main-content">
    <?php require_once('../partials/_topnav.php'); ?>

    <!-- Header moderno con imagen de fondo -->
    <div class="header bg-gradient-primary pb-8 pt-5 pt-md-8">
      <div class="container-fluid">
        <div class="header-body">
          <div class="row align-items-center py-4">
            <div class="col-lg-6 col-7">
              <h1 class="display-2 text-white">🍽️ Gestión de Mesas</h1>
              <p class="text-white mb-0">Sistema de administración de mesas del restaurante</p>
            </div>
            <div class="col-lg-6 col-5 text-right">
              <?php if ($usuario_es_admin || $usuario_es_cajero || $usuario_es_supervisor): ?>
              <a href="add_mesa.php" class="btn btn-neutral">
                <i class="fas fa-plus-circle mr-2"></i> Nueva Mesa
              </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="container-fluid mt--7">
      <!-- Alertas -->
      <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <span class="alert-icon"><i class="ni ni-like-2"></i></span>
          <span class="alert-text"><?php echo $success; ?></span>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      <?php endif; ?>
      
      <?php if (!empty($err)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <span class="alert-icon"><i class="ni ni-support-16"></i></span>
          <span class="alert-text"><?php echo $err; ?></span>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      <?php endif; ?>

      <!-- Filtros de mesas -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card shadow">
            <div class="card-body">
              <div class="row align-items-center">
                <div class="col-md-8">
                  <h4 class="mb-0">Filtrar mesas</h4>
                </div>
                <div class="col-md-4 text-right">
                  <div class="btn-group btn-group-toggle" data-toggle="buttons">
                    <label class="btn btn-sm btn-outline-primary active">
                      <input type="radio" name="filter" id="filter-all" autocomplete="off" checked> Todas
                    </label>
                    <label class="btn btn-sm btn-outline-primary">
                      <input type="radio" name="filter" id="filter-available" autocomplete="off"> Disponibles
                    </label>
                    <label class="btn btn-sm btn-outline-primary">
                      <input type="radio" name="filter" id="filter-occupied" autocomplete="off"> Ocupadas
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Vista de cuadrícula de mesas -->
      <div class="row" id="mesas-grid">
        <?php
        // Excluir mesa 0 desde SQL
        $query = "SELECT m.*, COUNT(om.order_id) as ordenes_activas 
                  FROM rpos_mesas m
                  LEFT JOIN rpos_ordenes_mesas om ON m.mesa_id = om.mesa_id AND om.estado = 'Activa'
                  WHERE m.numero_mesa <> 0
                  GROUP BY m.mesa_id
                  ORDER BY m.numero_mesa ASC";
        $stmt = $mysqli->prepare($query);
        $stmt->execute();
        $res = $stmt->get_result();

        while ($mesa = $res->fetch_object()) {
          // Determinar clase CSS según estado
          $status_class = '';
          $status_icon = '';
          
          switch($mesa->estado) {
            case 'Disponible':
              $status_class = 'success';
              $status_icon = 'fa-check-circle';
              break;
            case 'Ocupada':
              $status_class = 'danger';
              $status_icon = 'fa-utensils';
              break;
            case 'Reservada':
              $status_class = 'warning';
              $status_icon = 'fa-calendar-alt';
              break;
            case 'Lista para facturar':
              $status_class = 'info';
              $status_icon = 'fa-cash-register';
              break;
            case 'En preparación':
              $status_class = 'primary';
              $status_icon = 'fa-clock';
              break;
            default:
              $status_class = 'secondary';
              $status_icon = 'fa-question-circle';
          }
        ?>
        <div class="col-xl-3 col-md-4 col-sm-6 mb-4 mesa-card" data-status="<?php echo strtolower($mesa->estado); ?>">
          <div class="card card-lift--hover shadow border-0">
            <div class="card-header bg-gradient-<?php echo $status_class; ?>">
              <div class="row align-items-center">
                <div class="col">
                  <h4 class="text-white mb-0">Mesa #<?php echo htmlspecialchars($mesa->numero_mesa); ?></h4>
                </div>
                <div class="col-auto">
                  <span class="badge badge-light"><?php echo htmlspecialchars($mesa->capacidad); ?> <i class="fas fa-user"></i></span>
                </div>
              </div>
            </div>
            <div class="card-body py-3">
              <div class="text-center">
                <span class="badge badge-<?php echo $status_class; ?> mb-2">
                  <i class="fas <?php echo $status_icon; ?> mr-1"></i> <?php echo htmlspecialchars($mesa->estado); ?>
                </span>
                <p class="text-sm text-muted mb-2">
                  <i class="fas fa-map-marker-alt mr-1"></i> <?php echo htmlspecialchars($mesa->ubicacion); ?>
                </p>
                
                <?php if ($mesa->ordenes_activas > 0): ?>
                <div class="d-flex justify-content-center mb-3">
                  <span class="badge badge-pill badge-info">
                    <i class="fas fa-receipt mr-1"></i> <?php echo $mesa->ordenes_activas; ?> orden(es) activa(s)
                  </span>
                </div>
                <?php endif; ?>
                
                <div class="btn-group btn-group-sm" role="group">
                  <a href="mesa_detalle.php?mesa=<?php echo urlencode($mesa->mesa_id); ?>" class="btn btn-info">
                    <i class="fas fa-eye"></i>
                  </a>
                  <?php if ($usuario_es_admin || $usuario_es_cajero || $usuario_es_supervisor): ?>
                  <a href="update_mesa.php?update=<?php echo urlencode($mesa->mesa_id); ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i>
                  </a>
                  <a href="mesas.php?delete=<?php echo urlencode($mesa->mesa_id); ?>" 
                     onclick="return confirm('¿Está seguro que desea eliminar esta mesa?');" 
                     class="btn btn-danger">
                    <i class="fas fa-trash"></i>
                  </a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
        <?php } ?>
      </div>

      <?php require_once('../partials/_footer.php'); ?>
    </div>
  </div>
  <?php require_once('../partials/_scripts.php'); ?>

  <style>
    .mesa-card {
      transition: all 0.3s ease;
    }
    .mesa-card:hover {
      transform: translateY(-5px);
    }
    .card-lift--hover:hover {
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    .bg-gradient-success {
      background: linear-gradient(87deg, #2dce89 0, #2dcecc 100%) !important;
    }
    .bg-gradient-danger {
      background: linear-gradient(87deg, #f5365c 0, #f56036 100%) !important;
    }
    .bg-gradient-warning {
      background: linear-gradient(87deg, #fb6340 0, #fbb140 100%) !important;
    }
    .bg-gradient-info {
      background: linear-gradient(87deg, #11cdef 0, #1171ef 100%) !important;
    }
    .bg-gradient-primary {
      background: linear-gradient(87deg, #5e72e4 0, #825ee4 100%) !important;
    }
    .bg-gradient-secondary {
      background: linear-gradient(87deg, #8898aa 0, #525f7f 100%) !important;
    }
  </style>

  <script>
    $(document).ready(function() {
      // Filtrado de mesas
      $('input[name="filter"]').change(function() {
        var filter = $(this).attr('id');
        
        $('.mesa-card').show();
        
        if (filter === 'filter-available') {
          $('.mesa-card:not([data-status="disponible"])').hide();
        } else if (filter === 'filter-occupied') {
          $('.mesa-card:not([data-status="ocupada"])').hide();
        }
      });

      // Animación de carga de tarjetas
      $('.mesa-card').each(function(i) {
        $(this).delay(i * 100).fadeTo(500, 1);
      });
    });
  </script>
</body>
</html>

<?php ob_end_flush(); ?>
