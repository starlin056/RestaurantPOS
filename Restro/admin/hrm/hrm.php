<?php ob_start(); ?>
<?php
session_start();
include('../config/config.php');
include('../config/checklogin.php');
check_login();

// Eliminar empleado
if (isset($_GET['delete'])) {
  $id = intval($_GET['delete']);

  // Verificar si el empleado tiene órdenes asignadas
  $checkOrders = "SELECT * FROM rpos_delivery_orders WHERE repartidor_id = ?";
  $stmtCheck = $mysqli->prepare($checkOrders);
  $stmtCheck->bind_param('i', $id);
  $stmtCheck->execute();
  $stmtCheck->store_result();

  if ($stmtCheck->num_rows > 0) {
    $err = "No se puede eliminar el empleado porque tiene entregas asignadas";
  } else {
    $adn = "DELETE FROM rpos_staff WHERE staff_id = ?";
    $stmt = $mysqli->prepare($adn);
    $stmt->bind_param('i', $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
      $success = "Empleado eliminado correctamente";
      header("refresh:1; url=hrm.php");
    } else {
      $err = "Error al eliminar el empleado";
    }
    $stmt->close();
  }
  $stmtCheck->close();
}

require_once('../partials/_head.php');
?>

<body>
  <?php require_once('../partials/_sidebar.php'); ?>

  <div class="main-content">
    <?php require_once('../partials/_topnav.php'); ?>

    <!-- Header moderno -->
    <div class="header bg-gradient-primary pb-8 pt-5 pt-md-8">
      <div class="container-fluid">
        <div class="header-body">
          <div class="row align-items-center py-4">
            <div class="col-lg-6 col-7">
              <h1 class="display-3 text-white">👨‍💼 Gestión de Empleados</h1>
              <p class="text-white mb-0">Administra el personal de tu restaurante</p>
            </div>
            <div class="col-lg-6 col-5 text-right">
              <a href="add_staff.php" class="btn btn-neutral">
                <i class="fas fa-user-plus mr-2"></i> Nuevo Empleado
              </a>
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

      <!-- Tarjetas de resumen -->
      <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6">
          <div class="card card-stats bg-gradient-info">
            <div class="card-body">
              <div class="row">
                <div class="col">
                  <h5 class="card-title text-uppercase text-white mb-0">Total</h5>
                  <span class="h2 font-weight-bold text-white mb-0">
                    <?php
                    $query = "SELECT COUNT(*) as total FROM rpos_staff";
                    $stmt = $mysqli->prepare($query);
                    $stmt->execute();
                    echo $stmt->get_result()->fetch_object()->total;
                    $stmt->close();
                    ?>
                  </span>
                </div>
                <div class="col-auto">
                  <div class="icon icon-shape bg-white text-info rounded-circle shadow">
                    <i class="fas fa-users"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
          <div class="card card-stats bg-gradient-success">
            <div class="card-body">
              <div class="row">
                <div class="col">
                  <h5 class="card-title text-uppercase text-white mb-0">Meseros</h5>
                  <span class="h2 font-weight-bold text-white mb-0">
                    <?php
                    $query = "SELECT COUNT(*) as total FROM rpos_staff WHERE rol = 'Mesero'";
                    $stmt = $mysqli->prepare($query);
                    $stmt->execute();
                    echo $stmt->get_result()->fetch_object()->total;
                    $stmt->close();
                    ?>
                  </span>
                </div>
                <div class="col-auto">
                  <div class="icon icon-shape bg-white text-success rounded-circle shadow">
                    <i class="fas fa-concierge-bell"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
          <div class="card card-stats bg-gradient-warning">
            <div class="card-body">
              <div class="row">
                <div class="col">
                  <h5 class="card-title text-uppercase text-white mb-0">Cocineros</h5>
                  <span class="h2 font-weight-bold text-white mb-0">
                    <?php
                    $query = "SELECT COUNT(*) as total FROM rpos_staff WHERE rol = 'Cocinero'";
                    $stmt = $mysqli->prepare($query);
                    $stmt->execute();
                    echo $stmt->get_result()->fetch_object()->total;
                    $stmt->close();
                    ?>
                  </span>
                </div>
                <div class="col-auto">
                  <div class="icon icon-shape bg-white text-warning rounded-circle shadow">
                    <i class="fas fa-utensils"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
          <div class="card card-stats bg-gradient-primary">
            <div class="card-body">
              <div class="row">
                <div class="col">
                  <h5 class="card-title text-uppercase text-white mb-0">Delivery</h5>
                  <span class="h2 font-weight-bold text-white mb-0">
                    <?php
                    $query = "SELECT COUNT(*) as total FROM rpos_staff WHERE rol = 'Delivery'";
                    $stmt = $mysqli->prepare($query);
                    $stmt->execute();
                    echo $stmt->get_result()->fetch_object()->total;
                    $stmt->close();
                    ?>
                  </span>
                </div>
                <div class="col-auto">
                  <div class="icon icon-shape bg-white text-primary rounded-circle shadow">
                    <i class="fas fa-motorcycle"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
          <div class="card card-stats bg-gradient-danger">
            <div class="card-body">
              <div class="row">
                <div class="col">
                  <h5 class="card-title text-uppercase text-white mb-0">Bartenders</h5>
                  <span class="h2 font-weight-bold text-white mb-0">
                    <?php
                    $query = "SELECT COUNT(*) as total FROM rpos_staff WHERE rol = 'Bartender'";
                    $stmt = $mysqli->prepare($query);
                    $stmt->execute();
                    echo $stmt->get_result()->fetch_object()->total;
                    $stmt->close();
                    ?>
                  </span>
                </div>
                <div class="col-auto">
                  <div class="icon icon-shape bg-white text-danger rounded-circle shadow">
                    <i class="fas fa-cocktail"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
          <div class="card card-stats bg-gradient-default">
            <div class="card-body">
              <div class="row">
                <div class="col">
                  <h5 class="card-title text-uppercase text-white mb-0">Admins</h5>
                  <span class="h2 font-weight-bold text-white mb-0">
                    <?php
                    $query = "SELECT COUNT(*) as total FROM rpos_staff WHERE rol = 'Administrador'";
                    $stmt = $mysqli->prepare($query);
                    $stmt->execute();
                    echo $stmt->get_result()->fetch_object()->total;
                    $stmt->close();
                    ?>
                  </span>
                </div>
                <div class="col-auto">
                  <div class="icon icon-shape bg-white text-default rounded-circle shadow">
                    <i class="fas fa-user-cog"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabla de empleados -->
      <div class="row">
        <div class="col-12">
          <div class="card bg-secondary shadow">
            <div class="card-header bg-white border-0">
              <div class="row align-items-center">
                <div class="col-8">
                  <h3 class="mb-0">📋 Lista de Empleados</h3>
                </div>
                <div class="col-4 text-right">
                  <div class="btn-group" role="group">
                    <button class="btn btn-sm btn-outline-primary active" data-filter="all">Todos</button>
                    <button class="btn btn-sm btn-outline-primary" data-filter="Mesero">Meseros</button>
                    <button class="btn btn-sm btn-outline-primary" data-filter="Delivery">Delivery</button>
                  </div>
                </div>
              </div>
            </div>

            <div class="card-body">
              <div class="table-responsive">
                <table class="table align-items-center table-flush" id="empleadosTable">
                  <thead class="thead-light">
                    <tr>
                      <th>Empleado</th>
                      <th>Contacto</th>
                      <th>Rol</th>
                      <th>Estado</th>
                      <th>Registro</th>
                      <th>Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $ret = "SELECT * FROM rpos_staff ORDER BY created_at DESC";
                    $stmt = $mysqli->prepare($ret);
                    $stmt->execute();
                    $res = $stmt->get_result();

                    if ($res->num_rows > 0) {
                      while ($staff = $res->fetch_object()) {
                        $rol_class = [
                          'Cajero' => 'info',
                          'Mesero' => 'success',
                          'Cocinero' => 'warning',
                          'Bartender' => 'danger',
                          'Delivery' => 'primary',
                          'Administrador' => 'default'
                        ][$staff->rol] ?? 'secondary';

                        $estado_class = $staff->estado == 'Activo' ? 'success' : 'secondary';
                        $estado_icon = $staff->estado == 'Activo' ? 'fa-check-circle' : 'fa-times-circle';
                    ?>
                        <tr data-rol="<?php echo $staff->rol; ?>">
                          <td>
                            <div class="d-flex align-items-center">
                              <div class="avatar bg-gradient-<?php echo $rol_class; ?> rounded-circle mr-3">
                                <i class="fas 
                                <?php
                                switch ($staff->rol) {
                                  case 'Cajero':
                                    echo 'fa-cash-register';
                                    break;
                                  case 'Mesero':
                                    echo 'fa-concierge-bell';
                                    break;
                                  case 'Cocinero':
                                    echo 'fa-utensils';
                                    break;
                                  case 'Bartender':
                                    echo 'fa-cocktail';
                                    break;
                                  case 'Delivery':
                                    echo 'fa-motorcycle';
                                    break;
                                  case 'Administrador':
                                    echo 'fa-user-cog';
                                    break;
                                  default:
                                    echo 'fa-user';
                                }
                                ?> 
                                text-white">
                                </i>
                              </div>
                              <div>
                                <span class="font-weight-bold"><?php echo htmlspecialchars($staff->staff_name); ?></span>
                                <br>
                                <small class="text-muted"><?php echo htmlspecialchars($staff->staff_number); ?></small>
                              </div>
                            </div>
                          </td>
                          <td>
                            <?php if ($staff->staff_email): ?>
                              <i class="fas fa-envelope text-info mr-2"></i> <?php echo htmlspecialchars($staff->staff_email); ?>
                              <br>
                            <?php endif; ?>
                            <?php if ($staff->telefono): ?>
                              <i class="fas fa-phone text-primary mr-2"></i> <?php echo htmlspecialchars($staff->telefono); ?>
                            <?php endif; ?>
                          </td>
                          <td>
                            <span class="badge badge-<?php echo $rol_class; ?>">
                              <?php echo $staff->rol; ?>
                            </span>
                          </td>
                          <td>
                            <span class="badge badge-<?php echo $estado_class; ?>">
                              <i class="fas <?php echo $estado_icon; ?> mr-1"></i>
                              <?php echo $staff->estado; ?>
                            </span>
                          </td>
                          <td>
                            <?php echo date('d/m/Y', strtotime($staff->created_at)); ?>
                            <br>
                            <small class="text-muted"><?php echo date('H:i', strtotime($staff->created_at)); ?></small>
                          </td>
                          <td>
                            <div class="btn-group" role="group">
                              <a href="update_staff.php?update=<?php echo $staff->staff_id; ?>" class="btn btn-sm btn-info" title="Editar">
                                <i class="fas fa-edit"></i>
                              </a>
                              <a href="hrm.php?delete=<?php echo $staff->staff_id; ?>" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Está seguro que desea eliminar este empleado?');">
                                <i class="fas fa-trash"></i>
                              </a>
                            </div>
                          </td>
                        </tr>
                    <?php
                      }
                    } else {
                      echo '<tr><td colspan="6" class="text-center py-4">No hay empleados registrados</td></tr>';
                    }
                    $stmt->close();
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <footer class="py-5">
    <div class="container">
      <div class="row align-items-center justify-content-xl-between">
        <div class="col-xl-6">
          <div class="copyright text-center text-xl-left text-muted">
          </div>
        </div>
        <div class="col-xl-6">
          <ul class="nav nav-footer justify-content-center justify-content-xl-end">
            <li class="nav-item">
              <a href="" class="nav-link" target="_blank"> &copy; 2025 - <?php echo date('Y'); ?> ®️ PEDRO UREÑA TODOS LOS DERECHOS RESERVADOS</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </footer>

  <!-- DataTables -->
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap4.min.css">
  <script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap4.min.js"></script>

  <style>
    .card-stats .icon-shape {
      width: 3rem;
      height: 3rem;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .avatar {
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .bg-gradient-info {
      background: linear-gradient(87deg, #11cdef 0, #1171ef 100%) !important;
    }

    .bg-gradient-success {
      background: linear-gradient(87deg, #2dce89 0, #2dcecc 100%) !important;
    }

    .bg-gradient-warning {
      background: linear-gradient(87deg, #fb6340 0, #fbb140 100%) !important;
    }

    .bg-gradient-primary {
      background: linear-gradient(87deg, #5e72e4 0, #825ee4 100%) !important;
    }

    .bg-gradient-danger {
      background: linear-gradient(87deg, #f5365c 0, #f56036 100%) !important;
    }

    .bg-gradient-default {
      background: linear-gradient(87deg, #172b4d 0, #1a174d 100%) !important;
    }
  </style>

  <script>
    $(document).ready(function() {
      // DataTables
      $('#empleadosTable').DataTable({
        "language": {
          "url": "//cdn.datatables.net/plug-ins/1.11.3/i18n/es_es.json"
        },
        "order": [
          [4, "desc"]
        ],
        "responsive": true,
        "autoWidth": false
      });

      // Filtrar por rol - CORREGIDO
      $('[data-filter]').click(function() {
        var filtro = $(this).data('filter');
        $('[data-filter]').removeClass('active');
        $(this).addClass('active');

        if (filtro === 'all') {
          $('#empleadosTable tbody tr').show();
        } else {
          $('#empleadosTable tbody tr').hide();
          $('#empleadosTable tbody tr[data-rol="' + filtro + '"]').show();
        }

        // Actualizar DataTables después de filtrar
        $('#empleadosTable').DataTable().draw();
      });
    });
  </script>
</body>

</html>