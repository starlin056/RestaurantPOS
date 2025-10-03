<?php
session_start();
include('../config/config.php');
include('../config/checklogin.php');
check_login();

if (!isset($_GET['delivery_id'])) {
    header("Location: ../factura/imprimir_factura.php");
    exit;
}

$delivery_id = $_GET['delivery_id'];

// Obtener información del delivery
$stmt = $mysqli->prepare("SELECT * FROM rpos_delivery_orders WHERE delivery_id = ?");
$stmt->bind_param('s', $delivery_id);
$stmt->execute();
$delivery = $stmt->get_result()->fetch_object();
$stmt->close();

if (!$delivery) die("Delivery no encontrado.");

// Obtener items del delivery
$stmt = $mysqli->prepare("SELECT * FROM rpos_delivery_items WHERE delivery_id = ?");
$stmt->bind_param('s', $delivery_id);
$stmt->execute();
$items = $stmt->get_result();
$stmt->close();

// Configuración empresa
$stmt_config = $mysqli->prepare("SELECT * FROM rpos_configuracion WHERE config_id = 1");
$stmt_config->execute();
$config = $stmt_config->get_result()->fetch_object();
$stmt_config->close();

// Obtener últimos 4 dígitos del order_code
$order_code = $delivery->order_code;
preg_match_all('/\d/', $order_code, $digitos);
$todos_digitos = implode('', $digitos[0]);
$ultimos_4_digitos = substr($todos_digitos, -4);
if (strlen($ultimos_4_digitos) < 4) {
    $ultimos_4_digitos = substr($order_code, -4);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prefactura Delivery #<?php echo $ultimos_4_digitos; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }

        .prefactura-container {
            max-width: 80mm;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
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

        .delivery-info,
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

        @media print {
            body {
                padding: 0;
                margin: 0;
            }

            .prefactura-container {
                width: 80mm;
            }
        }
    </style>
</head>

<body>
    <div class="prefactura-container">
        <div class="header">

            <?php
            $logo_path = $_SERVER['DOCUMENT_ROOT'] . $config->logo;
            if (!empty($config->logo) && file_exists($logo_path)): ?>
                <div class="text-center">
                    <img src="<?php echo $config->logo; ?>" alt="Logo" style="max-width: 120px; max-height: 60px; margin-bottom: 10px;">
                </div>
            <?php endif; ?>

            <div class="empresa-nombre"><?php echo $config->nombre_empresa; ?></div>
            <div class="empresa-info">RNC: <?php echo $config->rnc; ?></div>
            <div class="empresa-info"><?php echo $config->direccion; ?></div>
            <div class="empresa-info">Tel: <?php echo $config->telefono; ?></div>
        </div>

        <div class="delivery-info">
            <div class="empresa-nombre">PREFACTURA DELIVERY</div>
            <div><strong>Delivery #:</strong> <?php echo $ultimos_4_digitos; ?></div>
            <div><strong>Fecha:</strong> <?php echo date('d/m/Y H:i'); ?></div>
        </div>

        <div class="cliente-info">
            <div><strong>Cliente:</strong> <?php echo $delivery->customer_name; ?></div>
            <div><strong>Teléfono:</strong> <?php echo $delivery->customer_phone; ?></div>
            <div><strong>Dirección:</strong> <?php echo $delivery->customer_address; ?></div>
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
                        <td>RD$ <?php echo number_format($item->prod_price * $item->prod_qty, 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="total-section">
            <div class="total-line"><span>Subtotal:</span><span>RD$ <?php echo number_format($delivery->subtotal, 2); ?></span></div>
            <div class="total-line"><span>ITEBIS (<?php echo $config->itebis_porcentaje; ?>%):</span><span>RD$ <?php echo number_format($delivery->impuestos, 2); ?></span></div>
            <div class="total-line"><span>Servicio (<?php echo $config->servicio_porcentaje; ?>%):</span><span>RD$ <?php echo number_format($delivery->servicio, 2); ?></span></div>
            <div class="total-line"><span>Cargo entrega:</span><span>RD$ <?php echo number_format($delivery->cargo_entrega, 2); ?></span></div>
            <div class="total-line total-final"><span>TOTAL:</span><span>RD$ <?php echo number_format($delivery->total, 2); ?></span></div>
        </div>

        <div class="footer">
            <div>Prefactura - No válida como comprobante fiscal</div>
            <div><?php echo date('d/m/Y H:i:s'); ?></div>
        </div>
    </div>

    <div style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">Imprimir Prefactura</button>
        <button onclick="window.close()" style="background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin-left: 10px;">Cerrar</button>
    </div>
</body>

</html>