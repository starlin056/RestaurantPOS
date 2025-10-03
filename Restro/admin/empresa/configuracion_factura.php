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

// Función para sanitizar entradas
function limpiar($valor) {
    return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
}

// Procesar formulario de configuración general
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['config_general'])) {
    $nombre     = limpiar($_POST['nombre_empresa']);
    $rnc        = limpiar($_POST['rnc']);
    $direccion  = limpiar($_POST['direccion']);
    $telefono   = limpiar($_POST['telefono']);
    $itebis     = floatval($_POST['itebis']);
    $servicio   = floatval($_POST['servicio']);

    // Manejar carga de logo
    $logo = limpiar($_POST['logo_actual']);
    if (!empty($_FILES['logo']['name'])) {
        // Ruta absoluta en el servidor
        $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/RestaurantPOS/Restro/admin/assets/img/';
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true); // Crear carpeta si no existe
        }

        $imageFileType = strtolower(pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $max_size = 2 * 1024 * 1024; // 2MB

        // Validar tipo de archivo
        if (!in_array($imageFileType, $allowed_types)) {
            $_SESSION['error'] = "Formato no permitido. Solo JPG, JPEG, PNG o GIF.";
        }
        // Validar tamaño
        elseif ($_FILES['logo']['size'] > $max_size) {
            $_SESSION['error'] = "El archivo es demasiado grande. Máximo 2MB.";
        }
        else {
            $check = getimagesize($_FILES["logo"]["tmp_name"]);
            if ($check !== false) {
                $unique_name = uniqid('logo_', true) . '.' . $imageFileType;
                $target_file = $target_dir . $unique_name;

                if (move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file)) {
                    // Guardar ruta web para mostrar en la página
                    $logo = '/RestaurantPOS/Restro/admin/assets/img/' . $unique_name;

                    // Borrar logo anterior si existe
                    if (!empty($_POST['logo_actual']) && $_POST['logo_actual'] != $logo) {
                        $old_logo_file = $_SERVER['DOCUMENT_ROOT'] . $_POST['logo_actual'];
                        if (file_exists($old_logo_file)) {
                            @unlink($old_logo_file);
                        }
                    }
                } else {
                    $_SESSION['error'] = "No se pudo subir el logo. Intenta nuevamente.";
                }
            } else {
                $_SESSION['error'] = "El archivo no es una imagen válida.";
            }
        }
    }

    // Actualizar configuración
    $query = "UPDATE rpos_configuracion SET 
              nombre_empresa = ?, rnc = ?, direccion = ?, telefono = ?, 
              logo = ?, itebis_porcentaje = ?, servicio_porcentaje = ?
              WHERE config_id = 1";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('sssssdd', $nombre, $rnc, $direccion, $telefono, $logo, $itebis, $servicio);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Configuración general actualizada correctamente";
    } else {
        $_SESSION['error'] = "Error al actualizar la configuración general";
    }
    $stmt->close();
}

// Procesar formulario de comprobantes fiscales
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['config_comprobantes'])) {
    $comprobantes = $_POST['comprobantes'];
    
    foreach ($comprobantes as $id => $data) {
        $secuencial_actual = intval($data['actual']);
        $secuencial_final = intval($data['final']);
        $estado = limpiar($data['estado']);
        
        if ($secuencial_actual > $secuencial_final) {
            $_SESSION['error'] = "El secuencial actual no puede ser mayor al secuencial final";
            break;
        }
        
        $query = "UPDATE rpos_secuenciales_comprobantes 
                 SET secuencial_actual = ?, secuencial_final = ?, estado = ?
                 WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('iisi', $secuencial_actual, $secuencial_final, $estado, $id);
        
        if (!$stmt->execute()) {
            $_SESSION['error'] = "Error al actualizar los comprobantes fiscales";
            break;
        }
        $stmt->close();
    }
    
    if (!isset($_SESSION['error'])) {
        $_SESSION['success'] = "Configuración de comprobantes actualizada correctamente";
    }
    
    header("Location: configuracion_factura.php");
    exit;
}

// Obtener configuración actual
$query = "SELECT * FROM rpos_configuracion WHERE config_id = 1";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$config = $stmt->get_result()->fetch_object();
$stmt->close();

