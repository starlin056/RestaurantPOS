<?php
session_start();
include('config/config.php');
include('config/code-generator.php');

if (!isset($_SESSION['pending_admin_id'])) {
    header("Location: index.php");
    exit();
}

$admin_id = $_SESSION['pending_admin_id'];
$now = new DateTime();

if (isset($_POST['activate'])) {
    $input_key = trim($_POST['activation_key']);

    $stmt = $mysqli->prepare("SELECT activation_key, activation_expiry FROM rpos_admin WHERE admin_id=? AND estado='Inactivo'");
    $stmt->bind_param("s", $admin_id);
    $stmt->execute();
    $stmt->bind_result($activation_key, $activation_expiry);
    $stmt->fetch();
    $stmt->close();

    if ($input_key === $activation_key && new DateTime($activation_expiry) >= $now) {
        // Activar usuario
        $update = $mysqli->prepare("UPDATE rpos_admin SET estado='Activo' WHERE admin_id=?");
        $update->bind_param("s", $admin_id);
        $update->execute();
        $update->close();

        $_SESSION['user_id'] = $admin_id;
        $_SESSION['rol'] = 'admin';
        unset($_SESSION['pending_admin_id']);
        header("Location: index.php?route=" . Security::generateSecureURL('dashboard'));
        exit();
    } else {
        $err = "Clave de activación incorrecta o expirada.";
    }
}
require_once('partials/_head.php');
?>

<body class="bg-dark">
<div class="main-content">
    <div class="header bg-gradient-primary py-7 text-center">
        <h1 class="text-white">Activar usuario administrador</h1>
    </div>

    <div class="container mt--8 pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="card bg-secondary shadow border-0">
                    <div class="card-body px-lg-5 py-lg-5">
                        <?php if(isset($err)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
                        <?php endif; ?>
                        <form method="post" novalidate>
                            <div class="form-group mb-3">
                                <div class="input-group input-group-alternative">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-key-25"></i></span>
                                    </div>
                                    <input class="form-control" type="text" name="activation_key" placeholder="Ingrese clave de activación" required>
                                </div>
                            </div>
                            <div class="text-center mt-4">
                                <button type="submit" name="activate" class="btn btn-primary">Activar usuario</button>
                            </div>
                        </form>
                        <div class="mt-3 text-center">
                            <a href="index.php" class="text-light"><small>Volver al login</small></a>
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
