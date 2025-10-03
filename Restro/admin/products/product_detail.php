<?php
session_start();
include('../config/config.php');
include('../config/checklogin.php');
check_login();

$id = $_GET['id'] ?? '';
if (empty($id)) {
    header("Location: ../products/products.php");
    exit();
}

$stmt = $mysqli->prepare("SELECT p.*, c.nombre_categoria 
                          FROM rpos_products p
                          LEFT JOIN rpos_categorias_productos c 
                          ON p.categoria_id = c.categoria_id
                          WHERE p.prod_id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$product = $res->fetch_object();
$stmt->close();

if (!$product) {
    header("Location: ../products/products.php");
    exit();
}

require_once('../partials/_head.php');
?>
<body>
<?php require_once('../partials/_sidebar.php'); ?>
<div class="main-content">
<?php require_once('../partials/_topnav.php'); ?>

<div class="header pb-8 pt-5 pt-md-8" style="background-image: url(../assets/img/theme/restro00.jpg); background-size: cover;">
    <span class="mask bg-gradient-dark opacity-8"></span>
    <div class="container-fluid">
        <div class="header-body"></div>
    </div>
</div>

<div class="container-fluid mt--8">
    <div class="card shadow">
        <div class="card-header">
            <a href="products.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <h3 class="mb-0 d-inline-block ml-3">Detalles del Producto</h3>
        </div>
        <div class="card-body text-center">
            <img src="../assets/img/products/<?= htmlspecialchars($product->prod_img ?: 'default.jpg') ?>" 
                 class="img-fluid mb-3" style="max-height: 300px;">
            <h2><?= htmlspecialchars($product->prod_name) ?></h2>
            <p><strong>Código:</strong> <?= htmlspecialchars($product->prod_code) ?></p>
            <p><strong>Categoría:</strong> <?= htmlspecialchars($product->nombre_categoria ?? 'Sin categoría') ?></p>
            <p><strong>Precio:</strong> RD$ <?= number_format($product->prod_price, 2) ?></p>
            <p><strong>Descripción:</strong> <?= nl2br(htmlspecialchars($product->prod_desc)) ?></p>
        </div>
    </div>
</div>

<?php require_once('../partials/_footer.php'); ?>
</div>
</div>
<?php require_once('../partials/_scripts.php'); ?>
</body>
</html>
