<?php
session_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/error.log');
include('../config/config.php');
include('../config/checklogin.php');
check_login();

// Verificar rol administrador
if ($_SESSION['rol'] !== 'admin') {
    die("Acceso denegado");
}

// Filtrado por usuario, acción y fechas
$usuario_filter = $_GET['usuario'] ?? '';
$accion_filter  = $_GET['accion'] ?? '';
$fecha_inicio   = $_GET['fecha_inicio'] ?? '';
$fecha_fin      = $_GET['fecha_fin'] ?? '';

$sql = "SELECT l.*, 
        COALESCE(a.admin_name, s.staff_name) AS usuario_nombre
        FROM rpos_movimientos_log l
        LEFT JOIN rpos_admin a ON l.usuario_id = a.admin_id
        LEFT JOIN rpos_staff s ON l.usuario_id = s.staff_id
        WHERE 1=1";

$params = [];
$types = '';

// Filtros
if($usuario_filter !== ''){
    $sql .= " AND (COALESCE(a.admin_name, s.staff_name) LIKE ?)";
    $params[] = "%$usuario_filter%";
    $types .= 's';
}
if($accion_filter !== ''){
    $sql .= " AND l.accion LIKE ?";
    $params[] = "%$accion_filter%";
    $types .= 's';
}
if($fecha_inicio !== ''){
    $sql .= " AND l.fecha >= ?";
    $params[] = $fecha_inicio.' 00:00:00';
    $types .= 's';
}
if($fecha_fin !== ''){
    $sql .= " AND l.fecha <= ?";
    $params[] = $fecha_fin.' 23:59:59';
    $types .= 's';
}

$sql .= " ORDER BY l.fecha DESC LIMIT 500";
$stmt = $mysqli->prepare($sql);

if(count($params) > 0){
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

require_once('../partials/_head.php');
?>

<body class="g-sidenav-show bg-gray-100">
  <div class="wrapper">
    <?php require_once('../partials/_sidebar.php'); ?>

    <div class="main-content">
      <?php require_once('../partials/_topnav.php'); ?>

      <!-- Header -->
      <div class="header bg-gradient-primary pb-6 pt-5 pt-md-6">
        <div class="container-fluid">
          <div class="header-body">
            <div class="row align-items-center py-4">
              <div class="col-lg-6 col-7">
                <h1 class="text-white display-4">📋 Auditoría del Sistema</h1>
                <p class="text-white mb-0">Consulta de movimientos recientes</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filtros -->
      <div class="container-fluid mt--6">
        <div class="row mb-3">
          <div class="col-12">
            <form method="GET" class="form-inline bg-white p-3 rounded shadow">
              <div class="form-group mr-2">
                <input type="text" name="usuario" placeholder="Usuario" class="form-control" value="<?= htmlspecialchars($usuario_filter) ?>">
              </div>
              <div class="form-group mr-2">
                <input type="text" name="accion" placeholder="Acción" class="form-control" value="<?= htmlspecialchars($accion_filter) ?>">
              </div>
              <div class="form-group mr-2">
                <input type="date" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($fecha_inicio) ?>">
              </div>
              <div class="form-group mr-2">
                <input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($fecha_fin) ?>">
              </div>
              <button type="submit" class="btn btn-primary">Filtrar</button>
              <a href="index.php" class="btn btn-secondary ml-2">Limpiar</a>
            </form>
          </div>
        </div>

        <!-- Tabla de logs -->
        <div class="row">
          <div class="col-12">
            <div class="card shadow">
              <div class="card-header border-0">
                <h3 class="mb-0">Registro de Actividades</h3>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table id="tablaLogs" class="table table-striped table-bordered table-hover table-sm">
                    <thead class="thead-dark">
                      <tr>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>Descripción</th>
                        <th>IP</th>
                        <th>Dispositivo</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                          <td><?= date('d/m/Y H:i', strtotime($row['fecha'])) ?></td>
                          <td><?= htmlspecialchars($row['usuario_nombre'] ?: $row['usuario_id']) ?></td>
                          <td><?= htmlspecialchars($row['accion']) ?></td>
                          <td><?= htmlspecialchars($row['descripcion']) ?></td>
                          <td><?= htmlspecialchars($row['ip_usuario']) ?></td>
                          <td><?= htmlspecialchars(substr($row['user_agent'], 0, 50)) ?>...</td>
                        </tr>
                      <?php endwhile; ?>
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
  </div>

  <!-- Scripts -->
  <script src="../assets/vendor/jquery/dist/jquery.min.js"></script>
  <script src="../assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/vendor/datatables.net/js/jquery.dataTables.min.js"></script>
  <script src="../assets/vendor/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
  <script src="../assets/vendor/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
  <script src="../assets/vendor/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>
  <script src="../assets/js/argon.js?v=1.2.0"></script>

  <script>
    $(document).ready(function() {
      $('#tablaLogs').DataTable({
        language: { url: "//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json" },
        order: [[0, "desc"]],
        responsive: true,
        autoWidth: false
      });
    });
  </script>
</body>
</html>
