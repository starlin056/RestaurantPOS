<?php
ob_start();
// Determinar la ruta base
$folders = ['factura', 'delivery', 'auditoria', 'cocina', 'mesas', 'bar', 'usuario', 'hrm', 'customes', 'products', 'empresa','notificaciones'];
$is_in_subfolder = false;

foreach ($folders as $folder) {
  if (strpos($current_path, "/$folder/") !== false) {
    $is_in_subfolder = true;
    break;
  }
}

$base_path = $is_in_subfolder ? '../' : '';

// Verificar si la sesión está activa
if (!isset($_SESSION['user_id'])) {
  header("Location: " . $base_path . "index.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$rol = $_SESSION['rol'] ?? null;
$nombre = $_SESSION['nombre'] ?? 'Usuario';
?>

<style>
  /* Estilos para los títulos de secciones */
  .sidebar-section-title {
    padding: 6px 10px;
    border-radius: 8px;
    font-weight: bold;
    letter-spacing: 1px;
    color: #fff !important;
    margin: 8px 0;
    transition: 0.3s ease-in-out;
  }
  .sidebar-section-title:hover {
    filter: brightness(1.1);
  }

  /* Colores dinámicos */
  .section-comanda { background:  linear-gradient(45deg, #009688, #26c6da); }
  .section-delivery { background:  linear-gradient(45deg, #009688, #26c6da); }
  .section-factura { background:  linear-gradient(45deg, #009688, #26c6da); }
  .section-empresa { background:  linear-gradient(45deg, #009688, #26c6da); }
  .section-admin { background:  linear-gradient(45deg, #009688, #26c6da); }
  .section-reportes { background: linear-gradient(45deg, #009688, #26c6da); }
</style>

<nav class="navbar navbar-vertical fixed-left navbar-expand-md navbar-light bg-white" id="sidenav-main">
  <div class="container-fluid">
    <!-- Marca -->
    <a class="navbar-brand pt-0" href="<?php echo $base_path; ?>dashboard.php">
      <img src="<?php echo $base_path; ?>assets/img/brand/repos.png" class="navbar-brand-img" alt="Logo">
    </a>

    <!-- Menú lateral -->
    <div class="collapse navbar-collapse" id="sidenav-collapse-main">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" href="<?php echo $base_path; ?>dashboard.php">
            <i class="ni ni-tv-2 text-primary"></i> Panel
          </a>
        </li>

        <?php if ($rol === 'admin'): ?>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo $base_path; ?>auditoria/index.php">
              <i class="ni ni-user-run"></i> Auditoria
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo $base_path; ?>usuario/usuarios_vista.php">
              <i class="ni ni-bullet-list-67 text-primary"></i> Usuarios
            </a>
          </li>
        <?php endif; ?>

        <?php if (in_array($rol, ['admin', 'Cajero', 'Supervisor', 'Mesero'])): ?>
          <hr class="my-3">
          <div class="text-center w-100">
            <h6 class="navbar-heading sidebar-section-title section-comanda">COMANDA</h6>
          </div>

          <li class="nav-item"><a class="nav-link" href="<?php echo $base_path; ?>mesas/mesas.php"><i class="fas fa-chair text-primary"></i> Mesas</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo $base_path; ?>cocina/cocina.php"><i class="fas fa-utensils text-primary"></i> Cocina</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo $base_path; ?>bar/bar.php"><i class="fas fa-cocktail text-primary"></i> Bar</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo $base_path; ?>products/products.php"><i class="ni ni-circle-08 text-primary"></i> Productos</a></li>
        <?php endif; ?>

        <?php if (in_array($rol, ['admin', 'Cajero', 'Supervisor', 'Mesero'])): ?>
          <hr class="my-3">
          <div class="text-center w-100">
            <h6 class="navbar-heading sidebar-section-title section-delivery">DELIVERY COMANDA</h6>
          </div>

          <li class="nav-item"><a class="nav-link" href="<?php echo $base_path; ?>delivery/index.php"><i class="ni ni-delivery-fast text-primary"></i> Delivery</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo $base_path; ?>delivery/delivery_lista.php"><i class="ni ni-delivery-fast text-primary"></i> Delivery pedidos</a></li>
        <?php endif; ?>

        <?php if ($rol === 'Cajero' || $rol === 'admin'): ?>
          <hr class="my-3">
          <div class="text-center w-100">
            <h6 class="navbar-heading sidebar-section-title section-factura">FACTURA</h6>
          </div>

          <li class="nav-item"><a class="nav-link" href="<?php echo $base_path; ?>factura/mesa_pagos.php"><i class="ni ni-credit-card text-primary"></i> Factura</a></li>
        <?php endif; ?>

        <?php if ($rol === 'admin'): ?>
          <hr class="my-3">
          <div class="text-center w-100">
            <h6 class="navbar-heading sidebar-section-title section-empresa">AJUSTES EMPRESA</h6>
          </div>
          <li class="nav-item"><a class="nav-link" href="<?php echo $base_path; ?>empresa/configuracion_factura.php"><i class="ni ni-credit-card text-primary"></i> Empresa</a></li>

          <hr class="my-3">
          <div class="text-center w-100">
            <h6 class="navbar-heading sidebar-section-title section-admin">ADMINISTRACIÓN</h6>
          </div>
          <li class="nav-item"><a class="nav-link" href="<?php echo $base_path; ?>hrm/hrm.php"><i class="fas fa-user-tie text-primary"></i> Recursos Humanos</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo $base_path; ?>customes/customes.php"><i class="fas fa-users text-primary"></i> Clientes</a></li>

          <hr class="my-3">
          <div class="text-center w-100">
            <h6 class="navbar-heading sidebar-section-title section-reportes">REPORTES</h6>
          </div>
          <li class="nav-item"><a class="nav-link" href="<?php echo $base_path; ?>orders_reports.php"><i class="fas fa-shopping-basket"></i> Pedidos</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo $base_path; ?>factura/caja_reportes.php"><i class="fas fa-receipt"></i> Reporte de caja</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo $base_path; ?>factura/lista.php"><i class="fas fa-funnel-dollar"></i> Lista Facturas</a></li>
        <?php endif; ?>

        <hr class="my-3">
        <li class="nav-item"><a class="nav-link" href="<?php echo $base_path; ?>logout.php"><i class="fas fa-sign-out-alt text-danger"></i> Cerrar sesión</a></li>
      </ul>
    </div>
  </div>
</nav>

<?php
ob_end_flush();
