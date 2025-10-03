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
check_login();;


$err = null; 
$success = null;

// Procesar formulario antes de enviar HTML
if (isset($_POST['add_mesa'])) {
    $numero_mesa = intval($_POST['numero_mesa']);
    $capacidad = intval($_POST['capacidad']);
    $ubicacion = $_POST['ubicacion'];
    $estado = $_POST['estado'];
    $mesa_id = uniqid();

    // Verificar si el número de mesa ya existe
    $check = "SELECT * FROM rpos_mesas WHERE numero_mesa = ?";
    $stmt = $mysqli->prepare($check);
    $stmt->bind_param('i', $numero_mesa);
    $stmt->execute();
    $res = $stmt->get_result();
    $stmt->close();

    if ($res->num_rows > 0) {
        $err = "El número de mesa ya existe";
    } else {
        $query = "INSERT INTO rpos_mesas (mesa_id, numero_mesa, capacidad, ubicacion, estado) VALUES (?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('siiss', $mesa_id, $numero_mesa, $capacidad, $ubicacion, $estado);
        $stmt->execute();
        $stmt->close();

        if ($stmt) {
            $_SESSION['success'] = "Mesa agregada correctamente";
            header("Location: ../mesas/mesas.php");
            exit();
        } else {
            $err = "Error al agregar mesa";
        }
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
            <?php if ($err): ?>
                <div class="alert alert-danger alert-dismissible fade show"><?php echo htmlspecialchars($err); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-12">
                    <div class="card shadow">
                        <div class="card-header">
                            <h5>Agregar Nueva Mesa</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="add_mesa.php">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="numero_mesa">Número de Mesa</label>
                                        <input type="number" class="form-control" name="numero_mesa" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="capacidad">Capacidad (personas)</label>
                                        <input type="number" class="form-control" name="capacidad" min="1" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="ubicacion">Ubicación</label>
                                    <select class="form-control" name="ubicacion" required>
                                        <option value="Interior">Interior</option>
                                        <option value="Terraza">Terraza</option>
                                        <option value="Barra">Barra</option>
                                        <option value="Sala VIP">Sala VIP</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="estado">Estado Inicial</label>
                                    <select class="form-control" name="estado" required>
                                        <option value="Disponible">Disponible</option>
                                        <option value="Ocupada">Ocupada</option>
                                        <option value="Reservada">Reservada</option>
                                        <option value="Mantenimiento">Mantenimiento</option>
                                    </select>
                                </div>
                               

                                <div class="form-group mt-3">
                                     <button type="submit" name="add_mesa" class="btn btn-primary">Guardar Mesa</button>
                                    <a href="../mesas/mesas.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Atrás
                                    </a>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Footer -->
            <?php require_once('../partials/_footer.php'); ?>
        </div>
    </div>

    <?php require_once('../partials/_scripts.php'); ?>
</body>

</html>