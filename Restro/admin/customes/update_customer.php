<?php
session_start();
include('../config/config.php');
include('../config/checklogin.php');
include('../config/code-generator.php');

check_login();

// Update Customer
if (isset($_POST['updateCustomer'])) {
    if (empty($_POST["customer_phoneno"]) || empty($_POST["customer_name"]) || empty($_POST['customer_email'])) {
        $err = "Todos los campos obligatorios deben ser completados";
    } else {
        $customer_name = $_POST['customer_name'];
        $customer_phoneno = $_POST['customer_phoneno'];
        $customer_email = $_POST['customer_email'];
        $tipo_cliente = $_POST['tipo_cliente'] ?? 'Persona Física';
        $rnc_cedula = $_POST['rnc_cedula'] ?? '';
        $direccion_fiscal = $_POST['direccion_fiscal'] ?? '';
        $ciudad = $_POST['ciudad'] ?? '';
        $sector = $_POST['sector'] ?? '';
        $referencia = $_POST['referencia'] ?? '';
        $es_contribuyente = isset($_POST['es_contribuyente']) ? 1 : 0;
        $update = $_GET['update'];

        // Si se proporciona una nueva contraseña, actualizarla
        $password_update = "";
        $params = array($customer_name, $customer_phoneno, $customer_email, $tipo_cliente, $rnc_cedula, $direccion_fiscal, $ciudad, $sector, $referencia, $es_contribuyente);
        
        if (!empty($_POST['customer_password'])) {
            $customer_password = sha1(md5($_POST['customer_password']));
            $password_update = ", customer_password = ?";
            $params[] = $customer_password;
        }
        
        $params[] = $update;

        $postQuery = "UPDATE rpos_customers SET 
                     customer_name = ?, customer_phoneno = ?, customer_email = ?, 
                     tipo_cliente = ?, rnc_cedula = ?, direccion_fiscal = ?, ciudad = ?, 
                     sector = ?, referencia = ?, es_contribuyente = ?
                     $password_update 
                     WHERE customer_id = ?";
        
        $postStmt = $mysqli->prepare($postQuery);
        
        // Bind parameters dynamically
        $types = str_repeat('s', count($params));
        $postStmt->bind_param($types, ...$params);
        
        $postStmt->execute();

        if ($postStmt->affected_rows > 0) {
            $success = "Cliente actualizado correctamente";
            header("refresh:1; url=customes.php");
        } else {
            $err = "Error al actualizar el cliente o no hubo cambios";
        }
        $postStmt->close();
    }
}

require_once('../partials/_head.php');
?>

