<?php
session_start();
include('../config/config.php');
include('../config/checklogin.php');
include('../config/code-generator.php');

check_login();

// Definir roles disponibles según ENUM
$roles = ['Mesero','Cocinero','Bartender','Delivery','Cajero','Administrador'];

// Proceso para agregar nuevo miembro del personal
if (isset($_POST['addStaff'])) {
    // Validar campos obligatorios
    if (empty($_POST["staff_number"]) || empty($_POST["staff_name"]) || empty($_POST['staff_email']) || empty($_POST['staff_password']) || empty($_POST['rol'])) {
        $err = "Todos los campos obligatorios deben completarse";
    } else {
        $staff_number = $_POST['staff_number'];
        $staff_name = $_POST['staff_name'];
        $staff_email = $_POST['staff_email'];
        $staff_password = password_hash($_POST['staff_password'], PASSWORD_BCRYPT);
        $telefono = $_POST['telefono'] ?? '';
        $direccion = $_POST['direccion'] ?? '';
        $rol = in_array($_POST['rol'], $roles) ? $_POST['rol'] : 'Mesero';
        $estado = 'Activo'; // Por defecto activo para poder iniciar sesión

        // Verificar si el correo ya existe
        $checkQuery = "SELECT staff_email FROM rpos_staff WHERE staff_email = ?";
        $checkStmt = $mysqli->prepare($checkQuery);
        $checkStmt->bind_param('s', $staff_email);
        $checkStmt->execute();
        $checkStmt->store_result();
        
        if ($checkStmt->num_rows > 0) {
            $err = "El correo electrónico ya está registrado";
        } else {
            // Insertar datos en la base de datos
            $postQuery = "INSERT INTO rpos_staff (staff_number, staff_name, staff_email, staff_password, telefono, direccion, rol, estado) VALUES(?,?,?,?,?,?,?,?)";
            $postStmt = $mysqli->prepare($postQuery);
            $postStmt->bind_param('ssssssss', $staff_number, $staff_name, $staff_email, $staff_password, $telefono, $direccion, $rol, $estado);
            $postStmt->execute();
            
            if ($postStmt) {
                $success = "Personal registrado exitosamente";
                header("refresh:1; url=hrm.php");
            } else {
                $err = "Error al registrar, por favor intente nuevamente";
            }
        }
        $checkStmt->close();
    }
}

require_once('../partials/_head.php');
?>

<body>
  <?php require_once('../partials/_sidebar.php'); ?>
  <div class="main-content">
    <?php require_once('../partials/_topnav.php'); ?>
    
    <div style="background-image: url(assets/img/theme/restro00.jpg); background-size: cover;" class="header pb-8 pt-5 pt-md-8">
      <span class="mask bg-gradient-dark opacity-8"></span>
      <div class="container-fluid">
        <div class="header-body"></div>
      </div>
    </div>
    
    <div class="container-fluid mt--8">
      <div class="row">
        <div class="col">
          <div class="card shadow">
            <div class="card-header border-0 bg-primary text-white">
              <h3 class="mb-0">Registro de Nuevo Personal</h3>
              <p class="mb-0">Complete todos los campos requeridos</p>
            </div>
            
            <div class="card-body">
              <?php if(isset($err)): ?>
                  <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
              <?php elseif(isset($success)): ?>
                  <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
              <?php endif; ?>

              <form method="POST" id="staffForm">
                <div class="form-row">
                  <div class="col-md-6 mb-3">
                    <label for="staff_number">Número de Empleado</label>
                    <input type="text" id="staff_number" name="staff_number" class="form-control" value="<?php echo $alpha; ?>-<?php echo $beta; ?>" readonly>
                  </div>
                  
                  <div class="col-md-6 mb-3">
                    <label for="staff_name">Nombre Completo *</label>
                    <input type="text" id="staff_name" name="staff_name" class="form-control" required>
                  </div>
                </div>
                
                <div class="form-row">
                  <div class="col-md-6 mb-3">
                    <label for="staff_email">Correo Electrónico *</label>
                    <input type="email" id="staff_email" name="staff_email" class="form-control" required>
                  </div>
                  
                  <div class="col-md-6 mb-3">
                    <label for="telefono">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono" class="form-control" placeholder="809-555-5555">
                  </div>
                </div>
                
                <div class="form-row">
                  <div class="col-md-6 mb-3">
                    <label for="rol">Rol *</label>
                    <select id="rol" name="rol" class="form-control" required>
                      <?php foreach($roles as $r): ?>
                        <option value="<?php echo $r; ?>"><?php echo $r; ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  
                  <div class="col-md-6 mb-3">
                    <label for="staff_password">Contraseña *</label>
                    <div class="input-group">
                      <input type="password" id="staff_password" name="staff_password" class="form-control" required minlength="6">
                      <div class="input-group-append">
                        <button class="btn btn-outline-secondary toggle-password" type="button">
                          <i class="fas fa-eye"></i>
                        </button>
                      </div>
                    </div>
                    <small class="form-text text-muted">Mínimo 6 caracteres</small>
                  </div>
                </div>
                
                <div class="form-row">
                  <div class="col-md-12 mb-3">
                    <label for="direccion">Dirección</label>
                    <textarea id="direccion" name="direccion" class="form-control" rows="2" placeholder="Dirección completa"></textarea>
                  </div>
                </div>
                
                <div class="form-row mt-4">
                  <div class="col-md-6">
                    <button type="submit" name="addStaff" class="btn btn-success btn-lg">
                      <i class="fas fa-user-plus mr-2"></i> Registrar Personal
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      
      <?php require_once('../partials/_footer.php'); ?>
    </div>
  </div>
  
  <?php require_once('../partials/_scripts.php'); ?>
  
  <script>
    $(document).ready(function() {
      $('.toggle-password').click(function() {
        const passwordInput = $('#staff_password');
        const icon = $(this).find('i');
        if (passwordInput.attr('type') === 'password') {
          passwordInput.attr('type', 'text');
          icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
          passwordInput.attr('type', 'password');
          icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
      });

      $('#staffForm').submit(function() {
        const password = $('#staff_password').val();
        if (password.length < 6) {
          alert('La contraseña debe tener al menos 6 caracteres');
          return false;
        }
        return true;
      });

      $('#telefono').on('input', function() {
        this.value = this.value.replace(/[^0-9-]/g, '');
      });
    });
  </script>
</body>
</html>
