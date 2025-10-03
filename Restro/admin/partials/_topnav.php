<?php
// partials/_sidebar.php
ob_start();

// Determinar la ruta base
$current_path = $_SERVER['PHP_SELF'];
$folders = ['factura', 'delivery', 'auditoria', 'cocina', 'mesas', 'bar', 'usuario', 'hrm', 'customes', 'products', 'empresa', 'notificaciones'];
$is_in_subfolder = false;

foreach ($folders as $folder) {
    if (strpos($current_path, "/$folder/") !== false) {
        $is_in_subfolder = true;
        break;
    }
}

$base_path = $is_in_subfolder ? '../' : '';
// Verificar si la sesión está activa
if (!isset($_SESSION)) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: " . $base_path . "index.php");
    exit();
}

// Incluir la conexión a la base de datos si no está definida
if (!isset($mysqli)) {
    require_once($base_path . 'config/config.php');
}

$user_id = $_SESSION['user_id'] ?? null;
$rol     = $_SESSION['rol'] ?? null;
$user = null;

if ($user_id && $rol) {
    if ($rol === 'admin') {
        $query = "SELECT admin_name AS nombre FROM rpos_admin WHERE admin_id = ?";
    } else {
        $query = "SELECT staff_name AS nombre FROM rpos_staff WHERE staff_id = ?";
    }

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_object();
    $stmt->close();
}
?>

<?php if (!empty($user)) { ?>
    <nav class="navbar navbar-top navbar-expand-md navbar-dark" id="navbar-main">
        <div class="container-fluid">
            <a class="h4 mb-0 text-white text-uppercase d-none d-lg-inline-block" href="<?php echo $base_path; ?>dashboard.php">
                <?php echo htmlspecialchars($user->nombre); ?> Dashboard
            </a>




            <li class="nav-item dropdown">
                <a class="nav-link" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="ni ni-bell-55"></i>
                    <?php
                    $query_count = "SELECT COUNT(*) as count FROM rpos_notificaciones WHERE estado = 'pendiente'";
                    $count_result = $mysqli->query($query_count);
                    $count = $count_result->fetch_object()->count;
                    if ($count > 0): ?>
                        <span class="notification-badge"><?php echo $count; ?></span>
                    <?php endif; ?>
                </a>
                <div class="dropdown-menu dropdown-menu-right dropdown-menu-xl py-0">
                    <div class="px-3 py-3">
                        <h6 class="text-sm text-muted m-0">Tienes <strong><?php echo $count; ?></strong> notificaciones</h6>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php
                        $query_notif = "SELECT * FROM rpos_notificaciones WHERE estado = 'pendiente' ORDER BY created_at DESC LIMIT 5";
                        $notif_result = $mysqli->query($query_notif);
                        while ($notif = $notif_result->fetch_object()): ?>
                            <a href="../admin/notificaciones/notificaciones.php" class="list-group-item list-group-item-action">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <i class="ni ni-bell-55 text-primary"></i>
                                    </div>
                                    <div class="col ml--2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h4 class="mb-0 text-sm"><?php echo $notif->mensaje; ?></h4>
                                        </div>
                                        <p class="text-sm text-muted mb-0"><?php echo date('H:i', strtotime($notif->created_at)); ?></p>
                                    </div>
                                </div>
                            </a>
                        <?php endwhile; ?>
                    </div>
                    <a href="../admin/notificaciones/notificaciones.php" class="dropdown-item text-center text-primary font-weight-bold py-3">
                        Ver todas las notificaciones
                    </a>
                </div>
            </li>

<ul class="navbar-nav align-items-center d-none d-md-flex">
    <li class="nav-item dropdown">
        <a class="nav-link pr-0" href="#" role="button" data-toggle="dropdown">
            <div class="media align-items-center">
                <span class="avatar avatar-sm rounded-circle">
                    <img alt="Image placeholder" src="<?php echo $base_path; ?>assets/img/theme/user-a-min.png">
                </span>
                <div class="media-body ml-2 d-none d-lg-block">
                    <span class="mb-0 text-sm font-weight-bold"><?php echo htmlspecialchars($user->nombre); ?></span>
                </div>
            </div>
        </a>
        <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-right">
            <div class="dropdown-header noti-title">
                <h6 class="text-overflow m-0">Bienvenido!</h6>
            </div>
            <a href="<?php echo $base_path; ?>change_profile.php" class="dropdown-item">
                <i class="ni ni-single-02"></i>
                <span>Mi perfil</span>
            </a>
            <div class="dropdown-divider"></div>
            <!-- Logout con base_path dinámico -->
            <a href="<?php echo $base_path; ?>logout.php" class="dropdown-item">
                <i class="ni ni-user-run"></i>
                <span>Cerrar sesión</span>
            </a>
        </div>
    </li>
</ul>


        </div>
    </nav>
<?php } ?>