<?php
ob_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/error.log'); // asegúrate que exista la carpeta logs
error_reporting(E_ALL);

include('../config/config.php');
include('../config/checklogin.php');
check_login();

header('Content-Type: application/json');

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener datos del request
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos o JSON mal formado']);
    exit;
}

// Función para generar IDs únicos
function generarIDUnico($mysqli, $tabla, $columna, $longitud = 12) {
    do {
        $id = substr(md5(uniqid(rand(), true)), 0, $longitud);
        $res = $mysqli->query("SELECT $columna FROM $tabla WHERE $columna = '$id'");
    } while($res && $res->num_rows > 0);
    return $id;
}

$mysqli->begin_transaction();

try {
    // 1. Verificar o crear cliente
    $cliente_id = null;

    if (!empty($input['cliente']['id_existente'])) {
        $cliente_id = $input['cliente']['id_existente'];

        // Verificar que el cliente existe
        $stmt_check = $mysqli->prepare("SELECT customer_id FROM rpos_customers WHERE customer_id = ?");
        $stmt_check->bind_param('s', $cliente_id);
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        if ($result->num_rows === 0) {
            throw new Exception('El cliente seleccionado no existe');
        }
        $stmt_check->close();
    } else {
        // Crear nuevo cliente
        $cliente_id = generarIDUnico($mysqli, 'rpos_customers', 'customer_id', 12);

        $stmt = $mysqli->prepare("INSERT INTO rpos_customers 
            (customer_id, customer_name, customer_phoneno, customer_email, customer_password, tipo_cliente, rnc_cedula, direccion_fiscal, ciudad, sector) 
            VALUES (?, ?, ?, ?, ?, 'Persona Física', ?, ?, ?, ?)");

        if (!$stmt) throw new Exception('Error preparando consulta: ' . $mysqli->error);

        $password = sha1('temp123');
        $email_temp = !empty($input['cliente']['telefono']) ? $input['cliente']['telefono'] . '@delivery.com' : 'cliente@delivery.com';

        $stmt->bind_param('sssssssss', 
            $cliente_id,
            $input['cliente']['nombre'],
            $input['cliente']['telefono'],
            $email_temp,
            $password,
            $input['cliente']['rnc'],
            $input['cliente']['direccion'],
            $input['cliente']['ciudad'],
            $input['cliente']['sector']
        );

        if (!$stmt->execute()) throw new Exception('Error al crear cliente: ' . $stmt->error);
        $stmt->close();
    }

    // 2. Obtener caja abierta
    $caja_query = $mysqli->query("SELECT * FROM rpos_caja WHERE estado = 'Abierta' LIMIT 1");
    if ($caja_query->num_rows === 0) {
        throw new Exception('No hay caja abierta. Debe abrir una caja antes de realizar pedidos.');
    }
    $caja = $caja_query->fetch_assoc();
    $caja_id = $caja['caja_id'];

    // 3. Generar código de orden único
    $order_code = 'DLV' . date('YmdHis') . rand(100, 999);
    $delivery_id = generarIDUnico($mysqli, 'rpos_delivery_orders', 'delivery_id', 12);

    // 4. Crear registro en delivery_orders - CAMBIO IMPORTANTE: estado 'Lista para facturar'
    $stmt = $mysqli->prepare("INSERT INTO rpos_delivery_orders 
        (delivery_id, order_code, customer_name, customer_phone, customer_address, 
        subtotal, impuestos, servicio, cargo_entrega, total, estado, repartidor_id, 
        cliente_id, caja_id, itebis_porcentaje, servicio_porcentaje, notas, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Lista para facturar', ?, ?, ?, 18.00, 10.00, ?, NOW())");

    if (!$stmt) throw new Exception('Error preparando consulta delivery: ' . $mysqli->error);

    $notas_repartidor = $input['notas'] ?? '';
    $repartidor_id = !empty($input['repartidor']) ? $input['repartidor'] : null;

    // ⚡ Cadena de tipos corregida: 14 parámetros
    $stmt->bind_param('sssssdddddssss', 
        $delivery_id,
        $order_code,
        $input['cliente']['nombre'],
        $input['cliente']['telefono'],
        $input['cliente']['direccion'],
        $input['subtotal'],
        $input['itbis'],
        $input['servicio'],
        $input['cargo_entrega'],
        $input['total'],
        $repartidor_id,
        $cliente_id,
        $caja_id,
        $notas_repartidor
    );

    if (!$stmt->execute()) throw new Exception('Error al crear orden de delivery: ' . $stmt->error);
    $stmt->close();

    // 5. Agregar items del pedido
    foreach ($input['productos'] as $producto) {
        $stmt = $mysqli->prepare("INSERT INTO rpos_delivery_items 
            (delivery_id, prod_id, prod_name, prod_price, prod_qty) 
            VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) throw new Exception('Error preparando consulta items: ' . $mysqli->error);

        $stmt->bind_param('sssdi', 
            $delivery_id,
            $producto['id'],
            $producto['nombre'],
            $producto['precio'],
            $producto['cantidad']
        );

        if (!$stmt->execute()) throw new Exception('Error al agregar producto: ' . $stmt->error);
        $stmt->close();

        // 6. Determinar si va a cocina o bar según el tipo de producto
        $prod_query = $mysqli->query("SELECT tipo FROM rpos_products WHERE prod_id = '" . $mysqli->real_escape_string($producto['id']) . "'");
        if ($prod_query && $prod_query->num_rows > 0) {
            $prod_data = $prod_query->fetch_assoc();
            $tipo_producto = $prod_data['tipo'];

            // Crear orden según el tipo
            $order_id = generarIDUnico($mysqli, 'rpos_orders', 'order_id', 12);

            $order_stmt = $mysqli->prepare("INSERT INTO rpos_orders 
                (order_id, order_code, customer_id, customer_name, prod_id, 
                prod_name, prod_price, prod_qty, order_status, created_by, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente', 'delivery_system', NOW())");

            if (!$order_stmt) throw new Exception('Error preparando consulta orden: ' . $mysqli->error);

            $prod_price_str = (string)$producto['precio'];
            $prod_qty_str = (string)$producto['cantidad'];

            $order_stmt->bind_param('ssssssss', 
                $order_id,
                $order_code,
                $cliente_id,
                $input['cliente']['nombre'],
                $producto['id'],
                $producto['nombre'],
                $prod_price_str,
                $prod_qty_str
            );

            if (!$order_stmt->execute()) throw new Exception('Error al crear orden: ' . $order_stmt->error);
            $order_stmt->close();

            // Crear registro en cocina o bar según el tipo
            if ($tipo_producto === 'Comida') {
                $estado_id = generarIDUnico($mysqli, 'rpos_estados_cocina', 'estado_id', 12);
                $estado_stmt = $mysqli->prepare("INSERT INTO rpos_estados_cocina (estado_id, order_id, estado) VALUES (?, ?, 'Pendiente')");
                if ($estado_stmt) {
                    $estado_stmt->bind_param('ss', $estado_id, $order_id);
                    $estado_stmt->execute();
                    $estado_stmt->close();
                }
            } else if ($tipo_producto === 'Bebida') {
                $estado_id = generarIDUnico($mysqli, 'rpos_estados_bar', 'estado_id', 12);
                $estado_stmt = $mysqli->prepare("INSERT INTO rpos_estados_bar (estado_id, order_id, estado) VALUES (?, ?, 'Pendiente')");
                if ($estado_stmt) {
                    $estado_stmt->bind_param('ss', $estado_id, $order_id);
                    $estado_stmt->execute();
                    $estado_stmt->close();
                }
            }
        }
    }

    // 7. Confirmar transacción
    $mysqli->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Pedido procesado correctamente',
        'delivery_id' => $delivery_id,
        'order_code' => $order_code
    ]);

} catch (Exception $e) {
    $mysqli->rollback();
    error_log('Error en delivery_procesar.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}