<?php
// customes.php, mesa_pagos.php, etc.
session_start();
include('../config/config.php');
include('../config/checklogin.php');
check_login(); // ← Esto ahora establecerá $_SESSION['rol'] y $_SESSION['nombre']

require_once('../partials/_head.php');
// Ahora el sidebar tendrá acceso a las variables de sesión


// Eliminar cliente
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Verificar si el cliente tiene órdenes asociadas primero
    $checkOrders = "SELECT * FROM rpos_orders WHERE customer_id = ?";
    $stmtCheck = $mysqli->prepare($checkOrders);
    $stmtCheck->bind_param('s', $id);
    $stmtCheck->execute();
    $stmtCheck->store_result();
    
    if ($stmtCheck->num_rows > 0) {
        $err = "No se puede eliminar el cliente porque tiene órdenes asociadas";
    } else {
        $adn = "DELETE FROM rpos_customers WHERE customer_id = ?";
        $stmt = $mysqli->prepare($adn);
        $stmt->bind_param('s', $id);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            $success = "Cliente eliminado correctamente";
            header("refresh:1; url=customes.php");
        } else {
            $err = "Error al eliminar el cliente";
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
              <h1 class="display-3 text-white">👥 Gestión de Clientes</h1>
              <p class="text-white mb-0">Administra toda la información de tus clientes</p>
            </div>
            <div class="col-lg-6 col-5 text-right">
              <a href="add_customer.php" class="btn btn-neutral">
                <i class="fas fa-user-plus mr-2"></i> Nuevo Cliente
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
        <div class="col-xl-3 col-md-6">
          <div class="card card-stats bg-gradient-info">
            <div class="card-body">
              <div class="row">
                <div class="col">
                  <h5 class="card-title text-uppercase text-white mb-0">Total Clientes</h5>
                  <span class="h2 font-weight-bold text-white mb-0">
                    <?php
                    $query = "SELECT COUNT(*) as total FROM rpos_customers";
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
        
        <div class="col-xl-3 col-md-6">
          <div class="card card-stats bg-gradient-success">
            <div class="card-body">
              <div class="row">
                <div class="col">
                  <h5 class="card-title text-uppercase text-white mb-0">Personas Físicas</h5>
                  <span class="h2 font-weight-bold text-white mb-0">
                    <?php
                    $query = "SELECT COUNT(*) as total FROM rpos_customers WHERE tipo_cliente = 'Persona Física'";
                    $stmt = $mysqli->prepare($query);
                    $stmt->execute();
                    echo $stmt->get_result()->fetch_object()->total;
                    $stmt->close();
                    ?>
                  </span>
                </div>
                <div class="col-auto">
                  <div class="icon icon-shape bg-white text-success rounded-circle shadow">
                    <i class="fas fa-user"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
          <div class="card card-stats bg-gradient-warning">
            <div class="card-body">
              <div class="row">
                <div class="col">
                  <h5 class="card-title text-uppercase text-white mb-0">Empresas</h5>
                  <span class="h2 font-weight-bold text-white mb-0">
                    <?php
                    $query = "SELECT COUNT(*) as total FROM rpos_customers WHERE tipo_cliente = 'Empresa'";
                    $stmt = $mysqli->prepare($query);
                    $stmt->execute();
                    echo $stmt->get_result()->fetch_object()->total;
                    $stmt->close();
                    ?>
                  </span>
                </div>
                <div class="col-auto">
                  <div class="icon icon-shape bg-white text-warning rounded-circle shadow">
                    <i class="fas fa-building"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
          <div class="card card-stats bg-gradient-primary">
            <div class="card-body">
              <div class="row">
                <div class="col">
                  <h5 class="card-title text-uppercase text-white mb-0">Contribuyentes</h5>
                  <span class="h2 font-weight-bold text-white mb-0">
                    <?php
                    $query = "SELECT COUNT(*) as total FROM rpos_customers WHERE es_contribuyente = 1";
                    $stmt = $mysqli->prepare($query);
                    $stmt->execute();
                    echo $stmt->get_result()->fetch_object()->total;
                    $stmt->close();
                    ?>
                  </span>
                </div>
                <div class="col-auto">
                  <div class="icon icon-shape bg-white text-primary rounded-circle shadow">
                    <i class="fas fa-file-invoice-dollar"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabla de clientes -->
      <div class="row">
        <div class="col-12">
          <div class="card bg-secondary shadow">
            <div class="card-header bg-white border-0">
              <div class="row align-items-center">
                <div class="col-8">
                  <h3 class="mb-0">📋 Lista de Clientes</h3>
                </div>
                <div class="col-4 text-right">
                  <div class="btn-group" role="group">
                    <button class="btn btn-sm btn-outline-primary active" data-filter="all">Todos</button>
                    <button class="btn btn-sm btn-outline-primary" data-filter="Persona Física">Personas</button>
                    <button class="btn btn-sm btn-outline-primary" data-filter="Empresa">Empresas</button>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="card-body">
              <div class="table-responsive">
                <table class="table align-items-center table-flush" id="clientesTable">
                  <thead class="thead-light">
                    <tr>
                      <th>Cliente</th>
                      <th>Contacto</th>
                      <th>Identificación</th>
                      <th>Ubicación</th>
                      <th>Tipo</th>
                      <th>Registro</th>
                      <th>Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $ret = "SELECT * FROM rpos_customers ORDER BY created_at DESC";
                    $stmt = $mysqli->prepare($ret);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    
                    if ($res->num_rows > 0) {
                        while ($cust = $res->fetch_object()) {
                            $tipo_badge = $cust->tipo_cliente == 'Empresa' ? 'warning' : 'info';
                            $contribuyente_badge = $cust->es_contribuyente ? 'success' : 'secondary';
                    ?>
                    <tr data-tipo="<?php echo $cust->tipo_cliente; ?>">
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="avatar bg-gradient-<?php echo $tipo_badge; ?> rounded-circle mr-3">
                            <i class="fas <?php echo $cust->tipo_cliente == 'Empresa' ? 'fa-building' : 'fa-user'; ?> text-white"></i>
                          </div>
                          <div>
                            <span class="font-weight-bold"><?php echo htmlspecialchars($cust->customer_name); ?></span>
                            <br>
                            <small class="text-muted"><?php echo htmlspecialchars($cust->customer_email); ?></small>
                          </div>
                        </div>
                      </td>
                      <td>
                        <i class="fas fa-phone text-primary mr-2"></i> <?php echo htmlspecialchars($cust->customer_phoneno); ?>
                        <?php if ($cust->customer_email): ?>
                        <br>
                        <i class="fas fa-envelope text-info mr-2"></i> <?php echo htmlspecialchars($cust->customer_email); ?>
                        <?php endif; ?>
                      </td>
                      <td>
                        <span class="badge badge-light">
                          <?php echo $cust->tipo_cliente == 'Empresa' ? 'RNC:' : 'Cédula:'; ?>
                          <?php echo htmlspecialchars($cust->rnc_cedula); ?>
                        </span>
                        <?php if ($cust->es_contribuyente): ?>
                        <br>
                        <span class="badge badge-success">
                          <i class="fas fa-check-circle"></i> Contribuyente
                        </span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <?php if ($cust->direccion_fiscal): ?>
                        <span class="d-block text-sm">
                          <i class="fas fa-map-marker-alt text-danger mr-2"></i>
                          <?php echo htmlspecialchars($cust->direccion_fiscal); ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($cust->ciudad || $cust->sector): ?>
                        <small class="text-muted">
                          <?php echo htmlspecialchars($cust->ciudad); ?>
                          <?php echo $cust->sector ? ' - ' . htmlspecialchars($cust->sector) : ''; ?>
                        </small>
                        <?php endif; ?>
                      </td>
                      <td>
                        <span class="badge badge-<?php echo $tipo_badge; ?>">
                          <?php echo $cust->tipo_cliente; ?>
                        </span>
                      </td>
                      <td>
                        <?php echo date('d/m/Y', strtotime($cust->created_at)); ?>
                        <br>
                        <small class="text-muted"><?php echo date('H:i', strtotime($cust->created_at)); ?></small>
                      </td>
                      <td>
                        <div class="btn-group" role="group">
                          <a href="update_customer.php?update=<?php echo $cust->customer_id; ?>" class="btn btn-sm btn-info" title="Editar">
                            <i class="fas fa-edit"></i>
                          </a>
                          <a href="customes.php?delete=<?php echo $cust->customer_id; ?>" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Está seguro que desea eliminar este cliente?');">
                            <i class="fas fa-trash"></i>
                          </a>
                          <button class="btn btn-sm btn-primary view-details" data-customer-id="<?php echo $cust->customer_id; ?>" title="Ver detalles">
                            <i class="fas fa-eye"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                    <?php
                        }
                    } else {
                        echo '<tr><td colspan="7" class="text-center py-4">No hay clientes registrados</td></tr>';
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

  <!-- Modal para detalles del cliente -->
  <div class="modal fade" id="customerDetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-gradient-primary">
          <h5 class="modal-title text-white">👤 Detalles del Cliente</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="customerDetailsContent">
          <!-- Los detalles se cargarán aquí via AJAX -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
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
              <a href="" class="nav-link" target="_blank">   &copy; 2025 - <?php echo date('Y'); ?> ®️ PEDRO UREÑA TODOS LOS DERECHOS RESERVADOS</a>
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
    .bg-gradient-info { background: linear-gradient(87deg, #11cdef 0, #1171ef 100%) !important; }
    .bg-gradient-success { background: linear-gradient(87deg, #2dce89 0, #2dcecc 100%) !important; }
    .bg-gradient-warning { background: linear-gradient(87deg, #fb6340 0, #fbb140 100%) !important; }
    .bg-gradient-primary { background: linear-gradient(87deg, #5e72e4 0, #825ee4 100%) !important; }
  </style>
  
  <script>
    $(document).ready(function() {
      // DataTables
      $('#clientesTable').DataTable({
        "language": {
          "url": "//cdn.datatables.net/plug-ins/1.11.3/i18n/es_es.json"
        },
        "order": [[5, "desc"]],
        "responsive": true,
        "autoWidth": false
      });
      
      // Filtrar por tipo de cliente
      $('[data-filter]').click(function() {
        var filtro = $(this).data('filter');
        $('[data-filter]').removeClass('active');
        $(this).addClass('active');
        
        if (filtro === 'all') {
          $('#clientesTable tbody tr').show();
        } else {
          $('#clientesTable tbody tr').hide();
          $('#clientesTable tbody tr[data-tipo="' + filtro + '"]').show();
        }
      });
      
      // Ver detalles del cliente
      $('.view-details').click(function() {
        var customerId = $(this).data('customer-id');
        
        $.ajax({
          url: 'get_customer_details.php',
          type: 'GET',
          data: { customer_id: customerId },
          success: function(response) {
            $('#customerDetailsContent').html(response);
            $('#customerDetailsModal').modal('show');
          },
          error: function() {
            alert('Error al cargar los detalles del cliente');
          }
        });
      });
    });
  </script>
</body>
</html>