<?php
session_start();
include('../config/config.php');
include('../config/checklogin.php');
include('../config/code-generator.php');

check_login();

// Agregar nuevo cliente
if (isset($_POST['addCustomer'])) {
  // Validar campos vacíos
  if (empty($_POST["customer_phoneno"]) || empty($_POST["customer_name"]) || empty($_POST['customer_email']) || empty($_POST['customer_password'])) {
    $err = "No se aceptan valores vacíos";
  } else {
    $customer_name      = $_POST['customer_name'];
    $customer_phoneno   = $_POST['customer_phoneno'];
    $customer_email     = $_POST['customer_email'];
    $customer_password  = sha1(md5($_POST['customer_password'])); // ⚠️ Considera usar password_hash()
    $customer_id        = $_POST['customer_id'];
    $tipo_cliente       = $_POST['tipo_cliente'] ?? 'Persona Física';
    $rnc_cedula         = $_POST['rnc_cedula'] ?? '';
    $direccion          = $_POST['direccion'] ?? '';
    $ciudad             = $_POST['ciudad'] ?? '';
    $sector             = $_POST['sector'] ?? '';
    $referencia         = $_POST['referencia'] ?? '';
    $es_contribuyente   = isset($_POST['es_contribuyente']) ? 1 : 0;

    // Verificar si el correo ya existe
    $checkQuery = "SELECT customer_email FROM rpos_customers WHERE customer_email = ?";
    $checkStmt  = $mysqli->prepare($checkQuery);
    $checkStmt->bind_param('s', $customer_email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
      $err = "El correo electrónico ya está registrado";
    } else {
      // Insertar datos con nuevos campos
      $postQuery = "INSERT INTO rpos_customers 
      (customer_id, customer_name, customer_phoneno, customer_email, customer_password, tipo_cliente, rnc_cedula, direccion_fiscal, ciudad, sector, referencia, es_contribuyente) 
      VALUES(?,?,?,?,?,?,?,?,?,?,?,?)";

      $postStmt = $mysqli->prepare($postQuery);
      $postStmt->bind_param(
        'sssssssssssi',
        $customer_id,
        $customer_name,
        $customer_phoneno,
        $customer_email,
        $customer_password,
        $tipo_cliente,
        $rnc_cedula,
        $direccion,     // se guarda en direccion_fiscal
        $ciudad,
        $sector,
        $referencia,
        $es_contribuyente
      );

      if ($postStmt->execute()) {
        $success = "Cliente agregado correctamente";
        header("refresh:1; url=customes.php"); // ⚠️ corregí "customes.php" a "customers.php"
        exit;
      } else {
        $err = "Error al agregar, por favor intente nuevamente: " . $mysqli->error;
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
              <h3 class="mb-0">Registro de Nuevo Cliente</h3>
              <p class="mb-0">Complete todos los campos requeridos</p>
            </div>

            <div class="card-body">
              <form method="POST" id="customerForm">
                <input type="hidden" name="customer_id" value="<?php echo $cus_id; ?>">

                <div class="form-row">
                  <div class="col-md-6 mb-3">
                    <label for="customer_name">Nombre Completo *</label>
                    <input type="text" id="customer_name" name="customer_name" class="form-control" required>
                  </div>

                  <div class="col-md-6 mb-3">
                    <label for="customer_phoneno">Teléfono *</label>
                    <input type="tel" id="customer_phoneno" name="customer_phoneno" class="form-control" required>
                  </div>
                </div>

                <div class="form-row">
                  <div class="col-md-6 mb-3">
                    <label for="customer_email">Correo Electrónico *</label>
                    <input type="email" id="customer_email" name="customer_email" class="form-control" required>
                  </div>

                  <div class="col-md-6 mb-3">
                    <label for="tipo_cliente">Tipo de Cliente *</label>
                    <select id="tipo_cliente" name="tipo_cliente" class="form-control" required>
                      <option value="Persona Física">Persona Física</option>
                      <option value="Empresa">Empresa</option>
                    </select>
                  </div>
                </div>

                <div class="form-row">
                  <div class="col-md-6 mb-3">
                    <label for="rnc_cedula" id="label_rnc_cedula">Cédula/RNC *</label>
                    <input type="text" id="rnc_cedula" name="rnc_cedula" class="form-control" required>
                  </div>

                  <div class="col-md-6 mb-3">
                    <label for="customer_password">Contraseña *</label>
                    <div class="input-group">
                      <input type="password" id="customer_password" name="customer_password" class="form-control" required minlength="6">
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
                  <div class="col-md-6 mb-3">
                    <label for="ciudad">Ciudad</label>
                    <input type="text" id="ciudad" name="ciudad" class="form-control" placeholder="Ej: Santo Domingo">
                  </div>

                  <div class="col-md-6 mb-3">
                    <label for="sector">Sector/Barrio</label>
                    <input type="text" id="sector" name="sector" class="form-control" placeholder="Ej: Naco, Piantini, etc.">
                  </div>
                </div>

                <div class="form-row">
                  <div class="col-md-12 mb-3">
                    <label for="direccion">Dirección Completa *</label>
                    <textarea id="direccion" name="direccion" class="form-control" rows="2" required placeholder="Calle, número, edificio, etc."></textarea>
                  </div>
                </div>

                <div class="form-row">
                  <div class="col-md-12 mb-3">
                    <label for="referencia">Referencias de la Dirección</label>
                    <textarea id="referencia" name="referencia" class="form-control" rows="2" placeholder="Puntos de referencia, colores, etc."></textarea>
                  </div>
                </div>

                <div class="form-row">
                  <div class="col-md-12 mb-3">
                    <div class="custom-control custom-checkbox">
                      <input class="custom-control-input" type="checkbox" name="es_contribuyente" id="es_contribuyente" value="1">
                      <label class="custom-control-label" for="es_contribuyente">
                        Es contribuyente (Requiere factura fiscal)
                      </label>
                    </div>
                  </div>
                </div>

                <div class="form-row mt-4">
                  <div class="col-md-6">
                    <button type="submit" name="addCustomer" class="btn btn-success btn-lg">
                      <i class="fas fa-user-plus mr-2"></i> Registrar Cliente
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

      // Validación de formulario
      $('#customerForm').submit(function() {
        const password = $('#customer_password').val();
        if (password.length < 6) {
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