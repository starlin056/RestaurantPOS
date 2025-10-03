<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

$err = null;
$success = null;

$user_id = $_SESSION['user_id'];
$rol = $_SESSION['rol']; // admin o staff

// =====================
// Obtener datos del usuario según rol
// =====================
if ($rol === "admin") {
  $stmt = $mysqli->prepare("SELECT admin_id, admin_name, admin_email, activation_key, activation_expiry, admin_password FROM rpos_admin WHERE admin_id=?");
} else {
  $stmt = $mysqli->prepare("SELECT staff_id, staff_name, staff_email FROM rpos_staff WHERE staff_id=?");
}
$stmt->bind_param("s", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_object();
$stmt->close();

// Variables para HTML
if ($rol === "admin") {
  $user_name = $user->admin_name ?? '';
  $user_email = $user->admin_email ?? '';
  $activation_key = $user->activation_key ?? '';
  $activation_expiry = $user->activation_expiry ?? '';
  $user_password_hash = $user->admin_password ?? '';
} else {
  $user_name = $user->staff_name ?? '';
  $user_email = $user->staff_email ?? '';
  $activation_key = '';
  $activation_expiry = '';
}

// =====================
// Calcular días restantes si es admin
// =====================
$dias_restantes = null;
if ($rol === "admin" && !empty($activation_expiry)) {
  $hoy = new DateTime();
  $expira = new DateTime($activation_expiry);
  $diff = $hoy->diff($expira);
  $dias_restantes = $diff->invert ? 0 : $diff->days;
}

// =====================
// Actualizar perfil
// =====================
if (isset($_POST['ChangeProfile'])) {
  $new_name = $_POST['user_name'];
  $new_email = $_POST['user_email'];

  if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
    $err = "Correo electrónico inválido";
  } else {
    if ($rol === "admin") {
      $stmt = $mysqli->prepare("UPDATE rpos_admin SET admin_name=?, admin_email=? WHERE admin_id=?");
    } else {
      $stmt = $mysqli->prepare("UPDATE rpos_staff SET staff_name=?, staff_email=? WHERE staff_id=?");
    }
    $stmt->bind_param("sss", $new_name, $new_email, $user_id);
    if ($stmt->execute()) {
      $success = "Perfil actualizado correctamente";
      $user_name = $new_name;
      $user_email = $new_email;
    } else {
      $err = "Error al actualizar perfil: " . $stmt->error;
    }
    $stmt->close();
  }
}

// =====================
// Cambiar contraseña (solo admin)
// =====================
if ($rol === "admin" && isset($_POST['changePassword'])) {
  $error = 0;
  if (empty($_POST['old_password'])) {
    $error = 1;
    $err = "La contraseña actual no puede estar vacía";
  }
  if (empty($_POST['new_password'])) {
    $error = 1;
    $err = "La nueva contraseña no puede estar vacía";
  } elseif (strlen($_POST['new_password']) < 8) {
    $error = 1;
    $err = "La nueva contraseña debe tener al menos 8 caracteres";
  }
  if (empty($_POST['confirm_password'])) {
    $error = 1;
    $err = "Debe confirmar la nueva contraseña";
  }

  if (!$error) {
    $old_password_input = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $password_is_correct = false;
    if (password_verify($old_password_input, $user_password_hash)) {
      $password_is_correct = true;
    } elseif (sha1(md5($old_password_input)) === $user_password_hash) {
      $password_is_correct = true;
      // Migrar hash antiguo
      $new_hash = password_hash($old_password_input, PASSWORD_DEFAULT);
      $upd = $mysqli->prepare("UPDATE rpos_admin SET admin_password=? WHERE admin_id=?");
      $upd->bind_param("ss", $new_hash, $user_id);
      $upd->execute();
      $upd->close();
    }

    if (!$password_is_correct) {
      $err = "La contraseña actual es incorrecta";
    } elseif ($new_password !== $confirm_password) {
      $err = "Las contraseñas nuevas no coinciden";
    } else {
      $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
      $upd = $mysqli->prepare("UPDATE rpos_admin SET admin_password=? WHERE admin_id=?");
      $upd->bind_param("ss", $new_password_hash, $user_id);
      $upd->execute();
      if ($upd->affected_rows > 0) {
        $success = "Contraseña cambiada exitosamente";
      } else {
        $err = "No se pudo cambiar la contraseña, intente nuevamente";
      }
      $upd->close();
    }
  }
}

// =====================
// Cambiar código de activación (solo admin)
// =====================
if ($rol === "admin" && isset($_POST['changeActivation'])) {
  $nuevo_codigo = trim($_POST['activation_key']);
  $dias_extra = 30;

  if (!empty($nuevo_codigo)) {
    $nueva_fecha = date('Y-m-d H:i:s', strtotime("+$dias_extra days"));
    $stmt = $mysqli->prepare("UPDATE rpos_admin SET activation_key=?, activation_expiry=? WHERE admin_id=?");
    $stmt->bind_param("sss", $nuevo_codigo, $nueva_fecha, $user_id);
    if ($stmt->execute()) {
      $success = "Código activado. Expira el $nueva_fecha";
      $activation_key = $nuevo_codigo;
      $activation_expiry = $nueva_fecha;
      $dias_restantes = $dias_extra;
    } else {
      $err = "Error al actualizar activación";
    }
    $stmt->close();
  } else {
    $err = "Debe ingresar un código válido";
  }
}

// =====================
// Incluir partials y mostrar HTML
// =====================
require_once('partials/_head.php');
?>

