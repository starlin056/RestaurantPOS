<?php
session_start();
ob_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/error.log');
include('../config/config.php');
include('../config/checklogin.php');
check_login();

if (!isset($_GET['id'])) {
    header("Location: ../factura/mesa_pagos.php");
    exit;
}

$factura_id = $_GET['id'];
$tipo = $_GET['tipo'] ?? 'mesa'; // 'mesa' o 'delivery'

// Configuración empresa
$stmt_config = $mysqli->prepare("SELECT * FROM rpos_configuracion WHERE config_id = 1");
$stmt_config->execute();
$config = $stmt_config->get_result()->fetch_object();
$stmt_config->close();

// Datos de la factura
$stmt = $mysqli->prepare("SELECT f.*, c.nombre_empresa, c.rnc as rnc_empresa, c.direccion, c.telefono, c.logo
                          FROM rpos_facturas f
                          JOIN rpos_configuracion c ON c.config_id = 1
                          WHERE f.factura_id = ?");
$stmt->bind_param('s', $factura_id);
$stmt->execute();
$factura = $stmt->get_result()->fetch_object();
$stmt->close();

if (!$factura) die("Factura no encontrada.");

// ... después de obtener $factura ...

// Si es factura de delivery, obtener datos adicionales del delivery
if ($factura->mesa_id === 'DELIVERY' && !empty($factura->delivery_id)) {
    $stmt_delivery = $mysqli->prepare("SELECT customer_name, customer_phone, customer_address 
                                      FROM rpos_delivery_orders 
                                      WHERE delivery_id = ?");
    $stmt_delivery->bind_param('s', $factura->delivery_id);
    $stmt_delivery->execute();
    $delivery_info = $stmt_delivery->get_result()->fetch_object();
    $stmt_delivery->close();
    
    // Si encontramos info del delivery, usarla para mostrar datos del cliente
    if ($delivery_info && ($factura->cliente_nombre == 'Consumidor Final' || empty($factura->cliente_nombre) || $factura->cliente_nombre == '0')) {
        $factura->cliente_nombre = $delivery_info->customer_name;
    }
}

// Si el RNC es muy corto (solo muestra "132"), intentar obtener el RNC completo del cliente
if (strlen($factura->cliente_rnc) <= 3 && $factura->cliente_rnc != 'N/A' && !empty($factura->cliente_nombre) && $factura->cliente_nombre != 'Consumidor Final') {
    $stmt_cliente = $mysqli->prepare("SELECT rnc_cedula FROM rpos_customers WHERE customer_name = ? LIMIT 1");
    $stmt_cliente->bind_param('s', $factura->cliente_nombre);
    $stmt_cliente->execute();
    $cliente_data = $stmt_cliente->get_result()->fetch_object();
    $stmt_cliente->close();
    
    if ($cliente_data && !empty($cliente_data->rnc_cedula)) {
        $factura->cliente_rnc = $cliente_data->rnc_cedula;
    }
}
    
    // Si encontramos info del delivery, usarla para mostrar datos del cliente
    if ($delivery_info && ($factura->cliente_nombre == 'Consumidor Final' || empty($factura->cliente_nombre) || $factura->cliente_nombre == '0')) {
        $factura->cliente_nombre = $delivery_info->customer_name;
    }


// Obtener items según el tipo de factura
if ($factura->mesa_id != '' && $factura->mesa_id != 'DELIVERY' && $factura->mesa_id != null) {
    // Caso facturas de mesa - OBTENER TODOS LOS PRODUCTOS DE LA FACTURA
    $stmt = $mysqli->prepare("SELECT product_name as prod_name, product_price as prod_price, product_qty as prod_qty, product_total as total
                              FROM rpos_factura_items
                              WHERE factura_id = ?");
    $stmt->bind_param('s', $factura_id);
    $stmt->execute();
    $items = $stmt->get_result();
    $stmt->close();

    $origen = 'Mesa #' . $factura->numero_mesa;
} else {
    // Caso facturas de delivery → tomar productos desde rpos_factura_items
    $stmt = $mysqli->prepare("SELECT product_name as prod_name, product_price as prod_price, product_qty as prod_qty, product_total as total
                              FROM rpos_factura_items
                              WHERE factura_id = ?");
    $stmt->bind_param('s', $factura_id);
    $stmt->execute();
    $items = $stmt->get_result();
    $stmt->close();

    $origen = 'Delivery';
}

// Determinar tipo de comprobante
$tipo_comprobante = "";
if (!empty($factura->ncf)) {
    $codigo = substr($factura->ncf, 0, 3);
    switch ($codigo) {
        case 'B01':
            $tipo_comprobante = "Factura con Crédito Fiscal (B01)";
            break;
        case 'B02':
            $tipo_comprobante = "Factura de Consumo (B02)";
            break;
        case 'B03':
            $tipo_comprobante = "Nota de Débito (B03)";
            break;
        case 'B04':
            $tipo_comprobante = "Nota de Crédito (B04)";
            break;
        default:
            $tipo_comprobante = "Comprobante";
            break;
    }
}

// Cliente / identificación
$es_consumidor_final = ($factura->cliente_nombre == 'Consumidor Final' ||
    $factura->cliente_rnc == 'N/A' ||
    empty($factura->cliente_rnc));

$mostrar_identificacion = !$es_consumidor_final ||
    (substr($factura->ncf, 0, 3) == 'B02' && !empty($factura->cliente_rnc) && $factura->cliente_rnc != 'N/A');
 // CORRECCIÓN IMPORTANTE: Actualizar estado del delivery a "Pagada" 
    $stmt = $mysqli->prepare("UPDATE rpos_delivery_orders SET estado = 'Pagada', factura_id = ?, fecha_pago = NOW() WHERE delivery_id = ?");
    $stmt->bind_param('ss', $factura_id, $delivery_id);
    if (!$stmt->execute()) {
        throw new Exception("Error al actualizar estado del delivery: " . $stmt->error);
    }
    $stmt->close();

    error_log("Delivery actualizado a estado 'Pagada': $delivery_id");

    error_log("Factura delivery generada correctamente: $factura_id");
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura #<?php echo $factura->factura_code; ?></title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }

        .factura-container {
            max-width: 80mm;
            margin: 0 auto;
            background: white;
            padding: 15px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .logo-empresa {
            max-width: 150px;
            max-height: 80px;
            height: auto;
            margin-bottom: 10px;
        }

        .empresa-nombre {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .empresa-info {
            font-size: 10px;
            margin-bottom: 3px;
        }

        .factura-info,
        .cliente-info {
            margin: 10px 0;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        .items-table th,
        .items-table td {
            border-bottom: 1px solid #eee;
            padding: 3px;
            text-align: left;
            font-size: 11px;
        }

        .items-table th {
            border-bottom: 1px solid #000;
            font-weight: bold;
        }

        .total-section {
            margin-top: 15px;
            border-top: 2px solid #000;
            padding-top: 10px;
        }

        .total-line {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            font-size: 11px;
        }

        .total-final {
            font-weight: bold;
            font-size: 12px;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }

        .ncf-info {
            background-color: #f0f0f0;
            padding: 5px;
            text-align: center;
            margin: 5px 0;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
        }

        .tipo-comprobante {
            font-weight: bold;
            font-size: 12px;
            text-align: center;
            margin-bottom: 3px;
        }

        .credito-nota {
            background-color: #fffacd;
            padding: 5px;
            text-align: center;
            margin: 10px 0;
            border: 1px dashed #ccc;
            border-radius: 3px;
            font-size: 11px;
        }

        .delivery-badge {
            background-color: #17a2b8;
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            margin-left: 5px;
        }

        .no-print {
            text-align: center;
            margin-top: 20px;
        }

        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 0 5px;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        @media print {
            body {
                padding: 0;
                margin: 0;
                background: white;
            }

            .factura-container {
                width: 80mm;
                box-shadow: none;
                padding: 10px;
            }

            .logo-empresa {
                max-width: 120px;
                max-height: 60px;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="factura-container">
        <div class="header">
            <?php if (!empty($factura->logo)): ?>
                <img src="<?php echo $factura->logo; ?>" alt="Logo" class="logo-empresa">
            <?php endif; ?>
            <div class="empresa-nombre"><?php echo $factura->nombre_empresa; ?></div>
            <div class="empresa-info">RNC: <?php echo $factura->rnc_empresa; ?></div>
            <div class="empresa-info"><?php echo $factura->direccion; ?></div>
            <div class="empresa-info">Tel: <?php echo $factura->telefono; ?></div>
        </div>

        <div class="factura-info">
            <?php if (!empty($factura->ncf)): ?>
                <div class="tipo-comprobante"><?php echo $tipo_comprobante; ?></div>
                <div class="ncf-info">NCF: <?php echo $factura->ncf; ?></div>
            <?php endif; ?>
            <div>
                <strong>Factura:</strong> <?php echo $factura->factura_code; ?>
                <?php if ($origen === 'Delivery'): ?>
                    <span class="delivery-badge">DELIVERY</span>
                <?php endif; ?>
            </div>
            <div><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($factura->fecha_factura)); ?></div>
            <div><strong>Origen:</strong> <?php echo $origen; ?></div>
        </div>

        <div class="cliente-info">
            <div><strong>Cliente:</strong> <?php echo $factura->cliente_nombre ?: 'Consumidor Final'; ?></div>
            <?php if ($mostrar_identificacion && !empty($factura->cliente_rnc) && $factura->cliente_rnc != 'N/A'): ?>
                <div><strong><?php echo (substr($factura->ncf, 0, 3) == 'B01') ? 'RNC' : 'Identificación'; ?>:</strong> <?php echo $factura->cliente_rnc; ?></div>
            <?php endif; ?>
            <?php if ($factura->tipo_factura == 'Credito'): ?>
                <div class="credito-nota"><strong>✳️ FACTURA DE CRÉDITO ✳️</strong><br><small>Pago pendiente</small></div>
            <?php endif; ?>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th>Cant</th>
                    <th>Precio</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = $items->fetch_object()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item->prod_name); ?></td>
                        <td><?php echo $item->prod_qty; ?></td>
                        <td>RD$ <?php echo number_format($item->prod_price, 2); ?></td>
                        <td>RD$ <?php echo number_format($item->total, 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="total-section">
            <div class="total-line"><span>Subtotal:</span><span>RD$ <?php echo number_format($factura->subtotal, 2); ?></span></div>
            <div class="total-line"><span>ITBIS (<?php echo $config->itebis_porcentaje; ?>%):</span><span>RD$ <?php echo number_format($factura->itebis, 2); ?></span></div>
            <div class="total-line"><span>Servicio (<?php echo $config->servicio_porcentaje; ?>%):</span><span>RD$ <?php echo number_format($factura->servicio, 2); ?></span></div>
            <?php if ($origen === 'Delivery'): ?>
                <div class="total-line"><span>Cargo de entrega:</span><span>RD$ <?php echo number_format($factura->cargo_entrega, 2); ?></span></div>
            <?php endif; ?>
            <div class="total-line total-final"><span>TOTAL:</span><span>RD$ <?php echo number_format($factura->total, 2); ?></span></div>
        </div>

        <div class="footer">
            <div>¡Gracias por su visita!</div>
            <div><?php echo date('d/m/Y H:i:s'); ?></div>
            <?php if ($factura->estado == 'Pendiente'): ?>
                <div style="color:red;font-weight:bold;">PENDIENTE DE PAGO</div>
            <?php endif; ?>
            <?php if ($origen === 'Delivery'): ?>
                <div style="margin-top:5px;font-style:italic;">Servicio de Delivery</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="no-print">
        <button onclick="window.print()" class="btn btn-primary">Imprimir Factura</button>
        <button onclick="window.close()" class="btn btn-secondary">Cerrar</button>
    </div>

    <?php if (isset($_SESSION['vuelto_info'])): ?>
        <div id="modalVuelto" style="position: fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:white;padding:20px;border:3px solid #28a745;border-radius:10px;z-index:10000;box-shadow:0 0 30px rgba(0,0,0,0.5);min-width:300px;text-align:center;">
            <h3 style="color:#28a745;margin-bottom:20px;"><i class="fas fa-check-circle"></i> Pago Realizado</h3>
            <div style="margin-bottom:15px;"><strong>Total Pagado:</strong><br><span style="font-size:18px;">RD$ <?php echo number_format($_SESSION['vuelto_info']['total'], 2); ?></span></div>
            <div style="margin-bottom:15px;"><strong>Monto Recibido:</strong><br><span style="font-size:18px;">RD$ <?php echo number_format($_SESSION['vuelto_info']['monto_recibido'], 2); ?></span></div>
            <div style="margin-bottom:20px;"><strong>Vuelto a Entregar:</strong><br><span style="font-size:24px;color:#28a745;font-weight:bold;">RD$ <?php echo number_format($_SESSION['vuelto_info']['vuelto'], 2); ?></span></div>
            <button onclick="document.getElementById('modalVuelto').style.display='none'" style="background:#28a745;color:white;border:none;padding:10px 20px;border-radius:5px;cursor:pointer;font-size:16px;">Aceptar</button>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('modalVuelto').style.display = 'block';
            });
        </script>
    <?php unset($_SESSION['vuelto_info']);
    endif; ?>
</body>
</html>