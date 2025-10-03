<?php
session_start();
include('../config/config.php');
include('../config/checklogin.php');
include('../config/code-generator.php');

check_login();

// Definir roles disponibles según ENUM
$roles = ['Mesero','Cocinero','Bartender','Delivery','Cajero','Administrador'];
$estados = ['Activo', 'Inactivo'];

// Actualizar empleado
if (isset($_POST['UpdateStaff'])) {
    if (
        empty($_POST["staff_number"]) || empty($_POST["staff_name"]) ||
        empty($_POST['staff_email']) || empty($_POST['rol']) || empty($_POST['estado'])
    ) {
        $err = "Todos los campos obligatorios deben ser completados";
    } else {
        $staff_number = $_POST['staff_number'];
        $staff_name   = $_POST['staff_name'];
        $staff_email  = $_POST['staff_email'];
        $telefono     = $_POST['telefono'] ?? '';
        $direccion    = $_POST['direccion'] ?? '';
        $rol          = in_array($_POST['rol'], $roles) ? $_POST['rol'] : 'Mesero';
        $estado       = in_array($_POST['estado'], $estados) ? $_POST['estado'] : 'Activo';
        $update       = $_GET['update'];

        // Construir query dinámicamente si hay nueva contraseña
        $params = [$staff_number, $staff_name, $staff_email, $telefono, $direccion, $rol, $estado];
        $password_update = "";

        if (!empty($_POST['staff_password'])) {
            $staff_password = password_hash($_POST['staff_password'], PASSWORD_BCRYPT);
            $password_update = ", staff_password = ?";
            $params[] = $staff_password;
        }

        $params[] = $update; // WHERE staff_id

        $postQuery = "UPDATE rpos_staff SET 
                        staff_number = ?, staff_name = ?, staff_email = ?, 
                        telefono = ?, direccion = ?, rol = ?, estado = ? 
                        $password_update 
                      WHERE staff_id = ?";

        $postStmt = $mysqli->prepare($postQuery);

        // Bind parameters dinámicamente
        $types = str_repeat('s', count($params) - 1) . 'i';
        $postStmt->bind_param($types, ...$params);
        $postStmt->execute();

        if ($postStmt->affected_rows > 0) {
            $success = "Empleado actualizado correctamente";
            header("refresh:1; url=hrm.php");
        } else {
            $err = "Error al actualizar el empleado o no hubo cambios";
        }
        $postStmt->close();
    }
}

// Obtener información actual del empleado
$update = $_GET['update'] ?? 0;
$ret = "SELECT * FROM rpos_staff WHERE staff_id = ?";
$stmt = $mysqli->prepare($ret);
$stmt->bind_param('i', $update);
$stmt->execute();
$res = $stmt->get_result();
$staff = $res->fetch_object();

require_once('../partials/_head.php');
?>

<body>
<?php require_once('../partials/_sidebar.php'); ?>
<div class="main-content">
<?php require_once('../partials/_topnav.php'); ?>

<!-- Contenedor centrado -->
<div class="d-flex align-items-center justify-content-center min-vh-100">
    <div class="container">
        <?php if (!empty($staff)): ?>
        <div class="row justify-content-center">
            <div class="col-xl-8">
                <div class="card bg-secondary shadow-lg">
                    <div class="card-header bg-white border-0 text-center">
                        <h3 class="mb-0">Actualizar Información del Empleado</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success text-center"><?php echo htmlspecialchars($success); ?></div>
                        <?php elseif(!empty($err)): ?>
                            <div class="alert alert-danger text-center"><?php echo htmlspecialchars($err); ?></div>
                        <?php endif; ?>

                        <form method="POST" id="staffForm">
                            <div class="form-row">
                                <div class="col-md-6 mb-3">
                                    <label for="staff_number">Número de Empleado *</label>
                                    <input type="text" id="staff_number" name="staff_number" class="form-control" 
                                           value="<?php echo htmlspecialchars($staff->staff_number); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="staff_name">Nombre Completo *</label>
                                    <input type="text" id="staff_name" name="staff_name" class="form-control" 
                                           value="<?php echo htmlspecialchars($staff->staff_name); ?>" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-6 mb-3">
                                    <label for="staff_email">Correo Electrónico *</label>
                                    <input type="email" id="staff_email" name="staff_email" class="form-control" 
                                           value="<?php echo htmlspecialchars($staff->staff_email); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="telefono">Teléfono</label>
                                    <input type="tel" id="telefono" name="telefono" class="form-control" 
                                           value="<?php echo htmlspecialchars($staff->telefono ?? ''); ?>" 
                                           placeholder="809-555-5555">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-6 mb-3">
                                    <label for="rol">Rol *</label>
                                    <select id="rol" name="rol" class="form-control" required>
                                        <?php foreach($roles as $r): ?>
                                            <option value="<?php echo $r; ?>" 
                                                <?php echo $staff->rol == $r ? 'selected' : ''; ?>>
                                                <?php echo $r; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="estado">Estado *</label>
                                    <select id="estado" name="estado" class="form-control" required>
                                        <?php foreach($estados as $e): ?>
                                            <option value="<?php echo $e; ?>" 
                                                <?php echo $staff->estado == $e ? 'selected' : ''; ?>>
                                                <?php echo $e; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="direccion">Dirección</label>
                                <textarea id="direccion" name="direccion" class="form-control" rows="2"><?php echo htmlspecialchars($staff->direccion ?? ''); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="staff_password">Nueva Contraseña</label>
                                <input type="password" id="staff_password" name="staff_password" class="form-control" 
                                       placeholder="Dejar vacío para mantener la actual">
                                <small class="form-text text-muted">
                                    Mínimo 6 caracteres. Dejar vacío para no cambiar.
                                </small>
                            </div>

                            <div class="text-center">
                                <button type="submit" name="UpdateStaff" class="btn btn-success btn-lg">Actualizar Empleado</button>
                                <a href="hrm.php" class="btn btn-secondary btn-lg">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
            <div class="alert alert-danger text-center">Empleado no encontrado</div>
        <?php endif; ?>
    </div>
</div>

<?php require_once('../partials/_scripts.php'); ?>
<script>
$(document).ready(function() {
    $('#staffForm').submit(function() {
        const password = $('#staff_password').val();
        if (password && password.length < 6) {
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
</div>
<?php require_once('../partials/_footer.php'); ?>
</body>
</html>
