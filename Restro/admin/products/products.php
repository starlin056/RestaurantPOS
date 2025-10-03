<?php
session_start();
include('../config/config.php');
include('../config/checklogin.php');
check_login();



if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  if (!empty($id)) {
    try {
      $adn = "DELETE FROM rpos_products WHERE prod_id = ?";
      $stmt = $mysqli->prepare($adn);
      $stmt->bind_param('s', $id);
      $stmt->execute();
      $stmt->close();

      $success = "Producto eliminado correctamente";
      header("refresh:2; url=products.php");
    } catch (Exception $e) {
      $err = "Error al eliminar producto: " . $e->getMessage();
    }
  } else {
    $err = "ID de producto inválido";
  }
}

// Buscador
$search = '';
$category_id = '';
$conditions = [];
$params = [];
$types = '';

if (isset($_GET['search']) && !empty($_GET['search'])) {
  $search = trim($_GET['search']);
  $conditions[] = "(p.prod_name LIKE ? OR p.prod_code LIKE ? OR p.prod_desc LIKE ?)";
  $search_param = "%$search%";
  $params = array_merge($params, [$search_param, $search_param, $search_param]);
  $types .= 'sss';
}

if (isset($_GET['category']) && !empty($_GET['category'])) {
  $category_id = $_GET['category'];
  $conditions[] = "p.categoria_id = ?";
  $params[] = $category_id;
  $types .= 's';
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
      <!-- Alertas -->
      <?php if ($success) { ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <strong>¡Éxito!</strong> <?php echo $success; ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
      <?php } ?>
      <?php if ($err) { ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <strong>Error:</strong> <?php echo $err; ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
      <?php } ?>

      <!-- Filtros y buscador -->
      <div class="row mb-4">
        <div class="col">
          <div class="card shadow">
            <div class="card-body">
              <form method="get" action="products.php" class="row">
                <div class="form-group col-md-5">
                  <label for="search">Buscar Productos</label>
                  <div class="input-group">
                    <input type="text" class="form-control" name="search" placeholder="Nombre, código o descripción" value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-primary" type="submit">
                      <i class="fas fa-search"></i>
                    </button>
                  </div>
                </div>
                <div class="form-group col-md-5">
                  <label for="category">Filtrar por Categoría</label>
                  <select name="category" class="form-control" onchange="this.form.submit()">
                    <option value="">Todas las categorías</option>
                    <?php
                    $cat_query = "SELECT * FROM rpos_categorias_productos ORDER BY nombre_categoria ASC";
                    $cat_result = $mysqli->query($cat_query);
                    while ($cat = $cat_result->fetch_object()):
                    ?>
                      <option value="<?php echo $cat->categoria_id; ?>" <?php echo ($category_id == $cat->categoria_id) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat->nombre_categoria); ?>
                      </option>
                    <?php endwhile; ?>
                  </select>
                </div>
                <div class="form-group col-md-2">
                  <label>&nbsp;</label>
                  <a href="products.php" class="btn btn-outline-info w-100 btn-glass">
                    <i class="fas fa-sync-alt"></i> Limpiar
                  </a>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>


      <!-- Tabla -->
      <div class="row">
        <div class="col">
          <div class="card shadow">
            <div class="card-header border-0">
              <div class="row align-items-center">
                <div class="col">
                  <h3 class="mb-0">Lista de Productos</h3>
                </div>
                <div class="col text-right">
                  <a href="add_product.php" class="btn btn-sm btn-success">
                    <i class="fas fa-plus"></i> Nuevo Producto
                  </a>
                </div>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table align-items-center table-flush">
                <thead class="thead-light">
                  <tr>
                    <th scope="col">Imagen</th>
                    <th scope="col">Código</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Categoría</th>
                    <th scope="col">Precio</th>
                    <th scope="col">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  // Consulta base con joins para categorías
                  $ret = "SELECT p.*, c.nombre_categoria 
                          FROM rpos_products p
                          LEFT JOIN rpos_categorias_productos c ON p.categoria_id = c.categoria_id";

                  // Agregar condiciones si existen
                  if (!empty($conditions)) {
                    $ret .= " WHERE " . implode(" AND ", $conditions);
                  }

                  $ret .= " ORDER BY p.prod_name ASC";

                  $stmt = $mysqli->prepare($ret);

                  // Bind parameters dinámicamente si hay parámetros
                  if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                  }

                  $stmt->execute();
                  $res = $stmt->get_result();

                  if ($res->num_rows > 0) {
                    while ($prod = $res->fetch_object()):
                  ?>
                      <tr>
                        <td>
                          <?php if ($prod->prod_img): ?>
                            <img src="../assets/img/products/<?php echo htmlspecialchars($prod->prod_img); ?>" height="60" width="60" class="img-thumbnail">
                          <?php else: ?>
                            <img src="../assets/img/products/default.jpg" height="60" width="60" class="img-thumbnail">
                          <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($prod->prod_code); ?></td>
                        <td><?php echo htmlspecialchars($prod->prod_name); ?></td>
                        <td><?php echo htmlspecialchars($prod->nombre_categoria ?? 'Sin categoría'); ?></td>

                        <td>$RD <?php echo isset($prod->prod_price) ? number_format($prod->prod_price, 2) : '0.00'; ?></td>

                        <td>
                          <div class="btn-group" role="group">
                            <a href="products.php?delete=<?php echo $prod->prod_id; ?>" onclick="return confirm('¿Está seguro que desea eliminar este producto?');" class="btn btn-sm btn-danger">
                              <i class="fas fa-trash"></i>
                            </a>
                            <a href="update_product.php?update=<?php echo $prod->prod_id; ?>" class="btn btn-sm btn-primary">
                              <i class="fas fa-edit"></i>
                            </a>
                            <a href="product_detail.php?id=<?php echo $prod->prod_id; ?>" class="btn btn-sm btn-info">
                              <i class="fas fa-eye"></i>
                            </a>
                          </div>
                        </td>
                      </tr>
                  <?php
                    endwhile;
                  } else {
                    echo '<tr><td colspan="6" class="text-center">No se encontraron productos</td></tr>';
                  }
                  $stmt->close();
                  ?>
                </tbody>
              </table>
            </div>
            <div class="card-footer py-4">
              <nav aria-label="...">
                <!-- Aquí puedes agregar paginación si es necesario -->
              </nav>
            </div>
          </div>
        </div>
      </div>
      <!-- Pie de página -->
      <?php require_once('../partials/_footer.php'); ?>
    </div>
  </div>
  <!-- Scripts -->
  <?php require_once('../partials/_scripts.php'); ?>
</body>

</html>