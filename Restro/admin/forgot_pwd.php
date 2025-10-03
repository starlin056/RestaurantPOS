<?php
session_start();
include('config/config.php');
require_once('config/code-generator.php');

// Procesar solicitud de restablecimiento
if (isset($_POST['reset_pwd'])) {
    $reset_email = trim($_POST['reset_email']);
    
    // Validar formato de email
    if (!filter_var($reset_email, FILTER_VALIDATE_EMAIL)) {
        $err = 'Por favor ingrese un correo electrónico válido';
    } else {
        // Verificar si el email existe en la base de datos
        $query = "SELECT admin_email FROM rpos_admin WHERE admin_email = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('s', $reset_email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            // Generar token y código de restablecimiento
            $reset_code = $_POST['reset_code'];
            $reset_token = sha1(md5($_POST['reset_token']));
            $reset_status = 'Pending';
            
            // Insertar solicitud de restablecimiento
            $insertQuery = "INSERT INTO rpos_pass_resets (reset_email, reset_code, reset_token, reset_status) VALUES (?,?,?,?)";
            $reset = $mysqli->prepare($insertQuery);
            $reset->bind_param('ssss', $reset_email, $reset_code, $reset_token, $reset_status);
            $reset->execute();
            
            if ($reset->affected_rows > 0) {
                // Aquí deberías implementar el envío del correo electrónico
                $success = "Se han enviado las instrucciones para restablecer tu contraseña a tu correo electrónico";
            } else {
                $err = "Ocurrió un error, por favor intenta nuevamente";
            }
        } else {
            $err = "No existe una cuenta con ese correo electrónico";
        }
        $stmt->close();
    }
}

require_once('partials/_head.php');
?>

<body class="bg-dark">
  <div class="main-content">
    <!-- Encabezado -->
    <div class="header bg-gradient-primary py-7">
      <div class="container">
        <div class="header-body text-center mb-7">
          <div class="row justify-content-center">
            <div class="col-lg-5 col-md-6">
              <h1 class="text-white">Sistema POS para Restaurantes</h1>
              <p class="text-lead text-light">Restablecimiento de contraseña</p>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Contenido principal -->
    <div class="container mt--8 pb-5">
      <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
          <!-- Tarjeta del formulario -->
          <div class="card shadow">
            <div class="card-header bg-transparent pb-5">
              <div class="text-center">
                <h2 class="text-muted">¿Olvidaste tu contraseña?</h2>
                <small class="text-muted">Ingresa tu correo electrónico para recibir instrucciones</small>
              </div>
            </div>
            
            <div class="card-body px-lg-5 py-lg-5">
              <!-- Mostrar mensajes de error/éxito -->
              <?php if(isset($err)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                  <span class="alert-inner--icon"><i class="fas fa-exclamation-circle"></i></span>
                  <span class="alert-inner--text"><?php echo $err; ?></span>
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
              <?php endif; ?>
              
              <?php if(isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                  <span class="alert-inner--icon"><i class="fas fa-check-circle"></i></span>
                  <span class="alert-inner--text"><?php echo $success; ?></span>
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
              <?php endif; ?>
              
              <!-- Formulario -->
              <form method="post" role="form">
                <div class="form-group mb-4">
                  <div class="input-group input-group-alternative">
                    <div class="input-group-prepend">
                      <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    </div>
                    <input class="form-control" name="reset_email" placeholder="Correo electrónico" type="email" required>
                  </div>
                </div>
                
                <!-- Campos ocultos para seguridad -->
                <div style="display:none">
                  <input type="hidden" name="reset_token" value="<?php echo bin2hex(random_bytes(16)); ?>">
                  <input type="hidden" name="reset_code" value="<?php echo substr(md5(uniqid(rand(), true)), 0, 8); ?>">
                  <input type="hidden" name="reset_status" value="Pending">
                </div>
                
                <div class="text-center">
                  <button type="submit" name="reset_pwd" class="btn btn-primary btn-block mt-4">
                    <i class="fas fa-key mr-2"></i> Restablecer contraseña
                  </button>
                </div>
              </form>
            </div>
          </div>
          
          <div class="row mt-3">
            <div class="col-12 text-center">
              <a href="index.php" class="text-light">
                <small><i class="fas fa-arrow-left mr-1"></i> Volver al inicio de sesión</small>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Footer -->
  <?php require_once('partials/_footer.php'); ?>
  
  <!-- Scripts -->
  <?php require_once('partials/_scripts.php'); ?>
</body>
</html>