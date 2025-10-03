<?php
session_start();
include('../config/config.php');

// Verificar si se proporcionó un ID de delivery
if (!isset($_GET['delivery_id'])) {
    die('ID de delivery no proporcionado');
}

$delivery_id = $_GET['delivery_id'];

// Obtener información del pedido
$query = "SELECT d.*, c.*, s.staff_name as repartidor_nombre 
          FROM rpos_delivery_orders d 
          LEFT JOIN rpos_customers c ON d.cliente_id = c.customer_id 
          LEFT JOIN rpos_staff s ON d.repartidor_id = s.staff_id 
          WHERE d.delivery_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('s', $delivery_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Pedido no encontrado');
}

$pedido = $result->fetch_assoc();

// Obtener items del pedido
$items_query = $mysqli->prepare("SELECT * FROM rpos_delivery_items WHERE delivery_id = ?");
$items_query->bind_param('s', $delivery_id);
$items_query->execute();
$items_result = $items_query->get_result();
$items = $items_result->fetch_all(MYSQLI_ASSOC);

// Obtener configuración de la empresa
$config_query = $mysqli->query("SELECT * FROM rpos_configuracion LIMIT 1");
$config = $config_query->fetch_assoc();

// Incluir DomPDF desde vendor
require_once('../../../vendor/autoload.php');

use Dompdf\Dompdf;
use Dompdf\Options;

// Configurar opciones de PDF
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Arial');

$dompdf = new Dompdf($options);

// Crear HTML para el PDF
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Conduce de Delivery - ' . $pedido['order_code'] . '</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 20px; 
            font-size: 12px;
        }
        .header { 
            text-align: center; 
            margin-bottom: 15px; 
            border-bottom: 2px solid #333; 
            padding-bottom: 10px; 
        }
        .empresa-info { 
            margin-bottom: 10px; 
        }
        .empresa-info h2 {
            margin: 5px 0;
            font-size: 16px;
            color: #333;
        }
        .empresa-info p {
            margin: 3px 0;
            font-size: 11px;
        }
        .section { 
            margin-bottom: 15px; 
        }
        .section h3 { 
            background-color: #f2f2f2; 
            padding: 5px; 
            margin: 0 0 10px 0;
            font-size: 13px;
            border-bottom: 1px solid #ddd;
        }
        .table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 15px; 
            font-size: 11px;
        }
        .table th, .table td { 
            border: 1px solid #ddd; 
            padding: 6px; 
            text-align: left; 
        }
        .table th { 
            background-color: #f2f2f2; 
            font-weight: bold;
        }
        .no-border { 
            border: none !important; 
        }
        .no-border td { 
            border: none !important; 
            padding: 3px; 
        }
        .firma { 
            margin-top: 40px; 
            border-top: 1px solid #333; 
            padding-top: 10px; 
            font-size: 11px;
        }
        .text-center { 
            text-align: center; 
        }
        .text-right { 
            text-align: right; 
        }
        .conduce-info {
            font-size: 10px;
            color: #666;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="empresa-info">
            <h2>' . htmlspecialchars($config['nombre_empresa']) . '</h2>
            <p>RNC: ' . htmlspecialchars($config['rnc']) . ' | Tel: ' . htmlspecialchars($config['telefono']) . '</p>
            <p>' . htmlspecialchars($config['direccion']) . '</p>
        </div>
        <h1 style="margin: 10px 0; font-size: 18px;">CONDUCE DE DELIVERY</h1>
    </div>

    <div class="section">
        <h3>INFORMACIÓN DEL PEDIDO</h3>
        <table class="table no-border">
            <tr>
                <td width="50%"><strong>N° Orden:</strong> ' . htmlspecialchars($pedido['order_code']) . '</td>
                <td width="50%"><strong>Fecha:</strong> ' . date('d/m/Y H:i', strtotime($pedido['created_at'])) . '</td>
            </tr>
            <tr>
                <td><strong>Estado:</strong> ' . htmlspecialchars($pedido['estado']) . '</td>
                <td><strong>Repartidor:</strong> ' . htmlspecialchars($pedido['repartidor_nombre'] ?? 'Por asignar') . '</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>INFORMACIÓN DEL CLIENTE</h3>
        <table class="table no-border">
            <tr>
                <td width="50%"><strong>Nombre:</strong> ' . htmlspecialchars($pedido['customer_name']) . '</td>
                <td width="50%"><strong>Teléfono:</strong> ' . htmlspecialchars($pedido['customer_phone']) . '</td>
            </tr>
            <tr>
                <td colspan="2"><strong>Dirección:</strong> ' . htmlspecialchars($pedido['customer_address']) . '</td>
            </tr>';

if (!empty($pedido['ciudad']) || !empty($pedido['sector'])) {
    $html .= '
            <tr>
                <td colspan="2"><strong>Ubicación:</strong> ' . htmlspecialchars($pedido['ciudad'] . ' / ' . $pedido['sector']) . '</td>
            </tr>';
}

$html .= '
        </table>
    </div>

    <div class="section">
        <h3>PRODUCTOS SOLICITADOS</h3>
        <table class="table">
            <thead>
                <tr>
                    <th width="80%">Producto</th>
                    <th width="20%">Cantidad</th>
                </tr>
            </thead>
            <tbody>';

foreach ($items as $item) {
    $html .= '
                <tr>
                    <td>' . htmlspecialchars($item['prod_name']) . '</td>
                    <td>' . htmlspecialchars($item['prod_qty']) . '</td>
                </tr>';
}

$html .= '
            </tbody>
        </table>
    </div>';

if (!empty($pedido['notas'])) {
    $html .= '
    <div class="section">
        <h3>NOTAS ESPECIALES</h3>
        <p style="padding: 5px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 3px;">' . nl2br(htmlspecialchars($pedido['notas'])) . '</p>
    </div>';
}

$html .= '
    <div class="firma">
        <table width="100%">
            <tr>
                <td width="50%">
                    <p><strong>FIRMA DEL CLIENTE</strong></p>
                    <p>______________________________________________________</p>

                    <p>Nombre: _____________________________________________</p>
                </td>

                <td width="50%">
                    <p><strong>RECIBIDO POR</strong></p>

                    <p>Fecha: ________/_________/___________</p>

                    <p>Hora: _________:___________</p>

                </td>
            </tr>
        </table>
    </div>

    <div class="conduce-info text-center">

        <p>---------------------------------------------------</p>
        <p><em>Este documento es un comprobante de entrega para el repartidor</em></p>


        <p><strong>' . htmlspecialchars($config['nombre_empresa']) . '</strong> - ' . date('d/m/Y H:i') . '</p>

    </div>
</body>
</html>';

// Cargar HTML en DomPDF
$dompdf->loadHtml($html, 'UTF-8');

// Configurar papel y orientación
$dompdf->setPaper('A4', 'portrait');

// Renderizar PDF
$dompdf->render();

// Enviar PDF al navegador
$dompdf->stream(
    'conduce_delivery_' . $pedido['order_code'] . '.pdf', 
    array(
        'Attachment' => 0 // 0 para abrir en navegador, 1 para descargar
    )
);

// También puedes guardar el PDF en el servidor si lo deseas:
// $output = $dompdf->output();
// file_put_contents('conduces/conduce_' . $delivery_id . '.pdf', $output);
?>