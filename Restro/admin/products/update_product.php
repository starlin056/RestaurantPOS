<?php
session_start();
include('../config/config.php');
include('../config/checklogin.php');
check_login();



// Inicializar $prod para evitar errores de variable no definida
$prod = null;
$prod_id = null;

if (isset($_GET['update'])) {
  $prod_id = $_GET['update'];
  $query = "SELECT * FROM rpos_products WHERE prod_id = ?";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param('s', $prod_id);
  $stmt->execute();
  $res = $stmt->get_result();
  $prod = $res->fetch_object();
  $stmt->close();
}

require_once('../partials/_head.php');
?>

<body>
  <!-- Sidenav -->
  <?php require_once('../partials/_sidebar.php'); ?>
  <!-- Main content -->
  <div class="main-content">
    <!-- Top navbar -->
    <?php require_once('../partials/_topnav.php'); ?>
    <!-- Header -->
    <div style="background-image: url(../assets/img/theme/restro00.jpg); background-size: cover;" class="header pb-8 pt-5 pt-md-8">
      <span class="mask bg-gradient-dark opacity-8"></span>
      <div class="container-fluid">
        <div class="header-body"></div>
      </div>
    </div>
    <!-- Contenido de la página -->
    <div class="container-fluid mt--8">
      <?php if ($prod): ?>
        <div class="row">
          <div class="col-md-12">
            <div class="card shadow">
              <div class="card-header">
                <h5>Editar Producto: <?php echo htmlspecialchars($prod->prod_name); ?></h5>
              </div>
              <div class="card-body">
                <form method="post" action="update_product.php?update=<?php echo $prod_id; ?>" enctype="multipart/form-data">
                  <div class="form-row">
                    <div class="form-group col-md-6">
                      <label for="prod_name">Nombre del Producto</label>
                      <input type="text" class="form-control" name="prod_name" value="<?php echo htmlspecialchars($prod->prod_name); ?>" required>
                    </div>
                    <div class="form-group col-md-6">
                      <label for="prod_code">Código del Producto</label>
                      <input type="text" class="form-control" name="prod_code" value="<?php echo htmlspecialchars($prod->prod_code); ?>" required>
                    </div>
                  </div>
                  <div class="form-row">
                    <div class="form-group col-md-6">
                      <label for="prod_price">Precio</label>
                      <input type="number" class="form-control" name="prod_price" step="0.01" min="0" value="<?php echo htmlspecialchars($prod->prod_price); ?>" required>
                    </div>
                    <div class="form-group col-md-6">
                      <label for="categoria_id">Categoría</label>
                      <select class="form-control" name="categoria_id" required>
                        <option value="">Seleccione una categoría</option>
                        <?php
                        $cat_query = "SELECT * FROM rpos_categorias_productos ORDER BY nombre_categoria ASC";
                        $cat_result = $mysqli->query($cat_query);
                        while ($cat = $cat_result->fetch_object()):
                        ?>
                          <option value="<?php echo $cat->categoria_id; ?>" <?php echo ($prod->categoria_id == $cat->categoria_id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat->nombre_categoria); ?>
                          </option>
                        <?php endwhile; ?>
                      </select>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="prod_desc">Descripción</label>
                    <textarea class="form-control" name="prod_desc" rows="3"><?php echo htmlspecialchars($prod->prod_desc); ?></textarea>
                  </div>
                  <div class="form-group">
                    <label for="prod_img">Imagen del Producto</label>
                    <?php if ($prod->prod_img): ?>
                      <div class="mb-2">
                        <img src="../assets/img/products/<?php echo htmlspecialchars($prod->prod_img); ?>" height="100" class="img-thumbnail">
                        <div class="form-check mt-2">
                          <input class="form-check-input" type="checkbox" name="remove_img" id="remove_img">
                          <label class="form-check-label" for="remove_img">
                            Eliminar imagen actual
                          </label>
                        </div>
                      </div>
                    <?php endif; ?>
                    <input type="file" class="form-control-file" name="prod_img" accept="image/*">
                  </div>

                  <button type="submit" name="update_product" class="btn btn-primary">Actualizar Producto</button>
                  <a href="products.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                  </a>
                </form>
              </div>
            </div>
          </div>
        </div>
      <?php else: ?>
        <div class="alert alert-danger">
          No se encontró el producto solicitado.
        </div>
      <?php endif; ?>
      <!-- Pie de página -->
      <?php require_once('../partials/_footer.php'); ?>
    </div>
  </div>
  <!-- Scripts -->
  <?php require_once('../partials/_scripts.php'); ?>
</body>

</html>

<?php
if (isset($_POST['update_product']) && isset($prod_id)) {
  $prod_name = $_POST['prod_name'];
  $prod_code = $_POST['prod_code'];
  $prod_price = floatval($_POST['prod_price']);
  $categoria_id = $_POST['categoria_id'];
  $prod_desc = $_POST['prod_desc'];

  // Procesar imagen
  $prod_img = isset($prod->prod_img) ? $prod->prod_img : '';

  if (isset($_POST['remove_img']) && $_POST['remove_img'] == 'on') {
    if (!empty($prod_img)) {
      if (file_exists('../assets/img/products/' . $prod_img)) {
        unlink('../assets/img/products/' . $prod_img);
      }
      $prod_img = '';
    }
  } elseif (isset($_FILES['prod_img']) && $_FILES['prod_img']['error'] == 0) {
    // Eliminar imagen anterior si existe
    if (!empty($prod_img) && file_exists('../assets/img/products/' . $prod_img)) {
      unlink('../assets/img/products/' . $prod_img);
    }

    $ext = pathinfo($_FILES['prod_img']['name'], PATHINFO_EXTENSION);
    $prod_img = $prod_id . '.' . $ext;
    move_uploaded_file($_FILES['prod_img']['tmp_name'], '../assets/img/products/' . $prod_img);
  }

  // Verificar si el código ya existe (excluyendo el producto actual)
  $check = "SELECT * FROM rpos_products WHERE prod_code = ? AND prod_id != ?";
  $stmt = $mysqli->prepare($check);
  $stmt->bind_param('ss', $prod_code, $prod_id);
  $stmt->execute();
  $res = $stmt->get_result();
  $stmt->close();

  if ($res->num_rows > 0) {
    $err = "El código de producto ya está en uso por otro producto";
    echo "<script>alert('$err'); window.location.href='products.php';</script>";
  } else {
    $query = "UPDATE rpos_products SET 
                 prod_name = ?, prod_code = ?, prod_price = ?, 
                 prod_desc = ?, prod_img = ?, categoria_id = ?
                 WHERE prod_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('ssdssss', $prod_name, $prod_code, $prod_price, $prod_desc, $prod_img, $categoria_id, $prod_id);
    $stmt->execute();
    $stmt->close();

    if ($stmt) {
      $success = "Producto actualizado correctamente";
      echo "<script>alert('$success'); window.location.href='products.php';</script>";
    } else {
      $err = "Error al actualizar producto";
      echo "<script>alert('$err'); window.location.href='products.php';</script>";
    }
  }
}
?>