<body>
  <?php require_once('../partials/_sidebar.php'); ?>
  
  <div class="main-content">
    <?php require_once('../partials/_topnav.php'); ?>
    
    <?php
    $update = $_GET['update'];
    $ret = "SELECT * FROM rpos_customers WHERE customer_id = ?";
    $stmt = $mysqli->prepare($ret);
    $stmt->bind_param('s', $update);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows > 0) {
        $cust = $res->fetch_object();
    ?>
    
    <!-- Header -->
    <div class="header bg-gradient-primary pb-8 pt-5 pt-md-8">
      <div class="container-fluid">
        <div class="header-body">
          <div class="row align-items-center py-4">
            <div class="col-lg-6 col-7">
              <h1 class="display-3 text-white">👥 Actualizar Cliente</h1>
              <p class="text-white mb-0">Modifica la información del cliente</p>
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

      <!-- Formulario -->
      <div class="row">
        <div class="col-xl-8">
          <div class="card bg-secondary shadow">
            <div class="card-header bg-white border-0">
              <div class="row align-items-center">
                <div class="col-8">
                  <h3 class="mb-0">Información del Cliente</h3>
                </div>
              </div>
            </div>
            
            <div class="card-body">
              <form method="POST" id="customerForm">
                <h6 class="heading-small text-muted mb-4">Información Básica</h6>
                
                <div class="pl-lg-4">
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label class="form-control-label" for="customer_name">Nombre Completo *</label>
                        <input type="text" id="customer_name" name="customer_name" class="form-control" value="<?php echo htmlspecialchars($cust->customer_name); ?>" required>
                      </div>
                    </div>
                    
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label class="form-control-label" for="customer_phoneno">Teléfono *</label>
                        <input type="tel" id="customer_phoneno" name="customer_phoneno" class="form-control" value="<?php echo htmlspecialchars($cust->customer_phoneno); ?>" required>
                      </div>
                    </div>
                  </div>
                  
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label class="form-control-label" for="customer_email">Correo Electrónico *</label>
                        <input type="email" id="customer_email" name="customer_email" class="form-control" value="<?php echo htmlspecialchars($cust->customer_email); ?>" required>
                      </div>
                    </div>
                    
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label class="form-control-label" for="tipo_cliente">Tipo de Cliente *</label>
                        <select id="tipo_cliente" name="tipo_cliente" class="form-control" required>
                          <option value="Persona Física" <?php echo $cust->tipo_cliente == 'Persona Física' ? 'selected' : ''; ?>>Persona Física</option>
                          <option value="Empresa" <?php echo $cust->tipo_cliente == 'Empresa' ? 'selected' : ''; ?>>Empresa</option>
                        </select>
                      </div>
                    </div>
                  </div>
                  
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label class="form-control-label" for="rnc_cedula" id="label_rnc_cedula">
                          <?php echo $cust->tipo_cliente == 'Empresa' ? 'RNC *' : 'Cédula *'; ?>
                        </label>
                        <input type="text" id="rnc_cedula" name="rnc_cedula" class="form-control" value="<?php echo htmlspecialchars($cust->rnc_cedula ?? ''); ?>" required>
                      </div>
                    </div>
                    
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label class="form-control-label">&nbsp;</label>
                        <div class="custom-control custom-checkbox mt-2">
                          <input class="custom-control-input" type="checkbox" name="es_contribuyente" id="es_contribuyente" value="1" <?php echo $cust->es_contribuyente ? 'checked' : ''; ?>>
                          <label class="custom-control-label" for="es_contribuyente">
                            Es contribuyente (Requiere factura fiscal)
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <hr class="my-4">
                <h6 class="heading-small text-muted mb-4">Dirección</h6>
                
                <div class="pl-lg-4">
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label class="form-control-label" for="ciudad">Ciudad</label>
                        <input type="text" id="ciudad" name="ciudad" class="form-control" value="<?php echo htmlspecialchars($cust->ciudad ?? ''); ?>" placeholder="Ej: Santo Domingo">
                      </div>
                    </div>
                    
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label class="form-control-label" for="sector">Sector/Barrio</label>
                        <input type="text" id="sector" name="sector" class="form-control" value="<?php echo htmlspecialchars($cust->sector ?? ''); ?>" placeholder="Ej: Naco, Piantini, etc.">
                      </div>
                    </div>
                  </div>
                  
                  <div class="row">
                    <div class="col-lg-12">
                      <div class="form-group">
                        <label class="form-control-label" for="direccion_fiscal">Dirección Completa *</label>
                        <textarea id="direccion_fiscal" name="direccion_fiscal" class="form-control" rows="3" required placeholder="Calle, número, edificio, etc."><?php echo htmlspecialchars($cust->direccion_fiscal ?? ''); ?></textarea>
                      </div>
                    </div>
                  </div>
                  
                  <div class="row">
                    <div class="col-lg-12">
                      <div class="form-group">
                        <label class="form-control-label" for="referencia">Referencias de la Dirección</label>
                        <textarea id="referencia" name="referencia" class="form-control" rows="2" placeholder="Puntos de referencia, colores, etc."><?php echo htmlspecialchars($cust->referencia ?? ''); ?></textarea>
                      </div>
                    </div>
                  </div>
                </div>
                
                <hr class="my-4">
                <h6 class="heading-small text-muted mb-4">Seguridad</h6>
                
                <div class="pl-lg-4">
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label class="form-control-label" for="customer_password">Nueva Contraseña</label>
                        <div class="input-group">
                          <input type="password" id="customer_password" name="customer_password" class="form-control" placeholder="Dejar vacío para mantener la actual">
                          <div class="input-group-append">
                            <button class="btn btn-outline-secondary toggle-password" type="button">
                              <i class="fas fa-eye"></i>
                            </button>
                          </div>
                        </div>
                        <small class="form-text text-muted">Mínimo 6 caracteres. Dejar vacío para no cambiar.</small>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="text-center mt-4">
                  <button type="submit" name="updateCustomer" class="btn btn-success btn-lg">
                    <i class="fas fa-save mr-2"></i> Actualizar Cliente
                  </button>
                  <a href="customes.php" class="btn btn-secondary btn-lg ml-2">
                    <i class="fas fa-times mr-2"></i> Cancelar
                  </a>
                </div>
              </form>
            </div>
          </div>
        </div>
        
        <!-- Tarjeta de información -->
        <div class="col-xl-4">
          <div class="card shadow">
            <div class="card-header bg-transparent">
              <h3 class="mb-0">Información Actual</h3>
            </div>
            <div class="card-body">
              <div class="text-center">
                <div class="avatar bg-gradient-<?php echo $cust->tipo_cliente == 'Empresa' ? 'warning' : 'info'; ?> rounded-circle mx-auto mb-3" style="width: 80px; height: 80px;">
                  <i class="fas <?php echo $cust->tipo_cliente == 'Empresa' ? 'fa-building' : 'fa-user'; ?> text-white" style="font-size: 2rem;"></i>
                </div>
                
                <h4 class="mb-1"><?php echo htmlspecialchars($cust->customer_name); ?></h4>
                <span class="badge badge-<?php echo $cust->tipo_cliente == 'Empresa' ? 'warning' : 'info'; ?>">
                  <?php echo $cust->tipo_cliente; ?>
                </span>
                
                <?php if ($cust->es_contribuyente): ?>
                <span class="badge badge-success ml-1">
                  <i class="fas fa-check-circle"></i> Contribuyente
                </span>
                <?php endif; ?>
                
                <div class="mt-3">
                  <p class="mb-1"><i class="fas fa-phone text-primary mr-2"></i> <?php echo htmlspecialchars($cust->customer_phoneno); ?></p>
                  <p class="mb-1"><i class="fas fa-envelope text-info mr-2"></i> <?php echo htmlspecialchars($cust->customer_email); ?></p>
                  <p class="mb-1">
                    <i class="fas fa-id-card text-success mr-2"></i> 
                    <?php echo $cust->tipo_cliente == 'Empresa' ? 'RNC:' : 'Cédula:'; ?>
                    <?php echo htmlspecialchars($cust->rnc_cedula); ?>
                  </p>
                  <?php if ($cust->direccion_fiscal): ?>
                  <p class="mb-1"><i class="fas fa-map-marker-alt text-danger mr-2"></i> <?php echo htmlspecialchars($cust->direccion_fiscal); ?></p>
                  <?php endif; ?>
                  <?php if ($cust->ciudad || $cust->sector): ?>
                  <p class="mb-0 text-muted">
                    <?php echo htmlspecialchars($cust->ciudad); ?>
                    <?php echo $cust->sector ? ' - ' . htmlspecialchars($cust->sector) : ''; ?>
                  </p>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <?php require_once('../partials/_footer.php'); ?>
    </div>
    
    <?php 
    } else {
        echo '<div class="container-fluid mt--7"><div class="alert alert-danger">Cliente no encontrado</div></div>';
    }
    $stmt->close();
    ?>
  </div>
  
  <?php require_once('../partials/_scripts.php'); ?>
  
  <style>
    .avatar {
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .bg-gradient-info { background: linear-gradient(87deg, #11cdef 0, #1171ef 100%) !important; }
    .bg-gradient-warning { background: linear-gradient(87deg, #fb6340 0, #fbb140 100%) !important; }
  </style>
  
  <script>
    $(document).ready(function() {
      // Mostrar/ocultar contraseña
      $('.toggle-password').click(function() {
        const passwordInput = $('#customer_password');
        const icon = $(this).find('i');
        
        if (passwordInput.attr('type') === 'password') {
          passwordInput.attr('type', 'text');
          icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
          passwordInput.attr('type', 'password');
          icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
      });
      
      // Cambiar label según tipo de cliente
      $('#tipo_cliente').change(function() {
        const tipo = $(this).val();
        if (tipo === 'Empresa') {
          $('#label_rnc_cedula').text('RNC *');
          $('#rnc_cedula').attr('placeholder', 'RNC de la empresa');
        } else {
          $('#label_rnc_cedula').text('Cédula *');
          $('#rnc_cedula').attr('placeholder', 'Cédula de identidad');
        }
      });
      
      // Validación del formulario
      $('#customerForm').submit(function() {
        const password = $('#customer_password').val();
        if (password && password.length < 6) {
          alert('La contraseña debe tener al menos 6 caracteres');
          return false;
        }
        
        const rncCedula = $('#rnc_cedula').val();
        if (!rncCedula) {
          alert('El campo Cédula/RNC es obligatorio');
          return false;
        }
        
        return true;
      });
      
      // Formato de teléfono
      $('#customer_phoneno').on('input', function() {
        this.value = this.value.replace(/[^0-9-]/g, '');
      });
      
      // Formato de cédula/RNC
      $('#rnc_cedula').on('input', function() {
        this.value = this.value.replace(/[^0-9-]/g, '');
      });
    });
  </script>
</body>
</html>