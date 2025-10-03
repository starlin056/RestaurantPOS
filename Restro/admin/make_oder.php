<?php
session_start();
include('config/config.php');
include('config/check_login.php');
include('config/code-generator.php');

check_login();

// Procesar creación de pedido
if (isset($_POST['make'])) {
    // Validar campos vacíos
    if (empty($_POST["order_code"]) || empty($_POST["customer_name"]) || empty($_GET['prod_price'])) {
        $err = "Por favor complete todos los campos requeridos";
    } else {
        $order_id = $_POST['order_id'];
        $order_code = $_POST['order_code'];
        $customer_id = $_POST['customer_id'];
        $customer_name = $_POST['customer_name'];
        $prod_id = $_GET['prod_id'];
        $prod_name = $_GET['prod_name'];
        $prod_price = str_replace(['$', ','], '', $_GET['prod_price']); // Limpiar formato de precio
        $prod_qty = (int)$_POST['prod_qty'];

        // Validar cantidad
        if ($prod_qty <= 0) {
            $err = "La cantidad debe ser mayor a cero";
        } else {
            // Insertar pedido en la base de datos
            $postQuery = "INSERT INTO rpos_orders (prod_qty, order_id, order_code, customer_id, customer_name, prod_id, prod_name, prod_price) VALUES(?,?,?,?,?,?,?,?)";
            $postStmt = $mysqli->prepare($postQuery);
            $rc = $postStmt->bind_param('ssssssss', $prod_qty, $order_id, $order_code, $customer_id, $customer_name, $prod_id, $prod_name, $prod_price);
            $postStmt->execute();
            
            if ($postStmt) {
                $success = "Pedido creado exitosamente";
                header("refresh:1; url=payments.php");
            } else {
                $err = "Error al crear el pedido, por favor intente nuevamente";
            }
        }
    }
}

require_once('partials/_head.php');
?>

<body class="g-sidenav-show bg-gray-100">
  <!-- Barra lateral -->
  <?php require_once('partials/_sidebar.php'); ?>
  
  <!-- Contenido principal -->
  <main class="main-content position-relative border-radius-lg">
    <!-- Barra de navegación superior -->
    <?php require_once('partials/_topnav.php'); ?>
    
    <!-- Encabezado -->
    <div class="header bg-gradient-primary pb-8 pt-5 pt-md-8" style="background-image: url(assets/img/theme/restro00.jpg); background-size: cover;">
      <span class="mask bg-gradient-dark opacity-8"></span>
      <div class="container-fluid">
        <div class="header-body">
          <div class="row align-items-center py-4">
            <div class="col-lg-6 col-7">
              <h6 class="h2 text-white d-inline-block mb-0">Nuevo Pedido</h6>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Contenido de la página -->
    <div class="container-fluid mt--6">
      <div class="row">
        <div class="col">
          <div class="card shadow-lg">
            <div class="card-header bg-white border-0">
              <div class="row align-items-center">
                <div class="col-8">
                  <h3 class="mb-0">Detalles del Pedido</h3>
                </div>
              </div>
            </div>
            
            <div class="card-body">
              <!-- Mostrar mensajes de error/éxito -->
              <?php if(isset($err)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                  <span class="alert-icon"><i class="fas fa-exclamation-circle"></i></span>
                  <span class="alert-text"><?php echo $err; ?></span>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
              <?php endif; ?>
              
              <?php if(isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                  <span class="alert-icon"><i class="fas fa-check-circle"></i></span>
                  <span class="alert-text"><?php echo $success; ?></span>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
              <?php endif; ?>
              
              <form method="POST" id="orderForm">
                <div class="row">
                  <!-- Columna izquierda -->
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-control-label">Cliente</label>
                      <select class="form-select" name="customer_name" id="custName" onChange="getCustomer(this.value)" required>
                        <option value="">Seleccione un cliente</option>
                        <?php
                        $ret = "SELECT * FROM rpos_customers";
                        $stmt = $mysqli->prepare($ret);
                        $stmt->execute();
                        $res = $stmt->get_result();
                        while ($cust = $res->fetch_object()) {
                          echo '<option value="'.htmlspecialchars($cust->customer_name).'">'.htmlspecialchars($cust->customer_name).'</option>';
                        }
                        ?>
                      </select>
                      <input type="hidden" name="order_id" value="<?php echo $orderid; ?>">
                    </div>
                    
                    <div class="form-group">
                      <label class="form-control-label">ID del Cliente</label>
                      <input type="text" name="customer_id" id="customerID" class="form-control" readonly>
                    </div>
                  </div>
                  
                  <!-- Columna derecha -->
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-control-label">Código del Pedido</label>
                      <input type="text" name="order_code" value="<?php echo $alpha; ?>-<?php echo $beta; ?>" class="form-control" readonly>
                    </div>
                  </div>
                </div>
                
                <hr class="my-4">
                
                <!-- Detalles del producto -->
                <?php
                $prod_id = $_GET['prod_id'];
                $ret = "SELECT * FROM rpos_products WHERE prod_id = ?";
                $stmt = $mysqli->prepare($ret);
                $stmt->bind_param('s', $prod_id);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($prod = $res->fetch_object()) {
                ?>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-control-label">Precio Unitario</label>
                      <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="text" class="form-control" value="<?php echo number_format($prod->prod_price, 2); ?>" readonly>
                        <input type="hidden" name="prod_price" value="<?php echo $prod->prod_price; ?>">
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-control-label">Cantidad</label>
                      <input type="number" name="prod_qty" class="form-control" min="1" value="1" required>
                    </div>
                  </div>
                </div>
                <?php } ?>
                
                <div class="row mt-4">
                  <div class="col-md-12">
                    <button type="submit" name="make" class="btn btn-success btn-lg w-100">
                      <i class="fas fa-check-circle mr-2"></i>Confirmar Pedido
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
  
  <!-- Footer -->
  <?php require_once('partials/_footer.php'); ?>
  
  <!-- Scripts -->
  <?php require_once('partials/_scripts.php'); ?>
  
  <!-- Script para obtener datos del cliente -->
  <script>
    function getCustomer(customerName) {
      if (customerName.length == 0) {
        document.getElementById("customerID").value = "";
        return;
      } else {
        const xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
          if (this.readyState == 4 && this.status == 200) {
            document.getElementById("customerID").value = this.responseText;
          }
        };
        xmlhttp.open("GET", "get_customer.php?name=" + customerName, true);
        xmlhttp.send();
      }
    }
    
    // Validación del formulario
    document.getElementById('orderForm').addEventListener('submit', function(e) {
      const quantity = document.querySelector('input[name="prod_qty"]').value;
      if (quantity <= 0) {
        e.preventDefault();
        alert('La cantidad debe ser mayor a cero');
      }
    });
  </script>
</body>
</html>