<body>
  <?php require_once('partials/_sidebar.php'); ?>
  <div class="main-content">
    <?php require_once('partials/_topnav.php'); ?>

    <div class="header pb-8 pt-5 pt-lg-8 d-flex align-items-center"
      style="min-height: 400px; background-image: url(assets/img/theme/restro00.jpg); background-size: cover;">
      <span class="mask bg-gradient-default opacity-8"></span>
      <div class="container-fluid d-flex align-items-center">
        <div class="row">
          <div class="col-lg-7 col-md-10">
            <h1 class="display-4 text-white">Hola <?= htmlspecialchars($user_name); ?></h1>
            <p class="text-white mt-0 mb-5">Actualiza tu información personal<?= $rol === "admin" ? ", contraseña o código de activación" : ""; ?>.</p>
            <?php if ($err !== null) : ?>
              <div class="alert alert-danger"><?= htmlspecialchars($err); ?></div>
            <?php endif; ?>

            <?php if ($success !== null) : ?>
              <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
            <?php endif; ?>

          </div>
        </div>
      </div>
    </div>

    <div class="container-fluid mt--6">
      <div class="row">
        <!-- Tarjeta perfil flotante -->
        <div class="col-xl-4 order-xl-2 mb-4">
          <div class="card card-profile shadow hover-shadow" <?= $rol === "admin" && $dias_restantes !== null ? "data-toggle='tooltip' data-placement='top' title='Días restantes: $dias_restantes'" : ""; ?>>
            <div class="card-profile-image mt-4">
              <img src="assets/img/theme/user-a-min.png" class="rounded-circle" alt="Avatar">
            </div>
            <div class="card-body pt-0 pt-md-4 text-center">
              <h3 class="mb-3"><?= htmlspecialchars($user_name); ?></h3>
              <div class="h5 font-weight-300 mb-2">
                <i class="ni ni-email-83 mr-2"></i><?= htmlspecialchars($user_email); ?>
              </div>
              <hr class="my-2">
              <p class="text-muted"><?= $rol === "admin" ? "Administrador del sistema" : "Staff del sistema"; ?></p>
              <?php if ($rol === "admin"): ?>
                <?php if ($dias_restantes !== null): ?>
                  <p class="text-muted">Expira el: <?= $activation_expiry; ?></p>
                <?php else: ?>
                  <p class="text-danger">No tienes activación activa</p>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Formulario cuenta -->
        <div class="col-xl-8 order-xl-1">
          <div class="card shadow hover-shadow">
            <div class="card-header bg-white border-0">
              <h3 class="mb-0">Mi cuenta</h3>
            </div>
            <div class="card-body">
              <!-- Perfil -->
              <form method="post">
                <h6 class="heading-small text-muted mb-4">Información personal</h6>
                <div class="pl-lg-4">
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Nombre de usuario</label>
                        <input type="text" name="user_name" value="<?= htmlspecialchars($user_name); ?>" class="form-control" required>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Correo electrónico</label>
                        <input type="email" name="user_email" value="<?= htmlspecialchars($user_email); ?>" class="form-control" required>
                      </div>
                    </div>
                  </div>
                  <button type="submit" name="ChangeProfile" class="btn btn-primary"><i class="fas fa-save mr-2"></i>Guardar cambios</button>
                </div>
              </form>

              <?php if ($rol === "admin"): ?>
                <hr class="my-4">
                <!-- Contraseña -->
                <form method="post">
                  <h6 class="heading-small text-muted mb-4">Cambiar contraseña</h6>
                  <div class="pl-lg-4">
                    <div class="row">
                      <div class="col-md-12">
                        <div class="form-group">
                          <label>Contraseña actual</label>
                          <div class="input-group">
                            <input type="password" name="old_password" class="form-control" required>
                            <div class="input-group-append">
                              <button class="btn btn-outline-secondary toggle-password" type="button">
                                <i class="fas fa-eye"></i>
                              </button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-group">
                          <label>Nueva contraseña</label>
                          <input type="password" name="new_password" class="form-control" minlength="8" required>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group">
                          <label>Confirmar nueva contraseña</label>
                          <input type="password" name="confirm_password" class="form-control" minlength="8" required>
                        </div>
                      </div>
                    </div>
                    <button type="submit" name="changePassword" class="btn btn-warning"><i class="fas fa-key mr-2"></i>Cambiar contraseña</button>
                  </div>
                </form>

                <hr class="my-4">
                <!-- Activación -->
                <form method="post">
                  <h6 class="heading-small text-muted mb-4">Actualizar código de activación</h6>
                  <div class="pl-lg-4">
                    <div class="row">
                      <div class="col-md-12">
                        <input type="text" name="activation_key" placeholder="Nuevo código de activación" class="form-control" required>
                      </div>
                    </div>
                    <div class="row mt-2">
                      <div class="col-md-12">
                        <button type="submit" name="changeActivation" class="btn btn-success"><i class="fas fa-bolt mr-2"></i>Actualizar activación</button>
                      </div>
                    </div>
                  </div>
                </form>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <?php require_once('partials/_footer.php'); ?>
    </div>

    <?php require_once('partials/_scripts.php'); ?>
    <script>
      $(document).ready(function() {
        $('.toggle-password').click(function() {
          const input = $(this).closest('.input-group').find('input');
          const icon = $(this).find('i');
          if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
          } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
          }
        });
        $('[data-toggle="tooltip"]').tooltip({
          animation: true
        });
      });
    </script>
    <style>
      .hover-shadow {
        transition: all 0.3s ease-in-out;
      }

      .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 20px rgba(0, 0, 0, 0.3);
      }
    </style>
</body>

</html>