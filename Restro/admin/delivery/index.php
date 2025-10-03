<?php
session_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/error.log'); 
include('../config/config.php');
include('../config/checklogin.php');
check_login();

// Verificar si hay caja abierta
$caja_abierta = false;
$caja_query = $mysqli->query("SELECT * FROM rpos_caja WHERE estado = 'Abierta' LIMIT 1");
if ($caja_query->num_rows > 0) {
    $caja_abierta = true;
    $caja_data = $caja_query->fetch_assoc();
    $caja_id = $caja_data['caja_id'];
}

// Obtener productos por categoría
$productos_comida = $mysqli->query("SELECT * FROM rpos_products WHERE tipo = 'Comida' ORDER BY prod_name");
$productos_bebida = $mysqli->query("SELECT * FROM rpos_products WHERE tipo = 'Bebida' ORDER BY prod_name");

// Obtener repartidores disponibles
$repartidores = $mysqli->query("SELECT * FROM rpos_staff WHERE rol = 'Delivery' AND estado = 'Activo'");

// Obtener clientes
$clientes = $mysqli->query("SELECT * FROM rpos_customers ORDER BY customer_name");

include('../partials/_head.php');
?>

<style>
.product-card { cursor: pointer; transition: all 0.3s; }
.product-card:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
.selected-product { background-color: #e8f5e9; border-left: 4px solid #4caf50; }
#order-summary { position: sticky; top: 20px; }
</style>

<body>
<?php require_once('../partials/_sidebar.php'); ?>
<div class="main-content">
<?php require_once('../partials/_topnav.php'); ?>

<div class="header bg-gradient-primary pb-6 pt-5 pt-md-6">
    <div class="container-fluid">
        <div class="header-body">
            <div class="row align-items-center py-4">
                <div class="col-lg-6 col-7">
                    <h1 class="text-white display-4">🛵 Creación de órdenes delivery</h1>
                    <p class="text-white mb-0">Gestión de pedidos para llevar</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-motorcycle me-2"></i>Nuevo Pedido de Delivery</h4>
                </div>
                <div class="card-body">
                    <?php if (!$caja_abierta): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            No hay caja abierta. Debe abrir una caja antes de realizar pedidos.
                        </div>
                    <?php else: ?>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>Información del Cliente</h5>
                                <div class="mb-3">
                                    <label class="form-label">Seleccionar cliente existente</label>
                                    <select class="form-select" id="cliente_existente">
                                        <option value="">-- Seleccionar cliente --</option>
                                        <?php while ($cliente = $clientes->fetch_assoc()): ?>
                                            <option value="<?= $cliente['customer_id'] ?>"
                                                data-nombre="<?= $cliente['customer_name'] ?>"
                                                data-telefono="<?= $cliente['customer_phoneno'] ?>"
                                                data-direccion="<?= $cliente['direccion_fiscal'] ?>"
                                                data-ciudad="<?= $cliente['ciudad'] ?>"
                                                data-sector="<?= $cliente['sector'] ?>"
                                                data-rnc="<?= $cliente['rnc_cedula'] ?>"
                                                data-tipo="<?= $cliente['tipo_cliente'] ?>">
                                                <?= $cliente['customer_name'] ?> - <?= $cliente['customer_phoneno'] ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">O crear nuevo cliente</label>
                                    <input type="text" class="form-control" id="cliente_nombre" placeholder="Nombre completo">
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control mb-2" id="cliente_telefono" placeholder="Teléfono">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control mb-2" id="cliente_rnc" placeholder="RNC/Cédula">
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <textarea class="form-control" id="cliente_direccion" placeholder="Dirección completa" rows="2"></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control mb-2" id="cliente_ciudad" placeholder="Ciudad">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control mb-2" id="cliente_sector" placeholder="Sector">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h5>Información del Delivery</h5>
                                <div class="mb-3">
                                    <label class="form-label">Repartidor asignado</label>
                                    <select class="form-select" id="repartidor">
                                        <option value="">-- Seleccionar repartidor --</option>
                                        <?php while ($repartidor = $repartidores->fetch_assoc()): ?>
                                            <option value="<?= $repartidor['staff_id'] ?>"><?= $repartidor['staff_name'] ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Notas para el repartidor</label>
                                    <textarea class="form-control" id="notas_repartidor" rows="2" placeholder="Instrucciones especiales para la entrega"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Cargo de entrega</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="cargo_entrega" value="100" min="0" step="50">
                                    </div>
                                </div>

                                <!--Checkboxes para activar/desactivar ITBIS y Servicio -->
                                <div class="mb-3">
                                    <label class="form-label">Opciones de impuestos y servicios</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="activar_itbis" checked>
                                        <label class="form-check-label" for="activar_itbis">Aplicar ITBIS 18%</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="activar_servicio" checked>
                                        <label class="form-check-label" for="activar_servicio">Aplicar Servicio 10%</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h5>Selección de Productos</h5>
                        <ul class="nav nav-tabs mb-3" id="productTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="comida-tab" data-bs-toggle="tab" data-bs-target="#comida" type="button" role="tab">Comida</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="bebida-tab" data-bs-toggle="tab" data-bs-target="#bebida" type="button" role="tab">Bebidas</button>
                            </li>
                        </ul>

                        <div class="tab-content" id="productTabsContent">
                            <div class="tab-pane fade show active" id="comida" role="tabpanel">
                                <div class="row">
                                    <?php while ($producto = $productos_comida->fetch_assoc()): ?>
                                        <div class="col-md-3 mb-3">
                                            <div class="card product-card"
                                                 onclick="agregarProducto('<?= $producto['prod_id'] ?>', '<?= addslashes($producto['prod_name']) ?>', <?= $producto['prod_price'] ?>, '<?= $producto['prod_img'] ?: 'default.jpg' ?>')">
                                                <img src="../assets/img/products/<?= $producto['prod_img'] ?: 'default.jpg' ?>" class="card-img-top" alt="<?= $producto['prod_name'] ?>">
                                                <div class="card-body text-center">
                                                    <h6 class="card-title"><?= $producto['prod_name'] ?></h6>
                                                    <p class="card-text text-success">$<?= number_format($producto['prod_price'], 2) ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="bebida" role="tabpanel">
                                <div class="row">
                                    <?php while ($producto = $productos_bebida->fetch_assoc()): ?>
                                        <div class="col-md-3 mb-3">
                                            <div class="card product-card"
                                                 onclick="agregarProducto('<?= $producto['prod_id'] ?>', '<?= addslashes($producto['prod_name']) ?>', <?= $producto['prod_price'] ?>, '<?= $producto['prod_img'] ?: 'default.jpg' ?>')">
                                                <img src="../assets/img/products/<?= $producto['prod_img'] ?: 'default.jpg' ?>" class="card-img-top" alt="<?= $producto['prod_name'] ?>">
                                                <div class="card-body text-center">
                                                    <h6 class="card-title"><?= $producto['prod_name'] ?></h6>
                                                    <p class="card-text text-success">$<?= number_format($producto['prod_price'], 2) ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card" id="order-summary">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Resumen del Pedido</h5>
                </div>
                <div class="card-body">
                    <div id="productos-seleccionados">
                        <p class="text-muted text-center">No hay productos seleccionados</p>
                    </div>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-2"><span>Subtotal:</span><span id="subtotal">$0.00</span></div>
                        <div class="d-flex justify-content-between mb-2"><span>ITBIS (18%):</span><span id="itbis">$0.00</span></div>
                        <div class="d-flex justify-content-between mb-2"><span>Servicio (10%):</span><span id="servicio">$0.00</span></div>
                        <div class="d-flex justify-content-between mb-2"><span>Cargo de entrega:</span><span id="cargo-entrega-display">$0.00</span></div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold"><span>Total:</span><span id="total">$0.00</span></div>
                    </div>
                    <div class="mt-4">
                        <button class="btn btn-primary w-100" id="btn-procesar-pedido" disabled>
                            <i class="fas fa-paper-plane me-2"></i>Procesar Pedido
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let productosSeleccionados = [];
let subtotal = 0;

document.getElementById('cargo_entrega').addEventListener('input', function() {
    document.getElementById('cargo-entrega-display').textContent = '$' + parseFloat(this.value || 0).toFixed(2);
    calcularTotal();
});

document.getElementById('cliente_existente').addEventListener('change', function() {
    if(this.value){
        const option = this.options[this.selectedIndex];
        document.getElementById('cliente_nombre').value = option.getAttribute('data-nombre');
        document.getElementById('cliente_telefono').value = option.getAttribute('data-telefono');
        document.getElementById('cliente_direccion').value = option.getAttribute('data-direccion');
        document.getElementById('cliente_ciudad').value = option.getAttribute('data-ciudad');
        document.getElementById('cliente_sector').value = option.getAttribute('data-sector');
        document.getElementById('cliente_rnc').value = option.getAttribute('data-rnc');
    }
});

function agregarProducto(id, nombre, precio, imagen){
    const index = productosSeleccionados.findIndex(p => p.id === id);
    if(index === -1){
        productosSeleccionados.push({id, nombre, precio: parseFloat(precio), cantidad:1, imagen});
    }else{
        productosSeleccionados[index].cantidad++;
    }
    actualizarResumen();
}

function eliminarProducto(index){
    productosSeleccionados.splice(index,1);
    actualizarResumen();
}

function actualizarCantidad(index,cambio){
    productosSeleccionados[index].cantidad += cambio;
    if(productosSeleccionados[index].cantidad<1) productosSeleccionados[index].cantidad=1;
    actualizarResumen();
}

function actualizarResumen(){
    const contenedor = document.getElementById('productos-seleccionados');
    subtotal=0;
    if(productosSeleccionados.length===0){
        contenedor.innerHTML='<p class="text-muted text-center">No hay productos seleccionados</p>';
        document.getElementById('btn-procesar-pedido').disabled=true;
    }else{
        let html='<h6>Productos seleccionados:</h6>';
        productosSeleccionados.forEach((producto,index)=>{
            const totalProducto = producto.precio*producto.cantidad;
            subtotal+=totalProducto;
            html+=`
            <div class="border rounded p-2 mb-2 selected-product d-flex align-items-center">
                <img src="../assets/img/products/${producto.imagen}" alt="${producto.nombre}" width="50" class="me-2">
                <div class="flex-grow-1">
                    <strong>${producto.nombre}</strong><br>
                    <small>$${producto.precio.toFixed(2)} x ${producto.cantidad}</small>
                </div>
                <div class="btn-group btn-group-sm me-2">
                    <button class="btn btn-outline-secondary" onclick="actualizarCantidad(${index}, -1)">-</button>
                    <button class="btn btn-outline-secondary" disabled>${producto.cantidad}</button>
                    <button class="btn btn-outline-secondary" onclick="actualizarCantidad(${index}, 1)">+</button>
                </div>
                <button class="btn btn-danger btn-sm" onclick="eliminarProducto(${index})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>`;
        });
        contenedor.innerHTML=html;
        document.getElementById('btn-procesar-pedido').disabled=false;
    }
    calcularTotal();
}

function calcularTotal(){
    const cargoEntrega=parseFloat(document.getElementById('cargo_entrega').value||0);
    const itbisActivo=document.getElementById('activar_itbis').checked;
    const servicioActivo=document.getElementById('activar_servicio').checked;

    const itbis = itbisActivo ? subtotal*0.18 : 0;
    const servicio = servicioActivo ? subtotal*0.10 : 0;
    const total = subtotal + itbis + servicio + cargoEntrega;

    document.getElementById('subtotal').textContent='$'+subtotal.toFixed(2);
    document.getElementById('itbis').textContent='$'+itbis.toFixed(2);
    document.getElementById('servicio').textContent='$'+servicio.toFixed(2);
    document.getElementById('cargo-entrega-display').textContent='$'+cargoEntrega.toFixed(2);
    document.getElementById('total').textContent='$'+total.toFixed(2);
}

document.getElementById('activar_itbis').addEventListener('change', calcularTotal);
document.getElementById('activar_servicio').addEventListener('change', calcularTotal);

document.getElementById('btn-procesar-pedido').addEventListener('click',function(){
    const clienteNombre = document.getElementById('cliente_nombre').value;
    const clienteTelefono = document.getElementById('cliente_telefono').value;
    const clienteDireccion = document.getElementById('cliente_direccion').value;

    if(!clienteNombre || !clienteTelefono || !clienteDireccion){
        alert('Por favor complete la información del cliente (nombre, teléfono y dirección)');
        return;
    }
    if(productosSeleccionados.length===0){
        alert('Debe agregar al menos un producto al pedido');
        return;
    }

    const pedidoData={
        cliente:{
            nombre:clienteNombre,
            telefono:clienteTelefono,
            direccion:clienteDireccion,
            ciudad:document.getElementById('cliente_ciudad').value,
            sector:document.getElementById('cliente_sector').value,
            rnc:document.getElementById('cliente_rnc').value,
            id_existente:document.getElementById('cliente_existente').value
        },
        repartidor:document.getElementById('repartidor').value,
        notas:document.getElementById('notas_repartidor').value,
        cargo_entrega:parseFloat(document.getElementById('cargo_entrega').value||0),
        productos:productosSeleccionados,
        subtotal:subtotal,
        itbis:document.getElementById('activar_itbis').checked ? subtotal*0.18 : 0,
        servicio:document.getElementById('activar_servicio').checked ? subtotal*0.10 : 0,
        total:subtotal + (document.getElementById('activar_itbis').checked ? subtotal*0.18 : 0) + (document.getElementById('activar_servicio').checked ? subtotal*0.10 : 0) + parseFloat(document.getElementById('cargo_entrega').value||0)
    };

    fetch('delivery_procesar.php',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify(pedidoData)
    }).then(res=>res.json())
      .then(data=>{
        if(data.success){
            alert('Pedido procesado correctamente. Número de orden: '+data.order_code);
           window.open('delivery_conduce.php?delivery_id=' + data.delivery_id, '_blank');

        }else{
            alert('Error: '+data.message);
        }
    }).catch(err=>{
        console.error(err);
        alert('Error al procesar el pedido');
    });
});
</script>

</body>
<?php require_once('../partials/_footer.php'); ?>
<?php require_once('../partials/_scripts.php'); ?>
</html>
