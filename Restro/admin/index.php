<?php
session_start();
include('config/config.php');
include('config/code-generator.php');

// Inicio de sesión
if (isset($_POST['login'])) {
    $email = trim($_POST['admin_email']);
    $input_password = trim($_POST['admin_password']);
    $err = '';

    $now = new DateTime();

    // Primero buscar en admins
    $stmt = $mysqli->prepare("SELECT admin_id, admin_password, estado, activation_key, activation_expiry FROM rpos_admin WHERE admin_email=?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $hashed_password, $estado, $activation_key, $activation_expiry);
        $stmt->fetch();
        $stmt->close();

        if (password_verify($input_password, $hashed_password)) {
            if ($estado === 'Inactivo') {
                $_SESSION['pending_admin_id'] = $user_id;
                header("Location: activate_admin.php");
                exit();
            }

            if ($activation_expiry && new DateTime($activation_expiry) < $now) {
                $err = "La clave de activación ha expirado. Contacte al administrador principal.";
            } else {
                $_SESSION['user_id'] = $user_id;
                $_SESSION['rol'] = 'admin';
                header("Location: dashboard.php");
                exit();
            }
        } else {
            $err = "Contraseña incorrecta";
        }
    } else {
        // Buscar en staff (mesero, cajero) - CORREGIDO: usando password_verify
        $stmt = $mysqli->prepare("SELECT staff_id, staff_password, rol, estado FROM rpos_staff WHERE staff_email=?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($user_id, $hashed_password, $role, $estado);
            $stmt->fetch();
            $stmt->close();

            // CORRECCIÓN: Usar password_verify para empleados también
            if (password_verify($input_password, $hashed_password)) {
                if ($estado === 'Inactivo') {
                    $err = "Su cuenta está inactiva. Contacte al administrador.";
                } else {
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['rol'] = $role;
                    $_SESSION['staff_email'] = $email;
                    $_SESSION['staff_id'] = $user_id;
                    header("Location: dashboard.php");
                    exit();
                }
            } else {
                $err = "Contraseña incorrecta para empleado";
            }
        } else {
            $err = "Usuario no encontrado";
        }
    }
}

require_once('partials/_head.php');
?>

<body class="bg-dark">
  <div class="main-content">
    <div class="header bg-gradient-primary py-7">
      <div class="container">
        <div class="header-body text-center mb-7">
          <div class="row justify-content-center">
            <div class="col-lg-5 col-md-6">
              <h1 class="text-white">Sistema de Punto de Venta para Restaurantes</h1>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Contenido de la página -->
    <div class="container mt--8 pb-5">
      <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
          <div class="card bg-secondary shadow border-0">
            <div class="card-body px-lg-5 py-lg-5">
              <?php if (isset($err)) : ?>
                <div class="alert alert-danger" role="alert">
                  <?php echo htmlspecialchars($err); ?>
                </div>
              <?php endif; ?>
              <form method="post" role="form" novalidate>
                <div class="form-group mb-3">
                  <div class="input-group input-group-alternative">
                    <div class="input-group-prepend">
                      <span class="input-group-text"><i class="ni ni-email-83"></i></span>
                    </div>
                    <input class="form-control" required name="admin_email" placeholder="Correo electrónico" type="email" autocomplete="username">
                  </div>
                </div>
                <div class="form-group">
                  <div class="input-group input-group-alternative">
                    <div class="input-group-prepend">
                      <span class="input-group-text"><i class="ni ni-lock-circle-open"></i></span>
                    </div>
                    <input class="form-control" required name="admin_password" placeholder="Contraseña" type="password" autocomplete="current-password">
                  </div>
                </div>
                <div class="custom-control custom-control-alternative custom-checkbox">
                  <input class="custom-control-input" id="customCheckLogin" type="checkbox" name="remember_me">
                  <label class="custom-control-label" for="customCheckLogin">
                    <span class="text-muted">Recordarme</span>
                  </label>
                </div>
                <div class="text-center">
                  <button type="submit" name="login" class="btn btn-primary my-4">Iniciar sesión</button>
                </div>
              </form>
            </div>
          </div>
<!-- crear usuario y olvido de contraseña -->
          <div class="row mt-3">
            <div class="col-6">
              <a href="forgot_pwd.php" class="text-light"><small>¿Olvidó su contraseña?</small></a>
            </div>
            <div class="col-6 text-right">
              <a href="create_admin.php" class="text-light"><small>Crear nuevo usuario</small></a>
            </div>
          </div>


        </div>
      </div>
    </div>
  </div>

  <?php require_once('partials/_footer.php'); ?>
  <?php require_once('partials/_scripts.php'); ?>
</body>

</html>