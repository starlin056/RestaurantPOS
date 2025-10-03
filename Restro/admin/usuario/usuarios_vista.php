<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ob_start();
include('../config/config.php');
include('../config/checklogin.php');
check_login();

$err = null;
$success = null;

require_once('../partials/_head.php');
?>


<body>
  <?php require_once('../partials/_sidebar.php'); ?>
  <div class="main-content">
    <?php require_once('../partials/_topnav.php'); ?>

    <div class="header bg-gradient-primary pb-8 pt-5 pt-md-8">
      <div class="container-fluid">
        <div class="header-body">
          <div class="row align-items-center py-4">
            <div class="col-lg-6 col-7">
              <h1 class="display-3 text-white">👥 Usuarios del Sistema</h1>
              <p class="text-white mb-0">Visualizador tipo de Usuarios registrado</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="container-fluid mt--7">
      <div class="row">
        <div class="col-12">
          <div class="card bg-secondary shadow">
            <div class="card-header bg-white border-0">
              <h3 class="mb-0">📋 Lista de Usuarios</h3>
            </div>

            <div class="card-body">
              <div class="table-responsive">
                <table class="table align-items-center table-flush" id="usuariosTable">
                  <thead class="thead-light">
                    <tr>
                      <th>Usuario</th>
                      <th>Email</th>
                      <th>Rol</th>
                      <th>Estado</th>
                      <th>Registro</th>
                      <th>Fecha Vencimiento</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    // Consulta unificada
                    $query = "
                      SELECT 
                        admin_id AS id,
                        admin_name AS nombre,
                        admin_email AS email,
                        'Administrador' AS rol,
                        estado,
                        NULL AS created_at,
                        activation_expiry AS vencimiento
                      FROM rpos_admin
                      UNION ALL
                      SELECT 
                        staff_id AS id,
                        staff_name AS nombre,
                        staff_email AS email,
                        rol,
                        estado,
                        created_at,
                        NULL AS vencimiento
                      FROM rpos_staff
                      ORDER BY created_at DESC
                    ";

                    $stmt = $mysqli->prepare($query);
                    $stmt->execute();
                    $res = $stmt->get_result();

                    if ($res->num_rows > 0) {
                      while ($u = $res->fetch_object()) {
                        $rol_class = [
                          'Administrador' => 'default',
                          'Cajero' => 'info',
                          'Mesero' => 'success',
                          'Cocinero' => 'warning',
                          'Bartender' => 'danger',
                          'Delivery' => 'primary'
                        ][$u->rol] ?? 'secondary';

                        $estado_class = $u->estado == 'Activo' ? 'success' : 'secondary';
                        $estado_icon = $u->estado == 'Activo' ? 'fa-check-circle' : 'fa-times-circle';

                        switch ($u->rol) {
                          case 'Administrador': $icon = 'fa-user-cog'; break;
                          case 'Cajero': $icon = 'fa-cash-register'; break;
                          case 'Mesero': $icon = 'fa-concierge-bell'; break;
                          case 'Cocinero': $icon = 'fa-utensils'; break;
                          case 'Bartender': $icon = 'fa-cocktail'; break;
                          case 'Delivery': $icon = 'fa-motorcycle'; break;
                          default: $icon = 'fa-user';
                        }
                    ?>
                        <tr>
                          <td>
                            <div class="d-flex align-items-center">
                              <div class="avatar bg-gradient-<?php echo $rol_class; ?> rounded-circle mr-3">
                                <i class="fas <?php echo $icon; ?> text-white"></i>
                              </div>
                              <div>
                                <span class="font-weight-bold"><?php echo htmlspecialchars($u->nombre); ?></span>
                                <br>
                                <small class="text-muted">ID: <?php echo $u->id; ?></small>
                              </div>
                            </div>
                          </td>
                          <td><?php echo htmlspecialchars($u->email); ?></td>
                          <td><span class="badge badge-<?php echo $rol_class; ?>"><?php echo $u->rol; ?></span></td>
                          <td>
                            <span class="badge badge-<?php echo $estado_class; ?>">
                              <i class="fas <?php echo $estado_icon; ?> mr-1"></i>
                              <?php echo $u->estado; ?>
                            </span>
                          </td>
                          <td>
                            <?php echo $u->created_at ? date('d/m/Y H:i', strtotime($u->created_at)) : '<span class="text-muted">-</span>'; ?>
                          </td>
                          <td>
                            <?php echo $u->vencimiento ? date('d/m/Y H:i', strtotime($u->vencimiento)) : '<span class="text-muted">Sin vencimiento</span>'; ?>
                          </td>
                        </tr>
                    <?php
                      }
                    } else {
                      echo '<tr><td colspan="6" class="text-center py-4">No hay usuarios registrados</td></tr>';
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
        <?php require_once('../partials/_footer.php'); ?>
    </div>
  </div>

  <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap4.min.css">
  <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap4.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#usuariosTable').DataTable({
        "language": { "url": "//cdn.datatables.net/plug-ins/1.11.3/i18n/es_es.json" },
        "order": [[4, "desc"]],
        "responsive": true
      });
    });
  </script>
</body>
</html>
