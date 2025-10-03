<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/error.log');

include('../config/config.php');
include('../config/checklogin.php');
include('../config/funciones_comprobantes.php');

check_login();

// Debug detallado
error_log("=== GENERAR FACTURA DELIVERY INICIADO ===");
error_log("POST: " . print_r($_POST, true));
error_log("SESSION user_id: " . $_SESSION['user_id']);

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método no permitido");
    }

    // Validar campos requeridos
    $required_fields = ['delivery_id', 'tipo_factura', 'metodo_pago'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Campo requerido faltante: $field");
        }
    }

    $delivery_id    = $_POST['delivery_id'];
    $tipo_factura   = $_POST['tipo_factura'];
    $metodo_pago    = $_POST['metodo_pago'];
    $cliente_id     = $_POST['cliente_id'] ?? null;
    $monto_recibido = isset($_POST['monto_recibido']) ? floatval($_POST['monto_recibido']) : 0;

    error_log("Procesando delivery: $delivery_id, tipo: $tipo_factura, metodo: $metodo_pago");

    // Verificar caja abierta
    $stmt = $mysqli->prepare("SELECT * FROM rpos_caja WHERE estado = 'Abierta' AND usuario_id = ?");
    $stmt->bind_param('s', $_SESSION['user_id']);
    $stmt->execute();
    $caja_abierta = $stmt->get_result()->fetch_object();
    $stmt->close();

    if (!$caja_abierta) {
        throw new Exception("Debe abrir caja antes de facturar");
    }

    // Obtener información del delivery ANTES de procesar
    $stmt = $mysqli->prepare("SELECT * FROM rpos_delivery_orders WHERE delivery_id = ?");
    $stmt->bind_param('s', $delivery_id);
    $stmt->execute();
    $delivery_info = $stmt->get_result()->fetch_object();
    $stmt->close();

    if (!$delivery_info) {
        throw new Exception("Delivery no encontrado");
    }

    error_log("Estado actual del delivery: " . $delivery_info->estado);

    // Obtener items del delivery
    $stmt = $mysqli->prepare("SELECT * FROM rpos_delivery_items WHERE delivery_id = ?");
    $stmt->bind_param('s', $delivery_id);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($items)) {
        throw new Exception("No hay items en el delivery");
    }

    // Totales del delivery
    $subtotal      = floatval($delivery_info->subtotal);
    $itebis        = floatval($delivery_info->impuestos);
    $servicio      = floatval($delivery_info->servicio);
    $cargo_entrega = floatval($delivery_info->cargo_entrega);
    $total         = floatval($delivery_info->total);

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

    // Datos de cliente
    $cliente_nombre = $delivery_info->customer_name ?: "Consumidor Final";
    $cliente_rnc    = "N/A";

    // Si hay cliente_id, obtener sus datos
    if ($cliente_id) {
        $stmt = $mysqli->prepare("SELECT customer_name, rnc_cedula FROM rpos_customers WHERE customer_id=?");
        $stmt->bind_param('s', $cliente_id);
        $stmt->execute();
        $cliente = $stmt->get_result()->fetch_object();
        $stmt->close();

        if ($cliente) {
            $cliente_nombre = $cliente->customer_name;
            $cliente_rnc    = $cliente->rnc_cedula ?: "N/A";
        }
    }

    // Comprobantes fiscales
    $ncf = null;
    $estado_factura = "Pagada";

    if ($tipo_factura == "Fiscal" || $tipo_factura == "Credito") {
        if ($cliente_id) {
            $stmt = $mysqli->prepare("SELECT customer_name, rnc_cedula FROM rpos_customers WHERE customer_id=?");
            $stmt->bind_param('s', $cliente_id);
            $stmt->execute();
            $cliente = $stmt->get_result()->fetch_object();
            $stmt->close();

            if ($cliente) {
                $cliente_nombre = $cliente->customer_name;
                $cliente_rnc    = $cliente->rnc_cedula ?: "N/A";
            } else {
                throw new Exception("Cliente no encontrado");
            }
        } else {
            $cliente_nombre = "Consumidor Final";
            $cliente_rnc = "N/A";
        }

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
    $mesa_id = 'DELIVERY';
    $numero_mesa = 0;
    $usuario_id = $_SESSION['user_id'];
    $factura_id = uniqid("FAC");
    $factura_code = "FACT-" . date("YmdHis");

    // Insertar factura con columnas esenciales
    $stmt = $mysqli->prepare("INSERT INTO rpos_facturas
    (factura_id, factura_code, mesa_id, delivery_id, numero_mesa, cliente_nombre, cliente_rnc,
     ncf, tipo_factura, estado, subtotal, itebis, servicio, cargo_entrega, total, usuario_id)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        'ssssssssssddddds',
        $factura_id,
        $factura_code,
        $mesa_id,
        $delivery_id,
        $numero_mesa,
        $cliente_nombre,
        $cliente_rnc,
        $ncf,
        $tipo_factura,
        $estado_factura,
        $subtotal,
        $itebis,
        $servicio,
        $cargo_entrega,
        $total,
        $usuario_id
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Error al insertar factura: " . $stmt->error);
    }
    $stmt->close();
    error_log("Factura insertada: $factura_id");

    // Insertar items
    foreach ($items as $item) {
        $factura_item_id = uniqid("FITM");
        $product_id      = $item['prod_id'];
        $product_name    = $item['prod_name'];
        $product_price   = floatval($item['prod_price']);
        $product_qty     = floatval($item['prod_qty']);
        $product_total   = $product_price * $product_qty;

        $stmt = $mysqli->prepare("INSERT INTO rpos_factura_items 
            (factura_item_id, factura_id, product_id, product_name, product_price, product_qty, product_total)
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            'ssssddd',
            $factura_item_id,
            $factura_id,
            $product_id,
            $product_name,
            $product_price,
            $product_qty,
            $product_total
        );
        if (!$stmt->execute()) {
            throw new Exception("Error al insertar item: " . $stmt->error);
        }
        $stmt->close();
    }

    // Insertar pago si no es crédito
    if ($tipo_factura != "Credito") {
        $pay_id = uniqid("PAY");
        $pay_code = 'PAY-' . date('YmdHis');
        $customer_id = $cliente_id ?: 'consumidor_final';

        $stmt = $mysqli->prepare("INSERT INTO rpos_payments 
            (pay_id, pay_code, factura_id, customer_id, pay_amt, pay_method) 
            VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sssdss', $pay_id, $pay_code, $factura_id, $customer_id, $total, $metodo_pago);
        if (!$stmt->execute()) {
            throw new Exception("Error al procesar pago: " . $stmt->error);
        }
        $stmt->close();
    }

    // SOLUCIÓN ALTERNATIVA: Usar consulta SQL directa sin prepared statements
    error_log("Actualizando delivery $delivery_id a estado 'Pagada' (método alternativo)");
    
    // Método 1: Intentar con consulta directa
    $update_query = "UPDATE rpos_delivery_orders SET estado = 'Pagada', fecha_pago = NOW() WHERE delivery_id = '$delivery_id'";
    if ($mysqli->query($update_query)) {
        error_log("Delivery actualizado correctamente a estado 'Pagada' (consulta directa)");
    } else {
        $error_msg = $mysqli->error;
        error_log("ERROR en consulta directa: " . $error_msg);
        
        // Método 2: Intentar con consulta preparada simple
        $stmt_simple = $mysqli->prepare("UPDATE rpos_delivery_orders SET estado = 'Pagada', fecha_pago = NOW() WHERE delivery_id = ?");
        $stmt_simple->bind_param('s', $delivery_id);
        
        if ($stmt_simple->execute()) {
            error_log("Delivery actualizado correctamente a estado 'Pagada' (consulta simple)");
        } else {
            $error_msg2 = $stmt_simple->error;
            error_log("ERROR en consulta simple: " . $error_msg2);
            throw new Exception("Error al actualizar estado del delivery: " . $error_msg2);
        }
        $stmt_simple->close();
    }

    // Método 3: Si todo lo demás falla, verificar manualmente después de la factura
    // Esta es una solución de respaldo
    error_log("Creando trigger manual para actualizar delivery...");
    
    // Verificar después de 2 segundos si se actualizó el delivery
    $verificar_query = "SELECT estado FROM rpos_delivery_orders WHERE delivery_id = '$delivery_id'";
    $resultado = $mysqli->query($verificar_query);
    if ($resultado && $resultado->num_rows > 0) {
        $delivery_actual = $resultado->fetch_object();
        if ($delivery_actual->estado != 'Pagada') {
            error_log("El delivery NO se actualizó automáticamente. Actualizando manualmente...");
            $mysqli->query("UPDATE rpos_delivery_orders SET estado = 'Pagada' WHERE delivery_id = '$delivery_id'");
            error_log("Delivery actualizado manualmente a 'Pagada'");
        } else {
            error_log("El delivery ya está en estado 'Pagada'");
        }
    }

    error_log("Factura delivery generada correctamente: $factura_id");

    // Redirigir a imprimir
    header("Location:imprimir_factura.php?id=$factura_id&tipo=delivery");
    exit();

} catch (Exception $e) {
    error_log("ERROR FATAL FACTURA DELIVERY: " . $e->getMessage());
    $_SESSION['error_msg'] = $e->getMessage();
    header("Location: ../factura/imprimir_factura.php");
    exit();
}