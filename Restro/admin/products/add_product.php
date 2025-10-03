<?php
session_start();
include('../config/config.php');
include('../config/checklogin.php');
include('../config/code-generator.php');

check_login();

if (isset($_POST['addProduct'])) {
    if (
        empty($_POST["prod_code"]) ||
        empty($_POST["prod_name"]) ||
        empty($_POST['prod_desc']) ||
        empty($_POST['prod_price']) ||
        empty($_POST['categoria_id'])
    ) {
        $err = "No se aceptan campos vacíos";
    } else {
        // Generar un ID único si no existe
        $prod_id = !empty($_POST['prod_id']) ? $_POST['prod_id'] : uniqid();

        $prod_code  = $_POST['prod_code'];
        $prod_name = $_POST['prod_name'];
        $prod_img = $_FILES['prod_img']['name'];
        if (!empty($prod_img)) {
            move_uploaded_file($_FILES["prod_img"]["tmp_name"], "../assets/img/products/" . $_FILES["prod_img"]["name"]);
        }
        $prod_desc = $_POST['prod_desc'];
        $prod_price = $_POST['prod_price'];
        $categoria_id = (int)$_POST['categoria_id'];

        // Validar categoría
        if (empty($categoria_id)) {
            $err = "Debe seleccionar una categoría válida.";
        } else {
            // --- CALCULAR TIPO AUTOMÁTICO SEGÚN LA CATEGORÍA ---
            $cat_stmt = $mysqli->prepare("SELECT nombre_categoria FROM rpos_categorias_productos WHERE categoria_id = ?");
            $cat_stmt->bind_param('i', $categoria_id);
            $cat_stmt->execute();
            $cat_stmt->bind_result($nombre_categoria);
            $cat_stmt->fetch();
            $cat_stmt->close();

            $tipo = (stripos($nombre_categoria, 'bebida') !== false) ? 'Bebida' : 'Comida';

            // INSERT incluyendo el tipo
            $postQuery = "INSERT INTO rpos_products 
                (prod_id, prod_code, prod_name, prod_img, prod_desc, prod_price, categoria_id, tipo) 
                VALUES (?,?,?,?,?,?,?,?)";

            $postStmt = $mysqli->prepare($postQuery);
            $postStmt->bind_param(
                'ssssssis',
                $prod_id,
                $prod_code,
                $prod_name,
                $prod_img,
                $prod_desc,
                $prod_price,
                $categoria_id,
                $tipo
            );

            if ($postStmt->execute()) {
                $success = "Producto agregado correctamente";
                header("refresh:1; url=add_product.php");
            } else {
                $err = "Error al guardar producto: " . $postStmt->error;
            }

            $postStmt->close();
        }
    }
}

require_once('../partials/_head.php');
?>

<style>
.btn-glass {
    background: rgba(40, 167, 69, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(6px);
    color: #fff;
    transition: all 0.3s ease;
}
.btn-glass:hover {
    background: rgba(40, 167, 69, 0.4);
    color: #000;
    border-color: rgba(255, 255, 255, 0.5);
}
.card-body {
    background-color: rgba(255, 255, 255, 0.05);
}
</style>

<body>
<?php require_once('../partials/_sidebar.php'); ?>
<div class="main-content">
    <?php require_once('../partials/_topnav.php'); ?>
    <div style="background-image: url(../assets/img/theme/restro00.jpg); background-size: cover;" class="header pb-8 pt-5 pt-md-8">
        <span class="mask bg-gradient-dark opacity-8"></span>
        <div class="container-fluid">
            <div class="header-body"></div>
        </div>
    </div>

    <div class="container-fluid mt--8">
        <div class="row">
            <div class="col">
                <div class="card shadow border-0">
                    <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
                        <h3 class="mb-0"><i class="fas fa-plus-circle"></i> Agregar Nuevo Producto</h3>
                        <a href="products.php" class="btn btn-outline-light btn-sm rounded-pill">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (isset($err)) { ?>
                            <div class="alert alert-danger"><?php echo $err; ?></div>
                        <?php } ?>
                        <?php if (isset($success)) { ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php } ?>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="form-row">
                                <div class="col-md-6">
                                    <label>Nombre del Producto</label>
                                    <input type="text" name="prod_name" class="form-control" required>
                                    <input type="hidden" name="prod_id" value="<?php echo $prod_id; ?>" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label>Código del Producto</label>
                                    <input type="text" name="prod_code" value="<?php echo $alpha; ?>-<?php echo $beta; ?>" class="form-control" readonly>
                                </div>
                            </div>
                            <hr>
                            <div class="form-row">
                                <div class="col-md-6">
                                    <label>Imagen del Producto</label>
                                    <input type="file" name="prod_img" class="form-control-file btn btn-outline-success" accept="image/*">
                                </div>
                                <div class="col-md-6">
                                    <label>Precio</label>
                                    <input type="text" name="prod_price" class="form-control" required>
                                </div>
                            </div>
                            <hr>
                            <div class="form-row">
                                <div class="col-md-12">
                                    <label>Categoría</label>
                                    <select name="categoria_id" class="form-control" required>
                                        <option value="">Seleccione una categoría</option>
                                        <?php
                                        $cat_query = "SELECT * FROM rpos_categorias_productos ORDER BY nombre_categoria ASC";
                                        $cat_result = $mysqli->query($cat_query);
                                        while ($cat = $cat_result->fetch_object()):
                                        ?>
                                            <option value="<?php echo $cat->categoria_id; ?>">
                                                <?php echo htmlspecialchars($cat->nombre_categoria); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            <hr>
                            <div class="form-row">
                                <div class="col-md-12">
                                    <label>Descripción</label>
                                    <textarea rows="5" name="prod_desc" class="form-control" required></textarea>
                                </div>
                            </div>
                            <br>
                            <div class="form-row d-flex justify-content-between">
                                <div class="col-md-6">
                                    <button type="submit" name="addProduct" class="btn btn-glass w-100">
                                        <i class="fas fa-save"></i> Guardar Producto
                                    </button>
                                </div>
                                <div class="col-md-6 text-right">
                                    <a href="products.php" class="btn btn-outline-secondary w-100">
                                        <i class="fas fa-arrow-left"></i> Atrás
                                    </a>
                                </div>
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