// Obtener comprobantes
$query = "SELECT * FROM rpos_secuenciales_comprobantes ORDER BY tipo_comprobante";
$comprobantes_result = $mysqli->query($query);

require_once('../partials/_head.php');
?>


<body>
<?php require_once('../partials/_sidebar.php'); ?>
<div class="main-content">
<?php require_once('../partials/_topnav.php'); ?>

<div class="header bg-gradient-primary pb-8 pt-5 pt-md-8">
  <div class="container-fluid">
    <div class="header-body">
      <div class="row align-items-center py-4">
        <div class="col-lg-6 col-7">
          <h1 class="text-white display-4">⚙️ Configuración de Facturación y Empresa</h1>
          <p class="text-white mb-0">Gestión de impuestos y comprobantes fiscales</p>
          <p class="text-white mb-0">Gestión de datos de la empresa </p>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container-fluid mt--7">
  <!-- Alertas -->
  <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <span class="alert-icon"><i class="ni ni-support-16"></i></span>
      <span class="alert-text"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  <?php endif; ?>

  <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <span class="alert-icon"><i class="ni ni-like-2"></i></span>
      <span class="alert-text"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  <?php endif; ?>

  <div class="row">
    <!-- Configuración General -->
    <div class="col-md-6">
      <div class="card shadow">
        <div class="card-header border-0">
          <h3 class="mb-0">📋 Configuración General</h3>
        </div>
        <div class="card-body">
          <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="config_general" value="1">

            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="nombre_empresa">Nombre de la Empresa *</label>
                <input type="text" class="form-control" name="nombre_empresa" required 
                       value="<?php echo htmlspecialchars($config->nombre_empresa ?? ''); ?>">
              </div>
              <div class="form-group col-md-6">
                <label for="rnc">RNC *</label>
                <input type="text" class="form-control" name="rnc" required 
                       value="<?php echo htmlspecialchars($config->rnc ?? ''); ?>">
              </div>
            </div>

            <div class="form-group">
              <label for="direccion">Dirección *</label>
              <input type="text" class="form-control" name="direccion" required 
                     value="<?php echo htmlspecialchars($config->direccion ?? ''); ?>">
            </div>

            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="telefono">Teléfono *</label>
                <input type="text" class="form-control" name="telefono" required 
                       value="<?php echo htmlspecialchars($config->telefono ?? ''); ?>">
              </div>
              <div class="form-group col-md-3">
                <label for="itebis">% ITEBIS *</label>
                <input type="number" step="0.01" class="form-control" name="itebis" required 
                       value="<?php echo $config->itebis_porcentaje ?? 18.00; ?>">
              </div>
              <div class="form-group col-md-3">
                <label for="servicio">% Servicio *</label>
                <input type="number" step="0.01" class="form-control" name="servicio" required 
                       value="<?php echo $config->servicio_porcentaje ?? 10.00; ?>">
              </div>
            </div>

            <div class="form-group">
              <label for="logo">Logo de la Empresa</label>
              <?php if (!empty($config->logo)): ?>
                <div class="mb-3">
                  <img src="<?php echo htmlspecialchars($config->logo); ?>" alt="Logo actual" style="max-height: 80px; max-width: 200px;" class="img-thumbnail">
                  <input type="hidden" name="logo_actual" value="<?php echo htmlspecialchars($config->logo); ?>">
                </div>
              <?php endif; ?>
              <input type="file" class="form-control-file" name="logo" accept="image/*">
              <small class="form-text text-muted">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 2MB</small>
            </div>

            <div class="text-center">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar Configuración General
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Configuración de Comprobantes Fiscales -->
    <div class="col-md-6">
      <div class="card shadow">
        <div class="card-header border-0">
          <h3 class="mb-0">🧾 Comprobantes Fiscales</h3>
        </div>
        <div class="card-body">
          <form method="post">
            <input type="hidden" name="config_comprobantes" value="1">

            <div class="table-responsive">
              <table class="table table-bordered table-sm">
                <thead class="thead-light">
                  <tr>
                    <th>Tipo</th>
                    <th>Prefijo</th>
                    <th>Sec. Actual</th>
                    <th>Sec. Final</th>
                    <th>Disponibles</th>
                    <th>Estado</th>
                    <th>Descripción</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while($comp = $comprobantes_result->fetch_object()): 
                    $disponibles = $comp->secuencial_final - $comp->secuencial_actual;
                    $porcentaje = $comp->secuencial_final > 0 ? ($disponibles / $comp->secuencial_final) * 100 : 0;

                    $clase_fila = '';
                    if ($disponibles <= 0) {
                        $clase_fila = 'table-danger';
                    } elseif ($disponibles < 50) {
                        $clase_fila = 'table-warning';
                    } elseif ($disponibles < 100) {
                        $clase_fila = 'table-info';
                    }
                  ?>
                  <tr class="<?php echo $clase_fila; ?>">
                    <td><strong><?php echo $comp->tipo_comprobante; ?></strong></td>
                    <td><?php echo $comp->prefijo; ?></td>
                    <td>
                      <input type="number" name="comprobantes[<?php echo $comp->id; ?>][actual]" 
                             value="<?php echo $comp->secuencial_actual; ?>" class="form-control form-control-sm" min="0" required>
                    </td>
                    <td>
                      <input type="number" name="comprobantes[<?php echo $comp->id; ?>][final]" 
                             value="<?php echo $comp->secuencial_final; ?>" class="form-control form-control-sm" min="1" required>
                    </td>
                    <td>
                      <div class="progress-wrapper">
                        <div class="progress-info">
                          <div class="progress-label">
                            <span><?php echo $disponibles; ?></span>
                          </div>
                          <div class="progress-percentage">
                            <span><?php echo round($porcentaje, 1); ?>%</span>
                          </div>
                        </div>
                        <div class="progress" style="height: 8px;">
                          <div class="progress-bar 
                            <?php echo $porcentaje > 20 ? 'bg-success' : ($porcentaje > 5 ? 'bg-warning' : 'bg-danger'); ?>" 
                            role="progressbar" style="width: <?php echo $porcentaje; ?>%;" 
                            aria-valuenow="<?php echo $porcentaje; ?>" aria-valuemin="0" aria-valuemax="100">
                          </div>
                        </div>
                      </div>
                    </td>
                    <td>
                      <select name="comprobantes[<?php echo $comp->id; ?>][estado]" class="form-control form-control-sm">
                        <option value="Activo" <?php echo $comp->estado == 'Activo' ? 'selected' : ''; ?>>Activo</option>
                        <option value="Inactivo" <?php echo $comp->estado == 'Inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                        <option value="Agotado" <?php echo $comp->estado == 'Agotado' ? 'selected' : ''; ?>>Agotado</option>
                      </select>
                    </td>
                    <td><small><?php echo $comp->descripcion; ?></small></td>
                  </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>

            <div class="text-center mt-3">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar Comprobantes
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once('../partials/_scripts.php'); ?>
<?php require_once('../partials/_footer.php'); ?>

<style>
.progress-wrapper { margin: 5px 0; }
.progress-info { display: flex; justify-content: space-between; margin-bottom: 2px; }
.progress-label, .progress-percentage { font-size: 0.7rem; font-weight: 600; }
.table th { font-size: 0.8rem; padding: 8px; }
.table td { font-size: 0.8rem; padding: 8px; vertical-align: middle; }
.form-control-sm { height: calc(1.5em + 0.5rem + 2px); padding: 0.25rem 0.5rem; font-size: 0.8rem; }
</style>

<script>
$(document).ready(function() {
    $('input[name$="[actual]"]').on('change', function() {
        var actual = $(this);
        var final = actual.closest('tr').find('input[name$="[final]"]');
        if (parseInt(actual.val()) > parseInt(final.val())) {
            alert('El secuencial actual no puede ser mayor al secuencial final');
            actual.val(final.val());
        }
    });

    $('input[name$="[final]"]').on('change', function() {
        var final = $(this);
        var actual = final.closest('tr').find('input[name$="[actual]"]');
        if (parseInt(final.val()) < parseInt(actual.val())) {
            alert('El secuencial final no puede ser menor al secuencial actual');
            final.val(actual.val());
        }
    });

    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);
});
</script>
</body>
</html>
