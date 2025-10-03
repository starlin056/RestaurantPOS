<?php
session_start();
include('config/config.php');
include('config/code-generator.php'); // Para generar claves automáticamente

// Solo admins pueden crear otros usuarios
//if ($_SESSION['rol'] !== 'admin') {
//  header("Location: index.php?route=" . Security::generateSecureURL('dashboard'));
//exit();
//}

if (isset($_POST['create_admin'])) {
    $admin_name = trim($_POST['admin_name']);
    $admin_email = trim($_POST['admin_email']);
    $admin_password = trim($_POST['admin_password']);

    if (empty($admin_name) || empty($admin_email) || empty($admin_password)) {
        $err = "Todos los campos son obligatorios";
    } else {
        // Verificar si el correo ya existe
        $stmt = $mysqli->prepare("SELECT admin_id FROM rpos_admin WHERE admin_email=?");
        $stmt->bind_param('s', $admin_email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $err = "Este correo ya está registrado";
        } else {
            // Generar admin_id y clave de activación
            $admin_id = bin2hex(random_bytes(6));
            $activation_key = substr(str_shuffle("QWERTYUIOPLKJHGFDSAZXCVBNM1234567890"), 1, 30);
            $activation_expiry = (new DateTime())->modify('+1 year +15 days')->format('Y-m-d H:i:s');
            $estado = 'Inactivo';

            // Usar password_hash para guardar contraseña segura
            $admin_password_hash = password_hash($admin_password, PASSWORD_DEFAULT);

            $stmt_insert = $mysqli->prepare("INSERT INTO rpos_admin 
                (admin_id, admin_name, admin_email, admin_password, activation_key, estado, activation_expiry)
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt_insert->bind_param('sssssss', $admin_id, $admin_name, $admin_email, $admin_password_hash, $activation_key, $estado, $activation_expiry);

            if ($stmt_insert->execute()) {
                $success = "Usuario creado correctamente. La clave de activación se enviará al usuario.";
            } else {
                $err = "Error al crear usuario, intente nuevamente";
            }
            $stmt_insert->close();
        }
        $stmt->close();
    }
}

require_once('partials/_head.php');
?>

<body class="bg-dark">
    <div class="main-content">
        <div class="header bg-gradient-primary py-7">
            <div class="container text-center mb-7">
                <div class="row justify-content-center">
                    <div class="col-lg-5 col-md-6">
                        <h1 class="text-white">Crear nuevo usuario administrador</h1>
                    </div>
                </div>
            </div>
        </div>

        <div class="container mt--8 pb-5">
            <div class="row justify-content-center">
                <div class="col-lg-5 col-md-7">
                    <div class="card bg-secondary shadow border-0">
                        <div class="card-body px-lg-5 py-lg-5">
                            <?php if (isset($err)): ?>
                                <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
                            <?php endif; ?>
                            <?php if (isset($success)): ?>
                                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                            <?php endif; ?>

                            <form method="post" novalidate>
                                <div class="form-group mb-3">
                                    <div class="input-group input-group-alternative">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="ni ni-single-02"></i></span>
                                        </div>
                                        <input class="form-control" type="text" name="admin_name" placeholder="Nombre completo" required>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <div class="input-group input-group-alternative">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="ni ni-email-83"></i></span>
                                        </div>
                                        <input class="form-control" type="email" name="admin_email" placeholder="Correo electrónico" required>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <div class="input-group input-group-alternative">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="ni ni-lock-circle-open"></i></span>
                                        </div>
                                        <input class="form-control" type="password" name="admin_password" placeholder="Contraseña" required minlength="6">
                                    </div>
                                    <small class="text-muted">Mínimo 6 caracteres</small>
                                </div>

                                <div class="text-center mt-4">
                                    <button type="submit" name="create_admin" class="btn btn-success">Crear usuario</button>
                                </div>
                            </form>

                            <div class="mt-3 text-center">
                                <a href="dashboard.php" class="btn btn-primary">
                                    Volver al panel
                                </a>
                            </div>


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

