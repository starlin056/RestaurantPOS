<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ob_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/error.log');
error_reporting(E_ALL);
include('../config/config.php');
include('../config/checklogin.php');
check_login();

if (!isset($_GET['update'])) {
    $_SESSION['error'] = "Mesa no especificada";
    header("Location: ../mesas/mesas.php");
    exit;
}

$mesa_id = $_GET['update'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero_mesa = intval($_POST['numero_mesa']);
    $capacidad = intval($_POST['capacidad']);
    $ubicacion = $_POST['ubicacion'];
    $estado = $_POST['estado'];
    $num_personas = isset($_POST['num_personas']) ? intval($_POST['num_personas']) : null;
    $notas = $_POST['notas'];

    $query = "UPDATE rpos_mesas 
              SET numero_mesa = ?, capacidad = ?, ubicacion = ?, estado = ?, num_personas = ?, notas = ?
              WHERE mesa_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('iisssis', $numero_mesa, $capacidad, $ubicacion, $estado, $num_personas, $notas, $mesa_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['success'] = "Mesa actualizada correctamente";
    header("Location: ../mesas/mesas.php");
    exit;
}

// Obtener datos de la mesa
$query = "SELECT * FROM rpos_mesas WHERE mesa_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('s', $mesa_id);
$stmt->execute();
$mesa = $stmt->get_result()->fetch_object();
$stmt->close();

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
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fas fa-chair mr-2"></i>Actualizar Mesa #<?= $mesa->numero_mesa; ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Número de Mesa</label>
                                    <input type="number" name="numero_mesa" class="form-control" value="<?= $mesa->numero_mesa; ?>" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Capacidad (personas)</label>
                                    <input type="number" name="capacidad" class="form-control" value="<?= $mesa->capacidad; ?>" min="1" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Ubicación</label>
                                <select class="form-control" name="ubicacion" required>
                                    <option value="Interior" <?= $mesa->ubicacion=='Interior'?'selected':''; ?>>Interior</option>
                                    <option value="Terraza" <?= $mesa->ubicacion=='Terraza'?'selected':''; ?>>Terraza</option>
                                    <option value="Barra" <?= $mesa->ubicacion=='Barra'?'selected':''; ?>>Barra</option>
                                    <option value="Sala VIP" <?= $mesa->ubicacion=='Sala VIP'?'selected':''; ?>>Sala VIP</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Estado</label>
                                <select class="form-control" name="estado" required>
                                    <option value="Disponible" <?= $mesa->estado=='Disponible'?'selected':''; ?>>Disponible</option>
                                    <option value="Ocupada" <?= $mesa->estado=='Ocupada'?'selected':''; ?>>Ocupada</option>
                                    <option value="Reservada" <?= $mesa->estado=='Reservada'?'selected':''; ?>>Reservada</option>
                                    <option value="Mantenimiento" <?= $mesa->estado=='Mantenimiento'?'selected':''; ?>>Mantenimiento</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Número de Personas (si está ocupada)</label>
                                <input type="number" name="num_personas" class="form-control" value="<?= $mesa->num_personas; ?>">
                            </div>

                            <div class="form-group">
                                <label>Notas</label>
                                <textarea name="notas" class="form-control"><?= $mesa->notas; ?></textarea>
                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-2"></i>Actualizar Mesa</button>
                                <a href="mesas.php" class="btn btn-secondary"><i class="fas fa-arrow-left mr-2"></i>Atrás</a>
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
</body>
</html>
