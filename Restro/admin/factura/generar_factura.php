<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../config/config.php');
include('../config/checklogin.php');
include('../config/funciones_comprobantes.php');

check_login();

// Debug
error_log("=== GENERAR FACTURA INICIADO ===");
error_log("POST: " . print_r($_POST, true));

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método no permitido");
    }

    // Validar campos requeridos
    $required_fields = ['mesa_id', 'tipo_factura', 'metodo_pago'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Campo requerido faltante: $field");
        }
    }

    $mesa_id        = $_POST['mesa_id'];
    $tipo_factura   = $_POST['tipo_factura'];
    $metodo_pago    = $_POST['metodo_pago'];
    $cliente_id     = $_POST['cliente_id'] ?? null;
    $monto_recibido = isset($_POST['monto_recibido']) ? floatval($_POST['monto_recibido']) : 0;

    error_log("Procesando mesa: $mesa_id, tipo: $tipo_factura, metodo: $metodo_pago");

    // Verificar caja abierta
    $stmt = $mysqli->prepare("SELECT * FROM rpos_caja WHERE estado = 'Abierta' AND usuario_id = ?");
    $stmt->bind_param('s', $_SESSION['user_id']);
    $stmt->execute();
    $caja_abierta = $stmt->get_result()->fetch_object();
    $stmt->close();

    if (!$caja_abierta) {
        throw new Exception("Debe abrir caja antes de facturar");
    }

    // Obtener mesa y mesero
    $stmt = $mysqli->prepare("SELECT numero_mesa, mesero_asignado FROM rpos_mesas WHERE mesa_id = ?");
    $stmt->bind_param('s', $mesa_id);
    $stmt->execute();
    $mesa_info = $stmt->get_result()->fetch_object();
    $stmt->close();

    if (!$mesa_info) {
        throw new Exception("Mesa no encontrada");
    }

    $numero_mesa = $mesa_info->numero_mesa;
    $mesero_id   = $mesa_info->mesero_asignado;

    // Obtener todas las órdenes activas para esta mesa
    $stmt = $mysqli->prepare("
        SELECT om.order_id 
        FROM rpos_ordenes_mesas om 
        INNER JOIN rpos_orders o ON om.order_id = o.order_id 
        WHERE om.mesa_id = ? AND om.estado = 'Activa' 
        AND o.order_status != 'Facturada'
    ");
    $stmt->bind_param('s', $mesa_id);
    $stmt->execute();
    $ordenes_result = $stmt->get_result();
    $ordenes = [];
    while ($row = $ordenes_result->fetch_object()) {
        $ordenes[] = $row->order_id;
    }
    $stmt->close();

    if (empty($ordenes)) {
        throw new Exception("No hay órdenes activas para esta mesa");
    }

    // Obtener todos los productos de todas las órdenes
    $placeholders = implode(',', array_fill(0, count($ordenes), '?'));
    $stmt = $mysqli->prepare("
        SELECT o.*, p.prod_name 
        FROM rpos_orders o 
        INNER JOIN rpos_products p ON o.prod_id = p.prod_id 
        WHERE o.order_id IN ($placeholders) 
        AND o.order_status != 'Facturada'
    ");
    
    // Vincular parámetros dinámicamente
    $types = str_repeat('s', count($ordenes));
    $stmt->bind_param($types, ...$ordenes);
    $stmt->execute();
    $pedidos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($pedidos)) {
        throw new Exception("No hay pedidos activos en las órdenes");
    }

    // Calcular totales
    $subtotal = 0;
    foreach ($pedidos as $p) {
        $subtotal += floatval($p['prod_price']) * floatval($p['prod_qty']);
    }

    $config   = $mysqli->query("SELECT itebis_porcentaje, servicio_porcentaje FROM rpos_configuracion WHERE config_id = 1")->fetch_object();
    $itebis   = $subtotal * ($config->itebis_porcentaje / 100);
    $servicio = $subtotal * ($config->servicio_porcentaje / 100);
    $total    = $subtotal + $itebis + $servicio;

    // Validar efectivo
    $vuelto = 0;
    if ($metodo_pago == 'Efectivo') {
        if ($monto_recibido < $total) {
            throw new Exception("Monto recibido insuficiente");
        }
        $vuelto = $monto_recibido - $total;
        $_SESSION['vuelto_info'] = [
            'monto_recibido' => $monto_recibido,
            'vuelto'         => $vuelto,
            'total'          => $total
        ];
    }

    // Factura
    $factura_id   = uniqid("FAC");
    $factura_code = "FACT-" . date("YmdHis");
    $cliente_nombre = "Consumidor Final";
    $cliente_rnc    = "N/A";
    $ncf            = null;
    $estado_factura = "Pagada";

    // Comprobantes fiscales
    if ($tipo_factura == "Fiscal" || $tipo_factura == "Credito") {
        if (!$cliente_id) {
            throw new Exception("Seleccione un cliente válido");
        }
        $stmt = $mysqli->prepare("SELECT customer_name, rnc_cedula FROM rpos_customers WHERE customer_id=?");
        $stmt->bind_param('s', $cliente_id);
        $stmt->execute();
        $cliente = $stmt->get_result()->fetch_object();
        $stmt->close();

        if (!$cliente) {
            throw new Exception("Cliente no encontrado");
        }
        $cliente_nombre = $cliente->customer_name;
        $cliente_rnc    = $cliente->rnc_cedula;

        if ($tipo_factura == "Fiscal") {
            $ncf = obtenerProximoComprobante('B01');
            if ($ncf) actualizarSecuencialComprobante('B01');
        } else {
            $estado_factura = "Pendiente";
            $serie = ($cliente_rnc && $cliente_rnc != 'N/A') ? 'B01' : 'B02';
            $ncf = obtenerProximoComprobante($serie);
            if ($ncf) actualizarSecuencialComprobante($serie);
        }
    } else {
        $ncf = obtenerProximoComprobante('B02');
        if ($ncf) actualizarSecuencialComprobante('B02');
    }

    // Insertar factura
    $stmt = $mysqli->prepare("INSERT INTO rpos_facturas
        (factura_id, factura_code, mesa_id, numero_mesa, cliente_nombre, cliente_rnc,
         subtotal, itebis, servicio, total, usuario_id, mesero_id, ncf, tipo_factura, estado)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        'ssssssddddsssss',
        $factura_id,
        $factura_code,
        $mesa_id,
        $numero_mesa,
        $cliente_nombre,
        $cliente_rnc,
        $subtotal,
        $itebis,
        $servicio,
        $total,
        $_SESSION['user_id'],
        $mesero_id,
        $ncf,
        $tipo_factura,
        $estado_factura
    );
    if (!$stmt->execute()) {
        throw new Exception("Error al insertar factura: " . $stmt->error);
    }
    $stmt->close();

    // Insertar items de la factura
    foreach ($pedidos as $pedido) {
        $item_id = uniqid("FITM");
        $product_total = floatval($pedido['prod_price']) * floatval($pedido['prod_qty']);
        
        $stmt = $mysqli->prepare("INSERT INTO rpos_factura_items 
            (factura_item_id, factura_id, product_id, product_name, product_price, product_qty, product_total) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            'ssssddd',
            $item_id,
            $factura_id,
            $pedido['prod_id'],
            $pedido['prod_name'],
            $pedido['prod_price'],
            $pedido['prod_qty'],
            $product_total
        );
        $stmt->execute();
        $stmt->close();
    }

    // Pago si no es crédito
    if ($tipo_factura != "Credito") {
        $pay_id = uniqid("PAY");
        $pay_code = 'PAY-' . date('YmdHis');
        $customer_id = $cliente_id ?: 'consumidor_final';

        $stmt = $mysqli->prepare("INSERT INTO rpos_payments 
            (pay_id, pay_code, order_code, customer_id, pay_amt, pay_method) 
            VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssds', $pay_id, $pay_code, $factura_code, $customer_id, $total, $metodo_pago);
        $stmt->execute();
        $stmt->close();

        $campo_caja = match ($metodo_pago) {
            'Efectivo'        => "ventas_efectivo",
            'Tarjeta Débito',
            'Tarjeta Crédito' => "ventas_tarjeta",
            'Transferencia'   => "ventas_transferencia",
            default           => "ventas_app"
        };
        $update_caja = "UPDATE rpos_caja 
                        SET $campo_caja = $campo_caja + ?,
                            total_ventas = total_ventas + ?
                        WHERE caja_id = ? AND estado = 'Abierta'";
        $stmt = $mysqli->prepare($update_caja);
        $stmt->bind_param('dds', $total, $total, $caja_abierta->caja_id);
        $stmt->execute();
        $stmt->close();
    }

    // Actualizar todas las órdenes, mesa y estado
    foreach ($ordenes as $orden_id) {
        $stmt = $mysqli->prepare("UPDATE rpos_orders SET order_status='Facturada' WHERE order_id=?");
        $stmt->bind_param('s', $orden_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $mysqli->prepare("UPDATE rpos_ordenes_mesas SET estado='Facturada' WHERE order_id=? AND mesa_id=?");
        $stmt->bind_param('ss', $orden_id, $mesa_id);
        $stmt->execute();
        $stmt->close();
    }

    $stmt = $mysqli->prepare("UPDATE rpos_mesas SET estado='Disponible', num_personas=NULL, mesero_asignado=NULL WHERE mesa_id=?");
    $stmt->bind_param('s', $mesa_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['success'] = "Mesa #$numero_mesa facturada correctamente con " . count($pedidos) . " productos";
    $_SESSION['factura_generada'] = $factura_id;

    error_log("Factura generada exitosamente: $factura_id con " . count($pedidos) . " productos");

    // Redirigir a imprimir en nueva pestaña
    header("Location: imprimir_factura.php?id=" . $factura_id);
    exit;
    
} catch (Exception $e) {
    error_log("Error en generar_factura: " . $e->getMessage());
    $_SESSION['error'] = $e->getMessage();
    header("Location: ../mesas/mesas.php");
    exit;
}