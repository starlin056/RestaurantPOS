-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 21-10-2025 a las 23:28:17
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `rposystem`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria_tipo`
--

CREATE TABLE `categoria_tipo` (
  `nombre_categoria` varchar(200) NOT NULL,
  `tipo` enum('Comida','Bebida') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categoria_tipo`
--

INSERT INTO `categoria_tipo` (`nombre_categoria`, `tipo`) VALUES
('Bebidas', 'Bebida'),
('Comida', 'Comida'),
('Entradas', 'Comida'),
('Postres', 'Comida');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rpos_admin`
--

CREATE TABLE `rpos_admin` (
  `admin_id` varchar(200) NOT NULL,
  `admin_name` varchar(200) NOT NULL,
  `admin_email` varchar(200) NOT NULL,
  `admin_password` varchar(200) NOT NULL,
  `activation_key` varchar(200) DEFAULT NULL,
  `estado` enum('Activo','Inactivo') DEFAULT 'Inactivo',
  `activation_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rpos_admin`
--

INSERT INTO `rpos_admin` (`admin_id`, `admin_name`, `admin_email`, `admin_password`, `activation_key`, `estado`, `activation_expiry`) VALUES
('10e0b6dc958adfb5b094d8935a13aeadbe783c25', 'pedro ureña', 'admin@mail.com', '$2y$10$v4Sfq6EuL1ziXQI0oVkn7O5zTTn1d9ZZayVGJ.qS29MSow3qxgBoa', '125', 'Activo', '2026-09-07 18:50:50'),
('2ff3a06d33f5', 'miguel', 'miguel@gmail.com', '$2y$10$s4N732XO7oehucTTAMhx4elwImkB3yK1AnfTwDV/n8ipFwgHddpge', '1OAGUDZT7K42WHQR5L9YXNVF38CIJE', 'Inactivo', '2026-09-20 18:05:53'),
('d5bbd695a728', 'sdsdsd', 'ad123@gmail.com', '$2y$10$fYmkcRz2MIsiwGDzojMFNeBsM1XbE8Ofsh2z2JsWZPtvTzRZG3DH.', 'AX485OSNG1W7Y2T6IJBE9F3HPZUK0D', 'Inactivo', '2026-10-16 01:42:00'),
('d941d3ea2270', 'ejemplo1', 'ejemplo23@gmail.com', '$2y$10$yNtNylanseQDf8B.65u8h.AuBXKWUJYBZL3Vne.iOg67eFbMsFo6K', 'P30XIWAQTG95BYZRFNVEJ68HLO1SKU', 'Activo', '2026-09-07 23:15:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rpos_caja`
--

CREATE TABLE `rpos_caja` (
  `caja_id` varchar(200) NOT NULL,
  `usuario_id` varchar(200) NOT NULL,
  `fecha_apertura` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_cierre` timestamp NULL DEFAULT NULL,
  `monto_inicial` decimal(10,2) NOT NULL,
  `monto_final` decimal(10,2) DEFAULT NULL,
  `ventas_efectivo` decimal(10,2) DEFAULT 0.00,
  `ventas_tarjeta` decimal(10,2) DEFAULT 0.00,
  `ventas_transferencia` decimal(10,2) DEFAULT 0.00,
  `ventas_app` decimal(10,2) DEFAULT 0.00,
  `total_ventas` decimal(10,2) DEFAULT 0.00,
  `gastos` decimal(10,2) DEFAULT 0.00,
  `estado` enum('Abierta','Cerrada') NOT NULL DEFAULT 'Abierta',
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rpos_caja`
--

INSERT INTO `rpos_caja` (`caja_id`, `usuario_id`, `fecha_apertura`, `fecha_cierre`, `monto_inicial`, `monto_final`, `ventas_efectivo`, `ventas_tarjeta`, `ventas_transferencia`, `ventas_app`, `total_ventas`, `gastos`, `estado`, `observaciones`) VALUES
('CAJA68b8b2d5895a3', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', '2025-09-03 21:27:49', '2025-09-05 02:40:19', 50.00, 1094.48, 1044.48, 0.00, 0.00, 0.00, 1044.48, 0.00, 'Cerrada', ' | CIERRE: '),
('CAJA68bb0bc3288ee', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', '2025-09-05 16:11:47', '2025-09-05 16:24:34', 50.00, 498.00, 448.00, 0.00, 0.00, 0.00, 448.00, 0.00, 'Cerrada', ' | CIERRE: '),
('CAJA68bb0e2517a57', '7', '2025-09-05 16:21:57', '2025-09-05 16:22:07', 50.00, 50.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'Cerrada', ' | CIERRE: '),
('CAJA68bf94ca82c21', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', '2025-09-09 02:45:30', '2025-09-09 02:48:38', 100.00, 548.00, 448.00, 0.00, 0.00, 0.00, 448.00, 0.00, 'Cerrada', ' | CIERRE: 0'),
('CAJA68c35d9128578', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', '2025-09-11 23:38:57', '2025-09-11 23:39:57', 150.00, 150.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'Cerrada', ' | CIERRE: 0'),
('CAJA68c35de071e90', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', '2025-09-11 23:40:16', '2025-09-11 23:41:55', 150.00, 150.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'Cerrada', ' | CIERRE: 0'),
('CAJA68c35e4d62ac3', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', '2025-09-11 23:42:05', '2025-09-11 23:44:54', 150.00, 150.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'Cerrada', ' | CIERRE: 0'),
('CAJA68c35f0589ed4', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', '2025-09-11 23:45:09', '2025-09-11 23:48:30', 50.00, 50.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'Cerrada', ' | CIERRE: 0'),
('CAJA68c35fd4ac86e', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', '2025-09-11 23:48:36', '2025-09-11 23:49:35', 10.00, 10.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'Cerrada', ' | CIERRE: 0'),
('CAJA68c3601baf8ec', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', '2025-09-11 23:49:47', '2025-09-12 22:08:57', 100.00, 925.60, 825.60, 0.00, 0.00, 0.00, 825.60, 0.00, 'Cerrada', ' | CIERRE: 0'),
('CAJA68c4a68b7ed42', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', '2025-09-12 23:02:35', '2025-09-20 11:28:37', 50.00, 50.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'Cerrada', ' | CIERRE: 0'),
('CAJA68f7f9fd0f45c', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', '2025-10-21 21:24:13', NULL, 50.00, NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'Abierta', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rpos_categorias_productos`
--

CREATE TABLE `rpos_categorias_productos` (
  `categoria_id` int(11) NOT NULL,
  `nombre_categoria` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `created_at` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rpos_categorias_productos`
--

INSERT INTO `rpos_categorias_productos` (`categoria_id`, `nombre_categoria`, `descripcion`, `created_at`) VALUES
(1, 'Comida', 'Platos principales y acompañamientos', '2025-09-03 21:24:32.525893'),
(2, 'Bebidas', 'Bebidas alcohólicas y no alcohólicas', '2025-08-10 08:00:00.000000'),
(3, 'Postres', 'Dulces y postres', '2025-08-10 08:00:00.000000'),
(4, 'Entradas', 'Aperitivos y entradas', '2025-08-10 08:00:00.000000');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rpos_configuracion`
--

CREATE TABLE `rpos_configuracion` (
  `config_id` int(11) NOT NULL,
  `nombre_empresa` varchar(255) NOT NULL,
  `rnc` varchar(20) NOT NULL,
  `direccion` text NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `itebis_porcentaje` decimal(5,2) NOT NULL DEFAULT 18.00,
  `servicio_porcentaje` decimal(5,2) NOT NULL DEFAULT 10.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rpos_configuracion`
--

INSERT INTO `rpos_configuracion` (`config_id`, `nombre_empresa`, `rnc`, `direccion`, `telefono`, `logo`, `itebis_porcentaje`, `servicio_porcentaje`) VALUES
(1, 'RESTAURANT DE EJEMPLO', '132-65302-5', 'REPUBLICA DOMINICANA SANTO DOMINGOo', '8095445544', '/RestaurantPOS/Restro/admin/assets/img/logo_68bf8ba143dc19.08858978.png', 18.00, 10.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rpos_customers`
--

CREATE TABLE `rpos_customers` (
  `customer_id` varchar(200) NOT NULL,
  `customer_name` varchar(200) NOT NULL,
  `customer_phoneno` varchar(200) NOT NULL,
  `customer_email` varchar(200) NOT NULL,
  `customer_password` varchar(200) NOT NULL,
  `created_at` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  `tipo_cliente` enum('Persona Física','Empresa') DEFAULT 'Persona Física',
  `rnc_cedula` varchar(20) DEFAULT NULL,
  `direccion_fiscal` text DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `sector` varchar(100) DEFAULT NULL,
  `referencia` text DEFAULT NULL,
  `es_contribuyente` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rpos_customers`
--

INSERT INTO `rpos_customers` (`customer_id`, `customer_name`, `customer_phoneno`, `customer_email`, `customer_password`, `created_at`, `tipo_cliente`, `rnc_cedula`, `direccion_fiscal`, `ciudad`, `sector`, `referencia`, `es_contribuyente`) VALUES
('3856cd9e6581', 'ejemplo cliente2', '8499437780', 'ejemplo23@mail.com', 'a346bc80408d9b2a5063fd1bddb20e2d5586ec30', '2025-08-29 02:27:54.149973', 'Persona Física', '12345654444', '101 4TA ESQ, RAMON RODRIGUEZ, URB, ANTIGUA', 'LA ALTAGRACIA', 'punnta cana', 'punta cana', 1),
('38a1143f530b', 'ejemplo cliente', '8499437780', '8499437780@delivery.com', 'd7683e52af93b105a44fcef5bd668a77fafd49f9', '2025-09-05 16:16:42.336566', 'Persona Física', '12345654444', 'boulevar 1ro de noviembre punta cana', 'LA ALTAGRACIA', 'punnta cana', NULL, 0),
('65d09329214c', 'ejemplo cliente', '8499437780', 'admin@mail.com', 'a346bc80408d9b2a5063fd1bddb20e2d5586ec30', '2025-08-23 14:38:14.926716', 'Persona Física', '12345654444', '101 4TA ESQ, RAMON RODRIGUEZ, URB, ANTIGUA', 'LA ALTAGRACIA', 'punnta cana', 'punta cana', 0),
('88aa4ed53e26', 'VOCAUTO UH IMPOR Y SERVIS RSL', '8299437780', 'DRAMENDEZ@gmail.com', 'a346bc80408d9b2a5063fd1bddb20e2d5586ec30', '2025-08-27 20:40:41.352335', 'Empresa', '132-653025', 'boulevar 1ro de noviembre punta canadsd', 'Salvaleón de Higüey', 'punnta cana', 'punta canasdsdsd', 1),
('9279bceebc15', 'pedro starlin ureña', '8299437780', 'DRAsssMENDEZ@gmail.com', 'a9b0bbe0bb71324c81684ffc626723062dfb5892', '2025-09-11 23:58:52.463642', 'Empresa', '12345654444', 'boulevar 1ro de noviembre punta cana', 'Salvaleón de Higüey', 'punnta cana', 'feefefefefefefef', 1),
('eda859b66fb3', 'darlin bonilla', '8299437780', '8299437780@delivery.com', 'd7683e52af93b105a44fcef5bd668a77fafd49f9', '2025-09-05 20:19:55.109275', 'Persona Física', '', '101 4TA ESQ, RAMON RODRIGUEZ, URB, ANTIGUA\npunta cana', 'Higüey', 'puj', NULL, 0),
('fe6bb69bdd29', 'Cliente Genérico', '1020302055', 'brians@mail.com', 'a69681bcf334ae130217fea4505fd3c994f5683f', '2025-08-22 22:57:48.649269', 'Persona Física', '40223415321', 'boulevar 1ro de noviembre punta canadsd', 'Salvaleón de Higüey', 'punnta cana', 'punta canasdsdsd', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rpos_delivery_items`
--

CREATE TABLE `rpos_delivery_items` (
  `item_id` int(11) NOT NULL,
  `delivery_id` varchar(200) NOT NULL,
  `prod_id` varchar(200) NOT NULL,
  `prod_name` varchar(200) NOT NULL,
  `prod_price` decimal(10,2) NOT NULL,
  `prod_qty` int(11) NOT NULL DEFAULT 1,
  `notas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rpos_delivery_items`
--

INSERT INTO `rpos_delivery_items` (`item_id`, `delivery_id`, `prod_id`, `prod_name`, `prod_price`, `prod_qty`, `notas`) VALUES
(125, '336d9498a9ae', 'a287b155f9', 'Carbonara', 250.00, 1, NULL),
(126, '3021a18c1175', 'a287b155f9', 'Carbonara', 250.00, 1, NULL),
(127, '3021a18c1175', 'e2195f8190', 'Carbonara', 350.00, 1, NULL),
(128, '3021a18c1175', '5d66c79953', 'Cheese Curd', 650.00, 1, NULL),
(129, '3021a18c1175', '6ff923966b', 'cafe ejemplo', 35.00, 1, NULL),
(130, '3021a18c1175', '716b9e1c97', 'canada dry soda', 35.00, 1, NULL),
(131, '3021a18c1175', '43033bf27e', 'coca cola', 50.00, 1, NULL),
(132, 'b88bdaa3be75', '5d66c79953', 'Cheese Curd', 650.00, 1, NULL),
(133, 'b88bdaa3be75', 'ec18c5a4f0', 'Corn Dogs', 4.00, 1, NULL),
(134, 'b88bdaa3be75', 'e2195f8190', 'Carbonara', 350.00, 1, NULL),
(135, 'b88bdaa3be75', '716b9e1c97', 'canada dry soda', 35.00, 1, NULL),
(136, 'd8114a515fdc', '3d19e0bf27', 'Cincinnati Chili', 900.00, 1, NULL),
(137, 'c8f83f7517fb', '14c7b6370e', 'Reuben Sandwich', 8.00, 1, NULL),
(138, 'c8f83f7517fb', 'fa278c009b', 'Sandwich rd', 350.00, 1, NULL),
(139, 'c8f83f7517fb', '7ff7f41dca', 'jugo naturales', 75.00, 1, NULL),
(140, 'c8f83f7517fb', '716b9e1c97', 'canada dry soda', 35.00, 1, NULL),
(141, '8b772b429eb6', 'a287b155f9', 'Carbonara', 250.00, 1, NULL),
(142, '999f92bf06ad', 'ec18c5a4f0', 'Corn Dogs', 4.00, 1, NULL),
(143, '19783b6225d6', 'e769e274a3', 'Frappuccino', 3.00, 1, NULL),
(144, 'd3a4c3de2330', 'e2195f8190', 'Carbonara', 350.00, 1, NULL),
(145, 'd3a4c3de2330', '43033bf27e', 'coca cola', 50.00, 1, NULL),
(146, '5cfa6415412c', '43033bf27e', 'coca cola', 50.00, 2, NULL),
(147, '5cfa6415412c', 'e769e274a3', 'Frappuccino', 3.00, 1, NULL),
(148, '5cfa6415412c', 'bd200ef837', 'Turkish Coffee', 8.00, 1, NULL),
(149, '5b72997d357e', '2b976e49a0', 'Cheeseburger', 350.00, 1, NULL),
(150, 'c7a89f4aa284', '716b9e1c97', 'canada dry soda', 35.00, 1, NULL),
(151, 'c7a89f4aa284', '43033bf27e', 'coca cola', 50.00, 1, NULL),
(152, '125211443c61', 'e2195f8190', 'Carbonara', 350.00, 1, NULL),
(153, '36e3ac845ad5', 'ec18c5a4f0', 'Corn Dogs', 4.00, 1, NULL),
(154, '806c715d8a4f', '5d66c79953', 'Cheese Curd', 650.00, 1, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rpos_delivery_orders`
--

CREATE TABLE `rpos_delivery_orders` (
  `delivery_id` varchar(200) NOT NULL,
  `numero_control` varchar(20) DEFAULT NULL,
  `order_code` varchar(200) NOT NULL,
  `customer_name` varchar(200) NOT NULL,
  `customer_phone` varchar(200) NOT NULL,
  `customer_address` text NOT NULL,
  `customer_email` varchar(200) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `impuestos` decimal(10,2) NOT NULL DEFAULT 0.00,
  `servicio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cargo_entrega` decimal(10,2) DEFAULT 0.00,
  `descuento` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `estado` enum('Recibido','En preparación','En camino','Entregado','Cancelado','Pendiente','Listo para servir','Lista para facturar','Cerrada','Pagada') NOT NULL DEFAULT 'Recibido',
  `notas` text DEFAULT NULL,
  `repartidor` varchar(200) DEFAULT NULL,
  `metodo_pago` varchar(50) DEFAULT NULL,
  `aplicar_impuestos` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `itebis_porcentaje` decimal(5,2) DEFAULT 18.00,
  `servicio_porcentaje` decimal(5,2) DEFAULT 10.00,
  `facturado` tinyint(1) DEFAULT 0,
  `numero_factura` varchar(50) DEFAULT NULL,
  `tipo_factura` enum('Final','Fiscal') DEFAULT 'Final',
  `cliente_id` varchar(200) DEFAULT NULL,
  `fecha_entrega` timestamp NULL DEFAULT NULL,
  `repartidor_id` int(11) DEFAULT NULL,
  `fecha_cobro` timestamp NULL DEFAULT NULL,
  `fecha_pago` timestamp NULL DEFAULT NULL,
  `metodo_pago_cobro` varchar(50) DEFAULT NULL,
  `reimpresiones` int(11) NOT NULL DEFAULT 0,
  `factura_id` varchar(200) DEFAULT NULL,
  `caja_id` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rpos_delivery_orders`
--

INSERT INTO `rpos_delivery_orders` (`delivery_id`, `numero_control`, `order_code`, `customer_name`, `customer_phone`, `customer_address`, `customer_email`, `subtotal`, `impuestos`, `servicio`, `cargo_entrega`, `descuento`, `total`, `estado`, `notas`, `repartidor`, `metodo_pago`, `aplicar_impuestos`, `created_at`, `updated_at`, `itebis_porcentaje`, `servicio_porcentaje`, `facturado`, `numero_factura`, `tipo_factura`, `cliente_id`, `fecha_entrega`, `repartidor_id`, `fecha_cobro`, `fecha_pago`, `metodo_pago_cobro`, `reimpresiones`, `factura_id`, `caja_id`) VALUES
('125211443c61', NULL, 'DLV20250912035039126', 'pedro starlin ureña', '8299437780', 'boulevar 1ro de noviembre punta cana', NULL, 350.00, 63.00, 35.00, 100.00, 0.00, 548.00, 'Pagada', '', NULL, NULL, 0, '2025-09-12 01:50:39', '2025-09-12 01:53:59', 18.00, 10.00, 0, 'FACT-20250912035359', 'Final', '9279bceebc15', NULL, NULL, NULL, '2025-09-12 01:53:59', NULL, 0, 'FAC68c37d3720459', 'CAJA68c3601baf8ec'),
('19783b6225d6', NULL, 'DLV20250908021834873', 'ejemplo cliente', '8499437780', '101 4TA ESQ, RAMON RODRIGUEZ, URB, ANTIGUA', NULL, 3.00, 0.54, 0.30, 100.00, 0.00, 103.84, 'Pagada', '', NULL, NULL, 0, '2025-09-08 00:18:34', '2025-09-08 00:20:52', 18.00, 10.00, 0, 'FACT-20250908022052', 'Final', '65d09329214c', NULL, NULL, NULL, '2025-09-08 00:20:52', NULL, 0, 'FAC68be2164451df', 'CAJA68bb3de241e29'),
('3021a18c1175', NULL, 'DLV20250906200128966', 'VOCAUTO UH IMPOR Y SERVIS RSL', '8299437780', 'boulevar 1ro de noviembre punta canadsd', NULL, 1370.00, 246.60, 0.00, 150.00, 0.00, 1766.60, 'Pagada', '', NULL, NULL, 0, '2025-09-06 18:01:28', '2025-09-07 19:42:35', 18.00, 10.00, 0, NULL, 'Final', '88aa4ed53e26', NULL, 5, NULL, NULL, NULL, 0, NULL, 'CAJA68bb3de241e29'),
('336d9498a9ae', NULL, 'DLV20250906194035355', 'darlin bonilla', '8299437780', '101 4TA ESQ, RAMON RODRIGUEZ, URB, ANTIGUA\npunta cana', NULL, 250.00, 45.00, 25.00, 100.00, 0.00, 420.00, 'Pagada', '', NULL, NULL, 0, '2025-09-06 17:40:35', '2025-09-06 18:00:33', 18.00, 10.00, 0, NULL, 'Final', 'eda859b66fb3', NULL, 5, NULL, NULL, NULL, 0, NULL, 'CAJA68bb3de241e29'),
('36e3ac845ad5', NULL, 'DLV20250912041043874', 'VOCAUTO UH IMPOR Y SERVIS RSL', '8299437780', 'boulevar 1ro de noviembre punta canadsd', NULL, 4.00, 0.72, 0.40, 100.00, 0.00, 105.12, 'Pagada', '', NULL, NULL, 0, '2025-09-12 02:10:43', '2025-09-12 02:15:02', 18.00, 10.00, 0, 'FACT-20250912041502', 'Final', '88aa4ed53e26', NULL, NULL, NULL, '2025-09-12 02:15:02', NULL, 0, 'FAC68c38226542c3', 'CAJA68c3601baf8ec'),
('5b72997d357e', NULL, 'DLV20250909033548497', 'darlin bonilla', '8299437780', '101 4TA ESQ, RAMON RODRIGUEZ, URB, ANTIGUA\npunta cana', NULL, 350.00, 63.00, 35.00, 100.00, 0.00, 548.00, 'Pagada', '', NULL, NULL, 0, '2025-09-09 01:35:48', '2025-09-09 01:36:10', 18.00, 10.00, 0, 'FACT-20250909033610', 'Final', 'eda859b66fb3', NULL, NULL, NULL, '2025-09-09 01:36:10', NULL, 0, 'FAC68bf848a24607', 'CAJA68bb3de241e29'),
('5cfa6415412c', NULL, 'DLV20250909033155859', 'Cliente Genérico', '1020302055', 'boulevar 1ro de noviembre punta canadsd', NULL, 111.00, 19.98, 11.10, 100.00, 0.00, 242.08, 'Pagada', '', NULL, NULL, 0, '2025-09-09 01:31:55', '2025-09-09 01:33:51', 18.00, 10.00, 0, 'FACT-20250909033351', 'Final', 'fe6bb69bdd29', NULL, NULL, NULL, '2025-09-09 01:33:51', NULL, 0, 'FAC68bf83ffee07f', 'CAJA68bb3de241e29'),
('806c715d8a4f', NULL, 'DLV20250912042810254', 'ejemplo cliente', '8499437780', '101 4TA ESQ, RAMON RODRIGUEZ, URB, ANTIGUA', NULL, 650.00, 117.00, 65.00, 100.00, 0.00, 932.00, 'Pagada', '', NULL, NULL, 0, '2025-09-12 02:28:10', '2025-09-12 02:28:41', 18.00, 10.00, 0, 'FACT-20250912042841', 'Final', '65d09329214c', NULL, NULL, NULL, '2025-09-12 02:28:41', NULL, 0, 'FAC68c38559b7b84', 'CAJA68c3601baf8ec'),
('8b772b429eb6', NULL, 'DLV20250908000428165', 'darlin bonilla', '8299437780', '101 4TA ESQ, RAMON RODRIGUEZ, URB, ANTIGUA\npunta cana', NULL, 250.00, 45.00, 25.00, 100.00, 0.00, 420.00, 'Pagada', '', NULL, NULL, 0, '2025-09-07 22:04:28', '2025-09-08 00:15:34', 18.00, 10.00, 0, 'FACT-20250908021534', 'Final', 'eda859b66fb3', NULL, NULL, NULL, '2025-09-08 00:15:34', NULL, 0, 'FAC68be2026daeaf', 'CAJA68bb3de241e29'),
('999f92bf06ad', NULL, 'DLV20250908021641493', 'darlin bonilla', '8299437780', '101 4TA ESQ, RAMON RODRIGUEZ, URB, ANTIGUA\npunta cana', NULL, 4.00, 0.72, 0.40, 100.00, 0.00, 105.12, 'Pagada', '', NULL, NULL, 0, '2025-09-08 00:16:41', '2025-09-08 00:17:07', 18.00, 10.00, 0, 'FACT-20250908021707', 'Final', 'eda859b66fb3', NULL, NULL, NULL, '2025-09-08 00:17:07', NULL, 0, 'FAC68be20833a0db', 'CAJA68bb3de241e29'),
('b88bdaa3be75', NULL, 'DLV20250907214409765', 'VOCAUTO UH IMPOR Y SERVIS RSL', '8299437780', 'boulevar 1ro de noviembre punta canadsd', NULL, 1039.00, 0.00, 0.00, 100.00, 0.00, 1139.00, 'Pagada', '', NULL, NULL, 0, '2025-09-07 19:44:09', '2025-09-07 21:29:13', 18.00, 10.00, 0, NULL, 'Final', '88aa4ed53e26', NULL, 5, NULL, NULL, NULL, 0, NULL, 'CAJA68bb3de241e29'),
('c7a89f4aa284', NULL, 'DLV20250909044600198', 'darlin bonilla', '8299437780', '101 4TA ESQ, RAMON RODRIGUEZ, URB, ANTIGUA\npunta cana', NULL, 85.00, 15.30, 8.50, 100.00, 0.00, 208.80, 'Pagada', '', NULL, NULL, 0, '2025-09-09 02:46:00', '2025-09-09 02:46:39', 18.00, 10.00, 0, 'FACT-20250909044639', 'Final', 'eda859b66fb3', NULL, NULL, NULL, '2025-09-09 02:46:39', NULL, 0, 'FAC68bf950f20f5a', 'CAJA68bf94ca82c21'),
('c8f83f7517fb', NULL, 'DLV20250907233957495', 'ejemplo cliente2', '8499437780', '101 4TA ESQ, RAMON RODRIGUEZ, URB, ANTIGUA', NULL, 468.00, 84.24, 0.00, 100.00, 0.00, 652.24, 'Pagada', 'fvsvfasfcacqdc', NULL, NULL, 0, '2025-09-07 21:39:57', '2025-09-07 21:59:06', 18.00, 10.00, 0, NULL, 'Final', '3856cd9e6581', NULL, NULL, NULL, NULL, NULL, 0, NULL, 'CAJA68bb3de241e29'),
('d3a4c3de2330', NULL, 'DLV20250909030804300', 'darlin bonilla', '8299437780', '101 4TA ESQ, RAMON RODRIGUEZ, URB, ANTIGUA\npunta cana', NULL, 400.00, 72.00, 40.00, 100.00, 0.00, 612.00, 'Pagada', '', NULL, NULL, 0, '2025-09-09 01:08:04', '2025-09-09 01:08:26', 18.00, 10.00, 0, 'FACT-20250909030826', 'Final', 'eda859b66fb3', NULL, 5, NULL, '2025-09-09 01:08:26', NULL, 0, 'FAC68bf7e0ae8fd3', 'CAJA68bb3de241e29'),
('d8114a515fdc', NULL, 'DLV20250907233039102', 'VOCAUTO UH IMPOR Y SERVIS RSL', '8299437780', 'boulevar 1ro de noviembre punta canadsd', NULL, 900.00, 162.00, 0.00, 100.00, 0.00, 1162.00, 'Pagada', '', NULL, NULL, 0, '2025-09-07 21:30:39', '2025-09-07 21:32:11', 18.00, 10.00, 0, NULL, 'Final', '88aa4ed53e26', NULL, 5, NULL, NULL, NULL, 0, NULL, 'CAJA68bb3de241e29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rpos_estados_bar`
--

CREATE TABLE `rpos_estados_bar` (
  `estado_id` varchar(200) NOT NULL,
  `order_id` varchar(200) NOT NULL,
  `estado` enum('Pendiente','En preparación','Listo','Entregado') NOT NULL DEFAULT 'Pendiente',
  `bartender_asignado` varchar(200) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `updated_at` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rpos_estados_bar`
--

INSERT INTO `rpos_estados_bar` (`estado_id`, `order_id`, `estado`, `bartender_asignado`, `notas`, `updated_at`) VALUES
('0ff67d8335e1', '8d24cd296835', 'Listo', NULL, NULL, '2025-09-06 18:01:48.037886'),
('1b48e9c1f5cc', 'bfb18f4fd36b', 'Listo', NULL, NULL, '2025-09-08 00:18:40.961030'),
('40e03321cab9', '34aa5eed0a5e', 'Listo', NULL, NULL, '2025-09-06 18:01:46.059568'),
('5de46a5ebea5', 'ad5b204b3fb9', 'Listo', NULL, NULL, '2025-09-07 21:40:22.369175'),
('739b3bfddc64', 'cdb000705f71', 'Listo', NULL, NULL, '2025-09-09 01:32:32.396151'),
('a8affa6b03bb', 'da63b771b859', 'Listo', NULL, NULL, '2025-09-09 01:32:31.734135'),
('a8e634d64e30', '120587f6196c', 'Listo', NULL, NULL, '2025-09-07 19:44:36.427718'),
('b6d10fcaf146', '65e6c16a1885', 'Listo', NULL, NULL, '2025-09-06 18:01:47.054153'),
('d46beba29f34', 'a97dd56a94cf', 'Listo', NULL, NULL, '2025-09-09 02:46:07.826320'),
('da5e01bb3cd0', '934afe64738a', 'Listo', NULL, NULL, '2025-09-07 21:40:20.948704'),
('db5b77e1b20a', 'dd44b5858dc0', 'Listo', NULL, NULL, '2025-09-09 02:46:08.732114'),
('defa3c8999dd', 'b3677f60539f', 'Listo', NULL, NULL, '2025-09-09 01:32:32.797618'),
('e3f0081d73bc', '785957edabbe', 'Listo', NULL, NULL, '2025-09-09 01:08:11.657915');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rpos_estados_cocina`
--

CREATE TABLE `rpos_estados_cocina` (
  `estado_id` varchar(200) NOT NULL,
  `order_id` varchar(200) NOT NULL,
  `estado` enum('Pendiente','En preparación','Listo','Entregado') NOT NULL DEFAULT 'Pendiente',
  `tiempo_preparacion` int(11) DEFAULT NULL COMMENT 'Tiempo estimado en minutos',
  `chef_asignado` varchar(200) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `updated_at` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rpos_estados_cocina`
--

INSERT INTO `rpos_estados_cocina` (`estado_id`, `order_id`, `estado`, `tiempo_preparacion`, `chef_asignado`, `notas`, `updated_at`) VALUES
('100defb6d3d0', '91182fd7c4d1', 'Listo', NULL, NULL, NULL, '2025-09-07 19:44:42.617797'),
('163c0bea5af0', '61a832142831', 'Listo', NULL, NULL, NULL, '2025-09-12 01:50:49.185129'),
('30368bb612e1', 'ed0644f636cb', 'Listo', NULL, NULL, NULL, '2025-09-09 01:35:57.144936'),
('33492cced4f4', 'e324a704a884', 'Listo', NULL, NULL, NULL, '2025-09-06 18:01:39.692935'),
('34320c8dd947', '77cfe91bfbde', 'Listo', NULL, NULL, NULL, '2025-09-06 18:01:38.546099'),
('3756568fcb9e', '2bbec6c080fd', 'Listo', NULL, NULL, NULL, '2025-09-09 01:08:13.666564'),
('3b690dd60133', 'f4f6ae4d1a71', 'Listo', NULL, NULL, NULL, '2025-09-07 19:44:44.321237'),
('3c1c8c0ab039', 'a0d5a893e25c', 'Listo', NULL, NULL, NULL, '2025-09-07 21:40:26.620746'),
('4fd6b4ef12d3', '0b4ef73a8acd', 'Listo', NULL, NULL, NULL, '2025-09-12 02:28:16.979876'),
('61963754be44', '1c559416b123', 'Listo', NULL, NULL, NULL, '2025-09-06 18:01:37.470261'),
('a51683a45e37', '690887f83be1', 'Listo', NULL, NULL, NULL, '2025-09-07 21:40:25.265012'),
('bc2119d122bb', '3564c2a58573', 'Listo', NULL, NULL, NULL, '2025-09-08 00:16:49.047005'),
('c812c6c9e0f9', '684fb78060d8', 'Listo', NULL, NULL, NULL, '2025-09-06 17:40:47.011407'),
('d9cd08d73478', '659c89686f2a', 'Listo', NULL, NULL, NULL, '2025-09-12 02:10:51.215955'),
('db958d2d17c9', '2233c5f8b2b3', 'Listo', NULL, NULL, NULL, '2025-09-07 19:44:40.875711'),
('e95f9dbe5f9b', 'c3c3f46802d6', 'Listo', NULL, NULL, NULL, '2025-09-07 21:30:47.331363'),
('f614a3fd879c', 'c0a5d0a2a1b5', 'Listo', NULL, NULL, NULL, '2025-09-07 22:04:34.869657');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rpos_facturas`
--

CREATE TABLE `rpos_facturas` (
  `factura_id` varchar(200) NOT NULL,
  `factura_code` varchar(50) NOT NULL,
  `mesa_id` varchar(200) NOT NULL,
  `delivery_id` varchar(200) DEFAULT NULL,
  `numero_mesa` int(11) NOT NULL,
  `cliente_nombre` varchar(200) DEFAULT NULL,
  `cliente_rnc` varchar(20) DEFAULT NULL,
  `ncf` varchar(20) DEFAULT NULL,
  `comprobante_fiscal` varchar(50) DEFAULT NULL,
  `tipo_factura` enum('Final','Fiscal','Credito') NOT NULL DEFAULT 'Final',
  `estado` enum('Pagada','Pendiente') NOT NULL DEFAULT 'Pagada',
  `subtotal` decimal(10,2) NOT NULL,
  `itebis` decimal(10,2) NOT NULL,
  `servicio` decimal(10,2) NOT NULL,
  `cargo_entrega` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `fecha_factura` timestamp NOT NULL DEFAULT current_timestamp(),
  `usuario_id` varchar(200) NOT NULL,
  `cajero_id` varchar(200) DEFAULT NULL,
  `mesero_id` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rpos_facturas`
--

INSERT INTO `rpos_facturas` (`factura_id`, `factura_code`, `mesa_id`, `delivery_id`, `numero_mesa`, `cliente_nombre`, `cliente_rnc`, `ncf`, `comprobante_fiscal`, `tipo_factura`, `estado`, `subtotal`, `itebis`, `servicio`, `cargo_entrega`, `total`, `fecha_factura`, `usuario_id`, `cajero_id`, `mesero_id`) VALUES
('FAC68bb8f4ed468d', 'FACT-20250906033302', 'm001', NULL, 1, 'VOCAUTO UH IMPOR Y SERVIS RSL', '132-653025', 'B0100000026', NULL, 'Fiscal', 'Pagada', 200.00, 36.00, 20.00, 0.00, 256.00, '2025-09-06 01:33:02', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', NULL, '10e0b6dc958adfb5b094d8935a13aeadbe783c25'),
('FAC68bc611d135c5', 'FACT-20250906182813', 'm002', NULL, 2, 'Consumidor Final', 'N/A', 'B0200000074', NULL, 'Final', 'Pagada', 250.00, 45.00, 25.00, 0.00, 320.00, '2025-09-06 16:28:13', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', NULL, '10e0b6dc958adfb5b094d8935a13aeadbe783c25'),
('FAC68bc7a58c3fe7', 'FACT-20250906201552', 'm002', NULL, 2, 'VOCAUTO UH IMPOR Y SERVIS RSL', '132-653025', 'B0100000034', NULL, 'Fiscal', 'Pagada', 35.00, 6.30, 3.50, 0.00, 44.80, '2025-09-06 18:15:52', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', NULL, NULL),
('FAC68bc7e4378370', 'FACT-20250906203235', 'DELIVERY', NULL, 0, '0', '132', 'B0100000035', NULL, 'Fiscal', 'Pagada', 1370.00, 246.60, 0.00, 150.00, 1766.60, '2025-09-06 18:32:35', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', NULL, NULL),
('FAC68bde5940a373', 'FACT-20250907220540', 'm002', NULL, 2, 'VOCAUTO UH IMPOR Y SERVIS RSL', '132-653025', 'B0100000038', NULL, 'Fiscal', 'Pagada', 35.00, 6.30, 3.50, 0.00, 44.80, '2025-09-07 20:05:40', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', NULL, '10e0b6dc958adfb5b094d8935a13aeadbe783c25'),
('FAC68bdf8b336a93', 'FACT-20250907232715', 'DELIVERY', 'b88bdaa3be75', 0, 'VOCAUTO UH IMPOR Y SERVIS RSL', '132', 'B0100000041', NULL, 'Fiscal', 'Pagada', 1039.00, 0.00, 0.00, 100.00, 1139.00, '2025-09-07 21:27:15', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', NULL, NULL),
('FAC68bdf9b2af417', 'FACT-20250907233130', 'DELIVERY', 'd8114a515fdc', 0, 'VOCAUTO UH IMPOR Y SERVIS RSL', '132', 'B0100000042', NULL, 'Fiscal', 'Pagada', 900.00, 162.00, 0.00, 100.00, 1162.00, '2025-09-07 21:31:30', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', NULL, NULL),
('FAC68bdfbfdf06c0', 'FACT-20250907234117', 'DELIVERY', 'c8f83f7517fb', 0, 'VOCAUTO UH IMPOR Y SERVIS RSL', '132', 'B0100000043', NULL, 'Fiscal', 'Pagada', 468.00, 84.24, 0.00, 100.00, 652.24, '2025-09-07 21:41:17', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', NULL, NULL),
('FAC68be2026daeaf', 'FACT-20250908021534', 'DELIVERY', '8b772b429eb6', 0, 'VOCAUTO UH IMPOR Y SERVIS RSL', '132-653025', 'B0100000059', NULL, 'Fiscal', 'Pagada', 250.00, 45.00, 25.00, 100.00, 420.00, '2025-09-08 00:15:34', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', NULL, NULL),
('FAC68be20833a0db', 'FACT-20250908021707', 'DELIVERY', '999f92bf06ad', 0, 'VOCAUTO UH IMPOR Y SERVIS RSL', '132-653025', 'B0100000060', NULL, 'Fiscal', 'Pagada', 4.00, 0.72, 0.40, 100.00, 105.12, '2025-09-08 00:17:07', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', NULL, NULL),
('FAC68be20f09b086', 'FACT-20250908021856', 'DELIVERY', '19783b6225d6', 0, 'ejemplo cliente', 'N/A', 'B0200000086', NULL, 'Final', 'Pagada', 3.00, 0.54, 0.30, 100.00, 103.84, '2025-09-08 00:18:56', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', NULL, NULL),
('FAC68be2164451df', 'FACT-20250908022052', 'DELIVERY', '19783b6225d6', 0, 'ejemplo cliente', 'N/A', 'B0200000087', NULL, 'Final', 'Pagada', 3.00, 0.54, 0.30, 100.00, 103.84, '2025-09-08 00:20:52', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', NULL, NULL),
('FAC68bf7a2723c8c', 'FACT-20250909025151', 'm002', NULL, 2, 'Consumidor Final', 'N/A', 'B0200000088', NULL, 'Final', 'Pagada', 35.00, 6.30, 3.50, 0.00, 44.80, '2025-09-09 00:51:51', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', NULL, '4'),
('FAC68bf7bc8d195e', 'FACT-20250909025848', 'm002', NULL, 2, 'Consumidor Final', 'N/A', 'B0200000089', NULL, 'Final', 'Pagada', 350.00, 63.00, 35.00, 0.00, 448.00, '2025-09-09 00:58:48', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', NULL, NULL),
('FAC68bf7e0ae8fd3', 'FACT-20250909030826', 'DELIVERY', 'd3a4c3de2330', 0, 'darlin bonilla', 'N/A', 'B0200000090', NULL, 'Final', 'Pagada', 400.00, 72.00, 40.00, 100.00, 612.00, '2025-09-09 01:08:26', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', NULL, NULL),
('FAC68bf806e61bdf', 'FACT-20250909031838', 'm002', NULL, 2, 'Consumidor Final', 'N/A', 'B0200000091', NULL, 'Final', 'Pagada', 210.00, 37.80, 21.00, 0.00, 268.80, '2025-09-09 01:18:38', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', NULL, NULL),
('FAC68bf8157a0767', 'FACT-20250909032231', 'm002', NULL, 2, 'Consumidor Final', 'N/A', 'B0200000092', NULL, 'Final', 'Pagada', 410.00, 73.80, 41.00, 0.00, 524.80, '2025-09-09 01:22:31', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', NULL, '4'),
('FAC68bf83ce319cd', 'FACT-20250909033302', '689a7bb5ba475', NULL, 7, 'Consumidor Final', 'N/A', 'B0200000093', NULL, 'Final', 'Pagada', 916.00, 164.88, 91.60, 0.00, 1172.48, '2025-09-09 01:33:02', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', NULL, '4'),
('FAC68bf83ffee07f', 'FACT-20250909033351', 'DELIVERY', '5cfa6415412c', 0, 'Cliente Genérico', 'N/A', 'B0200000094', NULL, 'Final', 'Pagada', 111.00, 19.98, 11.10, 100.00, 242.08, '2025-09-09 01:33:51', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', NULL, NULL),
('FAC68bf848a24607', 'FACT-20250909033610', 'DELIVERY', '5b72997d357e', 0, 'darlin bonilla', 'N/A', 'B0200000095', NULL, 'Final', 'Pagada', 350.00, 63.00, 35.00, 100.00, 548.00, '2025-09-09 01:36:10', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', NULL, NULL),
('FAC68bf8bf34a453', 'FACT-20250909040747', 'm002', NULL, 2, 'Consumidor Final', 'N/A', 'B0200000096', NULL, 'Final', 'Pagada', 650.00, 117.00, 65.00, 0.00, 832.00, '2025-09-09 02:07:47', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', NULL, '10e0b6dc958adfb5b094d8935a13aeadbe783c25'),
('FAC68bf94fa336f3', 'FACT-20250909044618', 'm002', NULL, 2, 'Consumidor Final', 'N/A', 'B0200000097', NULL, 'Final', 'Pagada', 350.00, 63.00, 35.00, 0.00, 448.00, '2025-09-09 02:46:18', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', NULL, '10e0b6dc958adfb5b094d8935a13aeadbe783c25'),
('FAC68bf950f20f5a', 'FACT-20250909044639', 'DELIVERY', 'c7a89f4aa284', 0, 'darlin bonilla', 'N/A', 'B0200000098', NULL, 'Final', 'Pagada', 85.00, 15.30, 8.50, 100.00, 208.80, '2025-09-09 02:46:39', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', NULL, NULL),
('FAC68c37c55351d4', 'FACT-20250912035013', 'm002', NULL, 2, 'Consumidor Final', 'N/A', 'B0200000099', NULL, 'Final', 'Pagada', 35.00, 6.30, 3.50, 0.00, 44.80, '2025-09-12 01:50:13', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', NULL, '10e0b6dc958adfb5b094d8935a13aeadbe783c25'),
('FAC68c37d3720459', 'FACT-20250912035359', 'DELIVERY', '125211443c61', 0, 'pedro starlin ureña', 'N/A', 'B0200000100', NULL, 'Final', 'Pagada', 350.00, 63.00, 35.00, 100.00, 548.00, '2025-09-12 01:53:59', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', NULL, NULL),
('FAC68c38226542c3', 'FACT-20250912041502', 'DELIVERY', '36e3ac845ad5', 0, 'VOCAUTO UH IMPOR Y SERVIS RSL', 'N/A', '', NULL, 'Final', 'Pagada', 4.00, 0.72, 0.40, 100.00, 105.12, '2025-09-12 02:15:02', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', NULL, NULL),
('FAC68c38559b7b84', 'FACT-20250912042841', 'DELIVERY', '806c715d8a4f', 0, 'ejemplo cliente', 'N/A', 'B0200000101', NULL, 'Final', 'Pagada', 650.00, 117.00, 65.00, 100.00, 932.00, '2025-09-12 02:28:41', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', NULL, NULL),
('FAC68c3887d1ae33', 'FACT-20250912044205', 'm002', NULL, 2, 'Consumidor Final', 'N/A', 'B0200000102', NULL, 'Final', 'Pagada', 250.00, 45.00, 25.00, 0.00, 320.00, '2025-09-12 02:42:05', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', NULL, '10e0b6dc958adfb5b094d8935a13aeadbe783c25'),
('FAC68c3888565d5d', 'FACT-20250912044213', '68a7cf9e5168c', NULL, 3, 'Consumidor Final', 'N/A', 'B0200000103', NULL, 'Final', 'Pagada', 10.00, 1.80, 1.00, 0.00, 12.80, '2025-09-12 02:42:13', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', NULL, '10e0b6dc958adfb5b094d8935a13aeadbe783c25'),
('FAC68c49858d685d', 'FACT-20250913000200', 'm004', NULL, 4, 'Consumidor Final', 'N/A', 'B0200000104', NULL, 'Final', 'Pagada', 350.00, 63.00, 35.00, 0.00, 448.00, '2025-09-12 22:02:00', '10e0b6dc958adfb5b094d8935a13aeadbe783c25', NULL, '10e0b6dc958adfb5b094d8935a13aeadbe783c25');

--
-- Disparadores `rpos_facturas`
--
DELIMITER $$
CREATE TRIGGER `after_factura_insert` AFTER INSERT ON `rpos_facturas` FOR EACH ROW BEGIN
    IF NEW.mesa_id = 'DELIVERY' AND NEW.delivery_id IS NOT NULL THEN
        UPDATE rpos_delivery_orders 
        SET estado = 'Pagada', 
            factura_id = NEW.factura_id,
            numero_factura = NEW.factura_code,
            fecha_pago = NOW()
        WHERE delivery_id = NEW.delivery_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rpos_factura_items`
--

CREATE TABLE `rpos_factura_items` (
  `factura_item_id` varchar(200) NOT NULL,
  `factura_id` varchar(200) NOT NULL,
  `product_id` varchar(200) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `product_qty` decimal(10,2) NOT NULL,
  `product_total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rpos_factura_items`
--

INSERT INTO `rpos_factura_items` (`factura_item_id`, `factura_id`, `product_id`, `product_name`, `product_price`, `product_qty`, `product_total`) VALUES
('FITM68bc7e4378c55', 'FAC68bc7e4378370', 'a287b155f9', 'Carbonara', 250.00, 1.00, 250.00),
('FITM68bc7e437a230', 'FAC68bc7e4378370', 'e2195f8190', 'Carbonara', 350.00, 1.00, 350.00),
('FITM68bc7e437bb22', 'FAC68bc7e4378370', '5d66c79953', 'Cheese Curd', 650.00, 1.00, 650.00),
('FITM68bc7e437c0cd', 'FAC68bc7e4378370', '6ff923966b', 'cafe ejemplo', 35.00, 1.00, 35.00),
('FITM68bc7e437cfbc', 'FAC68bc7e4378370', '716b9e1c97', 'canada dry soda', 35.00, 1.00, 35.00),
('FITM68bc7e437e06e', 'FAC68bc7e4378370', '43033bf27e', 'coca cola', 50.00, 1.00, 50.00),
('FITM68bdf8b337d42', 'FAC68bdf8b336a93', '5d66c79953', 'Cheese Curd', 650.00, 1.00, 650.00),
('FITM68bdf8b33af80', 'FAC68bdf8b336a93', 'ec18c5a4f0', 'Corn Dogs', 4.00, 1.00, 4.00),
('FITM68bdf8b33bbfa', 'FAC68bdf8b336a93', 'e2195f8190', 'Carbonara', 350.00, 1.00, 350.00),
('FITM68bdf8b33db58', 'FAC68bdf8b336a93', '716b9e1c97', 'canada dry soda', 35.00, 1.00, 35.00),
('FITM68bdf9b2b04f3', 'FAC68bdf9b2af417', '3d19e0bf27', 'Cincinnati Chili', 900.00, 1.00, 900.00),
('FITM68bdfbfdf2d3e', 'FAC68bdfbfdf06c0', '14c7b6370e', 'Reuben Sandwich', 8.00, 1.00, 8.00),
('FITM68bdfbfe0148f', 'FAC68bdfbfdf06c0', 'fa278c009b', 'Sandwich rd', 350.00, 1.00, 350.00),
('FITM68bdfbfe02432', 'FAC68bdfbfdf06c0', '7ff7f41dca', 'jugo naturales', 75.00, 1.00, 75.00),
('FITM68bdfbfe02ffe', 'FAC68bdfbfdf06c0', '716b9e1c97', 'canada dry soda', 35.00, 1.00, 35.00),
('FITM68be2026dba9c', 'FAC68be2026daeaf', 'a287b155f9', 'Carbonara', 250.00, 1.00, 250.00),
('FITM68be20833ab1b', 'FAC68be20833a0db', 'ec18c5a4f0', 'Corn Dogs', 4.00, 1.00, 4.00),
('FITM68be20f09c04d', 'FAC68be20f09b086', 'e769e274a3', 'Frappuccino', 3.00, 1.00, 3.00),
('FITM68be216445ae7', 'FAC68be2164451df', 'e769e274a3', 'Frappuccino', 3.00, 1.00, 3.00),
('FITM68bf7e0ae988c', 'FAC68bf7e0ae8fd3', 'e2195f8190', 'Carbonara', 350.00, 1.00, 350.00),
('FITM68bf7e0aea1b0', 'FAC68bf7e0ae8fd3', '43033bf27e', 'coca cola', 50.00, 1.00, 50.00),
('FITM68bf806e64515', 'FAC68bf806e61bdf', 'a419f2ef1c', 'Chicken Nugget', 200.00, 1.00, 200.00),
('FITM68bf806e662a9', 'FAC68bf806e61bdf', '3adfdee116', 'Enchiladas', 10.00, 1.00, 10.00),
('FITM68bf8157a21eb', 'FAC68bf8157a0767', '3adfdee116', 'Enchiladas', 10.00, 1.00, 10.00),
('FITM68bf8157a2d87', 'FAC68bf8157a0767', 'f4ce3927bf', 'Hot Dog', 400.00, 1.00, 400.00),
('FITM68bf83ce334e3', 'FAC68bf83ce319cd', '3d19e0bf27', 'Cincinnati Chili', 900.00, 1.00, 900.00),
('FITM68bf83ce33ba2', 'FAC68bf83ce319cd', 'd9aed17627', 'Crab Cake', 16.00, 1.00, 16.00),
('FITM68bf83ffeea9e', 'FAC68bf83ffee07f', '43033bf27e', 'coca cola', 50.00, 2.00, 100.00),
('FITM68bf83ffef20d', 'FAC68bf83ffee07f', 'e769e274a3', 'Frappuccino', 3.00, 1.00, 3.00),
('FITM68bf83ffef935', 'FAC68bf83ffee07f', 'bd200ef837', 'Turkish Coffee', 8.00, 1.00, 8.00),
('FITM68bf848a24eca', 'FAC68bf848a24607', '2b976e49a0', 'Cheeseburger', 350.00, 1.00, 350.00),
('FITM68bf8bf34bd77', 'FAC68bf8bf34a453', '5d66c79953', 'Cheese Curd', 650.00, 1.00, 650.00),
('FITM68bf94fa35bb6', 'FAC68bf94fa336f3', 'e2195f8190', 'Carbonara', 350.00, 1.00, 350.00),
('FITM68bf950f2180b', 'FAC68bf950f20f5a', '716b9e1c97', 'canada dry soda', 35.00, 1.00, 35.00),
('FITM68bf950f23766', 'FAC68bf950f20f5a', '43033bf27e', 'coca cola', 50.00, 1.00, 50.00),
('FITM68c37c55366e6', 'FAC68c37c55351d4', '6ff923966b', 'cafe ejemplo', 35.00, 1.00, 35.00),
('FITM68c37d37211a2', 'FAC68c37d3720459', 'e2195f8190', 'Carbonara', 350.00, 1.00, 350.00),
('FITM68c38226549fd', 'FAC68c38226542c3', 'ec18c5a4f0', 'Corn Dogs', 4.00, 1.00, 4.00),
('FITM68c38559b84c6', 'FAC68c38559b7b84', '5d66c79953', 'Cheese Curd', 650.00, 1.00, 650.00),
('FITM68c3887d1be07', 'FAC68c3887d1ae33', 'a287b155f9', 'Carbonara', 250.00, 1.00, 250.00),
('FITM68c3888566ca8', 'FAC68c3888565d5d', 'd57cd89073', 'Country Fried Steak', 10.00, 1.00, 10.00),
('FITM68c49858d8d3e', 'FAC68c49858d685d', 'e2195f8190', 'Carbonara', 350.00, 1.00, 350.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rpos_mesas`
--

CREATE TABLE `rpos_mesas` (
  `mesa_id` varchar(200) NOT NULL,
  `numero_mesa` int(11) NOT NULL,
  `capacidad` int(11) NOT NULL,
  `ubicacion` varchar(200) NOT NULL,
  `estado` enum('Disponible','Ocupada','Reservada','Mantenimiento','En preparación','Listo para servir','Lista para facturar','Cerrada') NOT NULL DEFAULT 'Disponible',
  `created_at` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  `num_personas` int(11) DEFAULT NULL,
  `mesero_asignado` varchar(200) DEFAULT NULL,
  `notas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rpos_mesas`
--

INSERT INTO `rpos_mesas` (`mesa_id`, `numero_mesa`, `capacidad`, `ubicacion`, `estado`, `created_at`, `num_personas`, `mesero_asignado`, `notas`) VALUES
('689a69f11cf7e', 6, 15, 'Interior', 'Disponible', '2025-09-05 01:08:53.398960', NULL, NULL, NULL),
('689a7bb5ba475', 7, 10, 'Interior', 'Disponible', '2025-09-09 01:33:02.235322', NULL, NULL, '0'),
('68a7cf9e5168c', 3, 5, 'Interior', 'Disponible', '2025-09-12 02:42:13.434694', NULL, NULL, NULL),
('68b4e963b8a5a', 8, 5, 'Sala VIP', 'Disponible', '2025-09-01 01:18:48.911653', NULL, NULL, NULL),
('DELIVERY', 0, 0, '', 'Disponible', '2025-09-04 02:52:51.214412', NULL, NULL, NULL),
('m001', 1, 5, 'Terraza', 'Ocupada', '2025-10-21 21:23:48.096815', NULL, '10e0b6dc958adfb5b094d8935a13aeadbe783c25', '0'),
('m002', 2, 6, 'Interior', 'Disponible', '2025-09-20 14:37:15.184715', NULL, NULL, ''),
('m004', 4, 8, 'Sala VIP', 'Disponible', '2025-09-12 22:02:00.927044', NULL, NULL, NULL),
('m005', 5, 4, 'Interior', 'Disponible', '2025-08-31 20:45:39.816390', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rpos_movimientos_caja`
--

CREATE TABLE `rpos_movimientos_caja` (
  `movimiento_id` varchar(200) NOT NULL,
  `caja_id` varchar(200) NOT NULL,
  `tipo` enum('Ingreso','Egreso') NOT NULL,
  `concepto` varchar(200) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `metodo_pago` varchar(50) NOT NULL,
  `referencia` varchar(200) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_movimiento` timestamp NOT NULL DEFAULT current_timestamp(),
  `usuario_id` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rpos_movimientos_log`
--

CREATE TABLE `rpos_movimientos_log` (
  `id` int(11) NOT NULL,
  `usuario_id` varchar(50) NOT NULL,
  `accion` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `ip_usuario` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rpos_movimientos_log`
--

INSERT INTO `rpos_movimientos_log` (`id`, `usuario_id`, `accion`, `descripcion`, `ip_usuario`, `user_agent`, `fecha`) VALUES
(41, '10e0b6dc958adfb5b094d8935a13aeadbe783c25', 'Abrir caja', 'Usuario abrió la caja número 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-03 21:27:45'),
(42, '10e0b6dc958adfb5b094d8935a13aeadbe783c25', 'Abrir caja', 'Usuario abrió la caja número 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-03 22:33:24'),
(43, '10e0b6dc958adfb5b094d8935a13aeadbe783c25', 'Cerrar caja', 'Usuario cerro la caja.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-05 02:40:07'),
(44, '10e0b6dc958adfb5b094d8935a13aeadbe783c25', 'Abrir caja', 'Usuario abrió la caja número 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-05 16:11:44'),
(45, '10e0b6dc958adfb5b094d8935a13aeadbe783c25', 'Abrir caja', 'Usuario abrió la caja número 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-05 16:13:28'),
(46, '7', 'Abrir caja', 'Usuario abrió la caja número 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-05 16:21:54'),
(47, '7', 'Cerrar caja', 'Usuario cerro la caja.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-05 16:21:58'),
(48, '10e0b6dc958adfb5b094d8935a13aeadbe783c25', 'Cerrar caja', 'Usuario cerro la caja.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-05 16:24:27'),
(49, '10e0b6dc958adfb5b094d8935a13aeadbe783c25', 'Abrir caja', 'Usuario abrió la caja número 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-05 19:45:34'),
(50, '10e0b6dc958adfb5b094d8935a13aeadbe783c25', 'Cerrar caja', 'Usuario cerro la caja.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-05 22:26:59'),
(51, '10e0b6dc958adfb5b094d8935a13aeadbe783c25', 'Cerrar caja', 'Usuario cerro la caja.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-06 01:35:26'),
(52, '10e0b6dc958adfb5b094d8935a13aeadbe783c25', 'Cerrar caja', 'Usuario cerro la caja.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-09 02:39:30'),
(53, '10e0b6dc958adfb5b094d8935a13aeadbe783c25', 'Cerrar caja', 'Usuario cerro la caja.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-09 02:40:41'),
(54, '10e0b6dc958adfb5b094d8935a13aeadbe783c25', 'Cerrar caja', 'Usuario cerro la caja.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-09 02:40:42'),
(55, '10e0b6dc958adfb5b094d8935a13aeadbe783c25', 'Abrir caja', 'Usuario abrió la caja número 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-09 02:45:27'),
(56, '10e0b6dc958adfb5b094d8935a13aeadbe783c25', 'Abrir caja', 'Usuario abrió la caja número 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-09 02:45:33'),
(57, '10e0b6dc958adfb5b094d8935a13aeadbe783c25', 'Cerrar caja', 'Usuario cerro la caja.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-09 02:48:17'),
(58, '10e0b6dc958adfb5b094d8935a13aeadbe783c25', 'Abrir caja', 'Usuario abrió la caja número 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-11 23:38:53'),
(59, '10e0b6dc958adfb5b094d8935a13aeadbe783c25', 'Cerrar caja', 'Usuario cerro la caja.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-11 23:39:51'),
(60, '10e0b6dc958adfb5b094d8935a13aeadbe783c25', 'Abrir caja', 'Usuario abrió la caja número 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-11 23:40:12'),
(61, '10e0b6dc958adfb5b094d8935a13aeadbe783c25', 'Abrir caja', 'Usuario abrió la caja número 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-11 23:41:47'),
(62, '10e0b6dc958adfb5b094d8935a13aeadbe783c25', 'Cerrar caja', 'Usuario cerro la caja.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-11 23:41:52'),
(63, '10e0b6dc958adfb5b094d8935a13aeadbe783c25', 'Abrir caja', 'Usuario abrió la caja número 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-11 23:42:01'),
(64, '10e0b6dc958adfb5b094d8935a13aeadbe783c25', 'Abrir caja', 'Usuario abrió la caja número 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-11 23:44:45'),
(65, '10e0b6dc958adfb5b094d8935a13aeadbe783c25', 'Abrir caja', 'Usuario abrió la caja número 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-11 23:44:45'),
(66, '10e0b6dc958adfb5b094d8935a13aeadbe783c25', 'Cerrar caja', 'Usuario cerro la caja.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-11 23:44:47'),
(67, '10e0b6dc958adfb5b094d8935a13aeadbe783c25', 'Abrir caja', 'Usuario abrió la caja número 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-11 23:45:06'),
(68, '10e0b6dc958adfb5b094d8935a13aeadbe783c25', 'Abrir caja', 'Usuario abrió la caja número 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-11 23:48:18'),
(69, '10e0b6dc958adfb5b094d8935a13aeadbe783c25', 'Abrir caja', 'Usuario abrió la caja número 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-11 23:48:20'),
(70, '10e0b6dc958adfb5b094d8935a13aeadbe783c25', 'Cerrar caja', 'Usuario cerro la caja.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-11 23:48:26'),
(71, '10e0b6dc958adfb5b094d8935a13aeadbe783c25', 'Abrir caja', 'Usuario abrió la caja número 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-11 23:48:33'),
(72, '10e0b6dc958adfb5b094d8935a13aeadbe783c25', 'Cerrar caja', 'Usuario cerro la caja.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-11 23:49:32'),
(73, '10e0b6dc958adfb5b094d8935a13aeadbe783c25', 'Abrir caja', 'Usuario abrió la caja número 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-11 23:49:37'),
(74, '10e0b6dc958adfb5b094d8935a13aeadbe783c25', 'Cerrar caja', 'Usuario cerro la caja.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-09-12 22:08:49'),
(75, '10e0b6dc958adfb5b094d8935a13aeadbe783c25', 'Abrir caja', 'Usuario abrió la caja número 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-09-12 23:02:31'),
(76, '10e0b6dc958adfb5b094d8935a13aeadbe783c25', 'Cerrar caja', 'Usuario cerro la caja.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-09-20 11:28:31'),
(77, '10e0b6dc958adfb5b094d8935a13aeadbe783c25', 'Abrir caja', 'Usuario abrió la caja número 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-10-21 21:24:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rpos_notificaciones`
--

CREATE TABLE `rpos_notificaciones` (
  `notificacion_id` varchar(200) NOT NULL,
  `mesa_id` varchar(200) DEFAULT NULL,
  `order_id` varchar(200) DEFAULT NULL,
  `delivery_id` varchar(200) DEFAULT NULL,
  `mensaje` text NOT NULL,
  `tipo` enum('mesa_lista','nuevo_pedido','ayuda') NOT NULL,
  `tipo_pedido` enum('mesa','delivery') DEFAULT NULL,
  `estado` enum('pendiente','vista','atendida') NOT NULL DEFAULT 'pendiente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `vista_at` timestamp NULL DEFAULT NULL,
  `atendida_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rpos_notificaciones`
--

INSERT INTO `rpos_notificaciones` (`notificacion_id`, `mesa_id`, `order_id`, `delivery_id`, `mensaje`, `tipo`, `tipo_pedido`, `estado`, `created_at`, `vista_at`, `atendida_at`) VALUES
('68c37a6e53651', 'm002', '68c37a67cfc26', '', 'Mesa #2: cafe ejemplo listo para servir', 'nuevo_pedido', 'mesa', 'atendida', '2025-09-12 01:42:06', '2025-09-12 01:48:27', '2025-09-12 02:37:13'),
('68c38540f032c', NULL, '0b4ef73a8acd', '806c715d8a4f', 'Delivery #806c715d8a4f: Cheese Curd listo para entrega', 'nuevo_pedido', 'delivery', 'atendida', '2025-09-12 02:28:16', '2025-09-12 02:37:14', '2025-09-12 02:47:05'),
('68c387a70e351', 'm002', '68c3879fcdf06', NULL, 'Mesa #2: Carbonara listo para servir', 'nuevo_pedido', 'mesa', 'atendida', '2025-09-12 02:38:31', '2025-09-12 02:47:06', '2025-09-12 02:47:19'),
('68c38856d7d4b', '68a7cf9e5168c', '68c38853328fb', NULL, 'Mesa #3: Country Fried Steak listo para servir', 'nuevo_pedido', 'mesa', 'atendida', '2025-09-12 02:41:26', '2025-09-12 02:47:06', '2025-09-12 02:47:19'),
('68c389c925424', 'm004', '68c389c50633c', NULL, 'Mesa #4: Carbonara listo para servir', 'nuevo_pedido', 'mesa', 'atendida', '2025-09-12 02:47:37', '2025-09-12 23:07:48', '2025-09-12 23:07:53'),
('68f7f9ea493ef', 'm001', '68f7f9e41390e', NULL, 'Mesa #1: arepa listo para servir', 'nuevo_pedido', 'mesa', 'pendiente', '2025-10-21 21:23:54', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rpos_ordenes_mesas`
--

CREATE TABLE `rpos_ordenes_mesas` (
  `orden_mesa_id` varchar(200) NOT NULL,
  `order_id` varchar(200) NOT NULL,
  `mesa_id` varchar(200) NOT NULL,
  `estado` enum('Disponible','Ocupada','Reservada','Mantenimiento','En preparación','Listo para servir','Lista para facturar','Cerrada','Activa','Facturada') NOT NULL DEFAULT 'Activa',
  `created_at` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  `notas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rpos_ordenes_mesas`
--

INSERT INTO `rpos_ordenes_mesas` (`orden_mesa_id`, `order_id`, `mesa_id`, `estado`, `created_at`, `notas`) VALUES
('68bb87b463606', '68bb87b463021', 'm001', 'Facturada', '2025-09-06 01:33:02.893595', NULL),
('68bc60ff1cd07', '68bc60ff1c3dc', 'm002', 'Facturada', '2025-09-06 16:28:13.104667', NULL),
('68bc61031aaaa', '68bc6103199a5', 'm002', 'Facturada', '2025-09-06 18:15:52.821330', NULL),
('68bde57c5edb1', '68bde57c5c514', 'm002', 'Facturada', '2025-09-07 20:05:40.089739', NULL),
('68bf7805ebf42', '68bf7805e9ffd', 'm002', 'Facturada', '2025-09-09 00:51:51.168070', NULL),
('68bf780a53c8c', '68bf780a52f41', 'm002', 'Facturada', '2025-09-09 00:58:48.878654', NULL),
('68bf7ba2bbb58', '68bf7ba2bad3b', 'm002', 'Facturada', '2025-09-09 01:18:38.436881', NULL),
('68bf7e8d2036f', '68bf7e8d1fcf3', 'm002', 'Facturada', '2025-09-09 01:18:38.444375', NULL),
('68bf812a54d85', '68bf812a541c9', 'm002', 'Facturada', '2025-09-09 01:22:31.683012', NULL),
('68bf812f8ac22', '68bf812f89558', 'm002', 'Facturada', '2025-09-09 01:22:31.689000', NULL),
('68bf83a1deedd', '68bf83a1de821', '689a7bb5ba475', 'Facturada', '2025-09-09 01:33:02.226136', NULL),
('68bf83a7196b7', '68bf83a71862f', '689a7bb5ba475', 'Facturada', '2025-09-09 01:33:02.231691', NULL),
('68bf8bdfe5867', '68bf8bdfe50fe', 'm002', 'Facturada', '2025-09-09 02:07:47.325132', NULL),
('68bf94d6814fc', '68bf94d680ac7', 'm002', 'Facturada', '2025-09-09 02:46:18.236658', NULL),
('68c37a67d0841', '68c37a67cfc26', 'm002', 'Facturada', '2025-09-12 01:50:13.233183', NULL),
('68c3879fce7a2', '68c3879fcdf06', 'm002', 'Facturada', '2025-09-12 02:42:05.126679', NULL),
('68c3885333272', '68c38853328fb', '68a7cf9e5168c', 'Facturada', '2025-09-12 02:42:13.433020', NULL),
('68c389c506b41', '68c389c50633c', 'm004', 'Facturada', '2025-09-12 22:02:00.922971', NULL),
('68f7f9e416070', '68f7f9e41390e', 'm001', 'Activa', '2025-10-21 21:23:48.090986', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rpos_orders`
--

CREATE TABLE `rpos_orders` (
  `order_id` varchar(200) NOT NULL,
  `order_code` varchar(200) NOT NULL,
  `customer_id` varchar(200) NOT NULL,
  `customer_name` varchar(200) NOT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `customer_address` text DEFAULT NULL,
  `prod_id` varchar(200) NOT NULL,
  `prod_name` varchar(200) NOT NULL,
  `prod_price` varchar(200) NOT NULL,
  `prod_qty` varchar(200) NOT NULL,
  `order_status` enum('Pendiente','En preparación','Listo','Entregado','Cancelado','Facturada') NOT NULL DEFAULT 'Pendiente',
  `created_at` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  `notas` text DEFAULT NULL,
  `mesa_id` varchar(50) DEFAULT NULL,
  `created_by` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rpos_orders`
--

INSERT INTO `rpos_orders` (`order_id`, `order_code`, `customer_id`, `customer_name`, `customer_phone`, `customer_address`, `prod_id`, `prod_name`, `prod_price`, `prod_qty`, `order_status`, `created_at`, `notas`, `mesa_id`, `created_by`) VALUES
('0b4ef73a8acd', 'DLV20250912042810254', '65d09329214c', 'ejemplo cliente', NULL, NULL, '5d66c79953', 'Cheese Curd', '650', '1', 'Listo', '2025-09-12 02:28:16.976670', NULL, NULL, 'delivery_system'),
('120587f6196c', 'DLV20250907214409765', '88aa4ed53e26', 'VOCAUTO UH IMPOR Y SERVIS RSL', NULL, NULL, '716b9e1c97', 'canada dry soda', '35', '1', 'Listo', '2025-09-07 19:44:36.424314', NULL, NULL, 'delivery_system'),
('1c559416b123', 'DLV20250906200128966', '88aa4ed53e26', 'VOCAUTO UH IMPOR Y SERVIS RSL', NULL, NULL, '5d66c79953', 'Cheese Curd', '650', '1', 'Listo', '2025-09-06 18:01:37.465973', NULL, NULL, 'delivery_system'),
('2233c5f8b2b3', 'DLV20250907214409765', '88aa4ed53e26', 'VOCAUTO UH IMPOR Y SERVIS RSL', NULL, NULL, 'e2195f8190', 'Carbonara', '350', '1', 'Listo', '2025-09-07 19:44:40.872935', NULL, NULL, 'delivery_system'),
('2bbec6c080fd', 'DLV20250909030804300', 'eda859b66fb3', 'darlin bonilla', NULL, NULL, 'e2195f8190', 'Carbonara', '350', '1', 'Listo', '2025-09-09 01:08:13.663206', NULL, NULL, 'delivery_system'),
('34aa5eed0a5e', 'DLV20250906200128966', '88aa4ed53e26', 'VOCAUTO UH IMPOR Y SERVIS RSL', NULL, NULL, '43033bf27e', 'coca cola', '50', '1', 'Listo', '2025-09-06 18:01:46.057785', NULL, NULL, 'delivery_system'),
('3564c2a58573', 'DLV20250908021641493', 'eda859b66fb3', 'darlin bonilla', NULL, NULL, 'ec18c5a4f0', 'Corn Dogs', '4', '1', 'Listo', '2025-09-08 00:16:49.044968', NULL, NULL, 'delivery_system'),
('61a832142831', 'DLV20250912035039126', '9279bceebc15', 'pedro starlin ureña', NULL, NULL, 'e2195f8190', 'Carbonara', '350', '1', 'Listo', '2025-09-12 01:50:49.182270', NULL, NULL, 'delivery_system'),
('659c89686f2a', 'DLV20250912041043874', '88aa4ed53e26', 'VOCAUTO UH IMPOR Y SERVIS RSL', NULL, NULL, 'ec18c5a4f0', 'Corn Dogs', '4', '1', 'Listo', '2025-09-12 02:10:51.214202', NULL, NULL, 'delivery_system'),
('65e6c16a1885', 'DLV20250906200128966', '88aa4ed53e26', 'VOCAUTO UH IMPOR Y SERVIS RSL', NULL, NULL, '716b9e1c97', 'canada dry soda', '35', '1', 'Listo', '2025-09-06 18:01:47.051663', NULL, NULL, 'delivery_system'),
('684fb78060d8', 'DLV20250906194035355', 'eda859b66fb3', 'darlin bonilla', NULL, NULL, 'a287b155f9', 'Carbonara', '250', '1', 'Listo', '2025-09-06 17:40:47.008382', NULL, NULL, 'delivery_system'),
('68bb87b463021', 'F0B29B84', 'fe6bb69bdd29', 'Mesa m001', NULL, NULL, 'a419f2ef1c', 'Chicken Nugget', '200', '1', 'Facturada', '2025-09-06 01:33:02.890064', '', NULL, ''),
('68bc60ff1c3dc', 'C9094773', 'fe6bb69bdd29', 'Mesa m002', NULL, NULL, 'a287b155f9', 'Carbonara', '250', '1', 'Facturada', '2025-09-06 16:28:13.101323', '', NULL, ''),
('68bc6103199a5', '0EBEB562', 'fe6bb69bdd29', 'Mesa m002', NULL, NULL, '716b9e1c97', 'canada dry soda', '35', '1', 'Facturada', '2025-09-06 18:15:52.819303', '', NULL, ''),
('68bde57c5c514', '62096E69', 'fe6bb69bdd29', 'Mesa m002', NULL, NULL, '6ff923966b', 'cafe ejemplo', '35', '1', 'Facturada', '2025-09-07 20:05:40.086604', '', NULL, ''),
('68bf7805e9ffd', 'EBB5E69B', 'fe6bb69bdd29', 'Mesa m002', NULL, NULL, '6ff923966b', 'cafe ejemplo', '35', '1', 'Facturada', '2025-09-09 00:51:51.162921', '', NULL, ''),
('68bf780a52f41', 'B250A058', 'fe6bb69bdd29', 'Mesa m002', NULL, NULL, 'e2195f8190', 'Carbonara', '350', '1', 'Facturada', '2025-09-09 00:58:48.875322', '', NULL, ''),
('68bf7ba2bad3b', '0C41C8A2', 'fe6bb69bdd29', 'Mesa m002', NULL, NULL, 'a419f2ef1c', 'Chicken Nugget', '200', '1', 'Facturada', '2025-09-09 01:18:38.433427', '', NULL, ''),
('68bf7e8d1fcf3', '90E7B45D', 'fe6bb69bdd29', 'Mesa m002', NULL, NULL, '3adfdee116', 'Enchiladas', '10', '1', 'Facturada', '2025-09-09 01:18:38.440440', '', NULL, ''),
('68bf812a541c9', '89D2FD20', 'fe6bb69bdd29', 'Mesa m002', NULL, NULL, '3adfdee116', 'Enchiladas', '10', '1', 'Facturada', '2025-09-09 01:22:31.680922', '', NULL, ''),
('68bf812f89558', '76202FF5', 'fe6bb69bdd29', 'Mesa m002', NULL, NULL, 'f4ce3927bf', 'Hot Dog', '400', '1', 'Facturada', '2025-09-09 01:22:31.686887', '', NULL, ''),
('68bf83a1de821', '67BB6D87', 'fe6bb69bdd29', 'Mesa 689a7bb5ba475', NULL, NULL, '3d19e0bf27', 'Cincinnati Chili', '900', '1', 'Facturada', '2025-09-09 01:33:02.224141', '', NULL, ''),
('68bf83a71862f', 'B88F88D5', 'fe6bb69bdd29', 'Mesa 689a7bb5ba475', NULL, NULL, 'd9aed17627', 'Crab Cake', '16', '1', 'Facturada', '2025-09-09 01:33:02.229568', '', NULL, ''),
('68bf8bdfe50fe', '167F768B', 'fe6bb69bdd29', 'Mesa m002', NULL, NULL, '5d66c79953', 'Cheese Curd', '650', '1', 'Facturada', '2025-09-09 02:07:47.321830', '', NULL, ''),
('68bf94d680ac7', '3F1B1EC9', 'fe6bb69bdd29', 'Mesa m002', NULL, NULL, 'e2195f8190', 'Carbonara', '350', '1', 'Facturada', '2025-09-09 02:46:18.232574', '', NULL, ''),
('68c37a67cfc26', 'FF1168A0', 'fe6bb69bdd29', 'Mesa m002', NULL, NULL, '6ff923966b', 'cafe ejemplo', '35', '1', 'Facturada', '2025-09-12 01:50:13.230327', '', NULL, ''),
('68c3879fcdf06', '241E3BAC', 'fe6bb69bdd29', 'Mesa m002', NULL, NULL, 'a287b155f9', 'Carbonara', '250', '1', 'Facturada', '2025-09-12 02:42:05.124343', '', NULL, ''),
('68c38853328fb', '4A5370AF', 'fe6bb69bdd29', 'Mesa 68a7cf9e5168c', NULL, NULL, 'd57cd89073', 'Country Fried Steak', '10', '1', 'Facturada', '2025-09-12 02:42:13.431046', '', NULL, ''),
('68c389c50633c', 'BE5E4138', 'fe6bb69bdd29', 'Mesa m004', NULL, NULL, 'e2195f8190', 'Carbonara', '350', '1', 'Facturada', '2025-09-12 22:02:00.919048', '', NULL, ''),
('68f7f9e41390e', '97E8E84A', 'fe6bb69bdd29', 'Mesa m001', NULL, NULL, 'd99f541ed9', 'arepa', '75', '1', 'Listo', '2025-10-21 21:23:54.296398', '', NULL, ''),
('690887f83be1', 'DLV20250907233957495', '3856cd9e6581', 'ejemplo cliente2', NULL, NULL, '14c7b6370e', 'Reuben Sandwich', '8', '1', 'Listo', '2025-09-07 21:40:25.263066', NULL, NULL, 'delivery_system'),
('77cfe91bfbde', 'DLV20250906200128966', '88aa4ed53e26', 'VOCAUTO UH IMPOR Y SERVIS RSL', NULL, NULL, 'a287b155f9', 'Carbonara', '250', '1', 'Listo', '2025-09-06 18:01:38.542619', NULL, NULL, 'delivery_system'),
('785957edabbe', 'DLV20250909030804300', 'eda859b66fb3', 'darlin bonilla', NULL, NULL, '43033bf27e', 'coca cola', '50', '1', 'Listo', '2025-09-09 01:08:11.656400', NULL, NULL, 'delivery_system'),
('8d24cd296835', 'DLV20250906200128966', '88aa4ed53e26', 'VOCAUTO UH IMPOR Y SERVIS RSL', NULL, NULL, '6ff923966b', 'cafe ejemplo', '35', '1', 'Listo', '2025-09-06 18:01:48.035546', NULL, NULL, 'delivery_system'),
('91182fd7c4d1', 'DLV20250907214409765', '88aa4ed53e26', 'VOCAUTO UH IMPOR Y SERVIS RSL', NULL, NULL, 'ec18c5a4f0', 'Corn Dogs', '4', '1', 'Listo', '2025-09-07 19:44:42.614473', NULL, NULL, 'delivery_system'),
('934afe64738a', 'DLV20250907233957495', '3856cd9e6581', 'ejemplo cliente2', NULL, NULL, '7ff7f41dca', 'jugo naturales', '75', '1', 'Listo', '2025-09-07 21:40:20.945983', NULL, NULL, 'delivery_system'),
('a0055842f6f6', 'DLV20250906182903516', 'eda859b66fb3', 'darlin bonilla', NULL, NULL, 'a287b155f9', 'Carbonara', '250', '1', 'Listo', '2025-09-06 16:29:12.625277', NULL, NULL, 'delivery_system'),
('a0d5a893e25c', 'DLV20250907233957495', '3856cd9e6581', 'ejemplo cliente2', NULL, NULL, 'fa278c009b', 'Sandwich rd', '350', '1', 'Listo', '2025-09-07 21:40:26.619255', NULL, NULL, 'delivery_system'),
('a97dd56a94cf', 'DLV20250909044600198', 'eda859b66fb3', 'darlin bonilla', NULL, NULL, '43033bf27e', 'coca cola', '50', '1', 'Listo', '2025-09-09 02:46:07.807704', NULL, NULL, 'delivery_system'),
('ad5b204b3fb9', 'DLV20250907233957495', '3856cd9e6581', 'ejemplo cliente2', NULL, NULL, '716b9e1c97', 'canada dry soda', '35', '1', 'Listo', '2025-09-07 21:40:22.367063', NULL, NULL, 'delivery_system'),
('b3677f60539f', 'DLV20250909033155859', 'fe6bb69bdd29', 'Cliente Genérico', NULL, NULL, 'e769e274a3', 'Frappuccino', '3', '1', 'Listo', '2025-09-09 01:32:32.795342', NULL, NULL, 'delivery_system'),
('bfb18f4fd36b', 'DLV20250908021834873', '65d09329214c', 'ejemplo cliente', NULL, NULL, 'e769e274a3', 'Frappuccino', '3', '1', 'Listo', '2025-09-08 00:18:40.956461', NULL, NULL, 'delivery_system'),
('c0a5d0a2a1b5', 'DLV20250908000428165', 'eda859b66fb3', 'darlin bonilla', NULL, NULL, 'a287b155f9', 'Carbonara', '250', '1', 'Listo', '2025-09-07 22:04:34.867612', NULL, NULL, 'delivery_system'),
('c3c3f46802d6', 'DLV20250907233039102', '88aa4ed53e26', 'VOCAUTO UH IMPOR Y SERVIS RSL', NULL, NULL, '3d19e0bf27', 'Cincinnati Chili', '900', '1', 'Listo', '2025-09-07 21:30:47.328881', NULL, NULL, 'delivery_system'),
('cdb000705f71', 'DLV20250909033155859', 'fe6bb69bdd29', 'Cliente Genérico', NULL, NULL, 'bd200ef837', 'Turkish Coffee', '8', '1', 'Listo', '2025-09-09 01:32:32.394230', NULL, NULL, 'delivery_system'),
('da63b771b859', 'DLV20250909033155859', 'fe6bb69bdd29', 'Cliente Genérico', NULL, NULL, '43033bf27e', 'coca cola', '50', '2', 'Listo', '2025-09-09 01:32:31.731887', NULL, NULL, 'delivery_system'),
('dd44b5858dc0', 'DLV20250909044600198', 'eda859b66fb3', 'darlin bonilla', NULL, NULL, '716b9e1c97', 'canada dry soda', '35', '1', 'Listo', '2025-09-09 02:46:08.728503', NULL, NULL, 'delivery_system'),
('e324a704a884', 'DLV20250906200128966', '88aa4ed53e26', 'VOCAUTO UH IMPOR Y SERVIS RSL', NULL, NULL, 'e2195f8190', 'Carbonara', '350', '1', 'Listo', '2025-09-06 18:01:39.690512', NULL, NULL, 'delivery_system'),
('e86fb2e88e51', 'DLV20250906191401282', 'eda859b66fb3', 'darlin bonilla', NULL, NULL, 'e2195f8190', 'Carbonara', '350', '1', 'Listo', '2025-09-06 17:14:08.987679', NULL, NULL, 'delivery_system'),
('ed0644f636cb', 'DLV20250909033548497', 'eda859b66fb3', 'darlin bonilla', NULL, NULL, '2b976e49a0', 'Cheeseburger', '350', '1', 'Listo', '2025-09-09 01:35:57.143347', NULL, NULL, 'delivery_system'),
('f4f6ae4d1a71', 'DLV20250907214409765', '88aa4ed53e26', 'VOCAUTO UH IMPOR Y SERVIS RSL', NULL, NULL, '5d66c79953', 'Cheese Curd', '650', '1', 'Listo', '2025-09-07 19:44:44.318033', NULL, NULL, 'delivery_system');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rpos_order_items`
--

CREATE TABLE `rpos_order_items` (
  `item_id` int(11) NOT NULL,
  `order_id` varchar(200) NOT NULL,
  `prod_id` varchar(200) NOT NULL,
  `prod_name` varchar(200) NOT NULL,
  `prod_price` decimal(10,2) NOT NULL,
  `prod_qty` int(11) NOT NULL DEFAULT 1,
  `notas` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rpos_pass_resets`
--

CREATE TABLE `rpos_pass_resets` (
  `reset_id` int(20) NOT NULL,
  `reset_code` varchar(200) NOT NULL,
  `reset_token` varchar(200) NOT NULL,
  `reset_email` varchar(200) NOT NULL,
  `reset_status` varchar(200) NOT NULL,
  `created_at` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rpos_pass_resets`
--

INSERT INTO `rpos_pass_resets` (`reset_id`, `reset_code`, `reset_token`, `reset_email`, `reset_status`, `created_at`) VALUES
(3, 'ab8df572', '646c2cebdf73fead4d0091cf46c47b8ad2dbb10d', 'admin@mail.com', 'Pending', '2025-08-09 23:27:58.558734');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rpos_payments`
--

CREATE TABLE `rpos_payments` (
  `pay_id` varchar(200) NOT NULL,
  `pay_code` varchar(200) NOT NULL,
  `order_code` varchar(200) NOT NULL,
  `customer_id` varchar(200) NOT NULL,
  `pay_amt` varchar(200) NOT NULL,
  `pay_method` varchar(200) NOT NULL,
  `created_at` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rpos_payments`
--

INSERT INTO `rpos_payments` (`pay_id`, `pay_code`, `order_code`, `customer_id`, `pay_amt`, `pay_method`, `created_at`) VALUES
('PAY68bb8f4ed5b19', 'PAY-20250906033302', 'FACT-20250906033302', '88aa4ed53e26', '256', 'Efectivo', '2025-09-06 01:33:02.875723'),
('PAY68bc611d15720', 'PAY-20250906182813', 'FACT-20250906182813', 'consumidor_final', '320', 'Efectivo', '2025-09-06 16:28:13.088477'),
('PAY68bc7a58c5d4b', 'PAY-20250906201552', 'FACT-20250906201552', '88aa4ed53e26', '44.8', 'Efectivo', '2025-09-06 18:15:52.810418'),
('PAY68bde5940ddc4', 'PAY-20250907220540', 'FACT-20250907220540', '88aa4ed53e26', '44.8', 'Efectivo', '2025-09-07 20:05:40.057329'),
('PAY68bf7a2725c49', 'PAY-20250909025151', 'FACT-20250909025151', 'consumidor_final', '44.8', 'Efectivo', '2025-09-09 00:51:51.155352'),
('PAY68bf7bc8d3088', 'PAY-20250909025848', 'FACT-20250909025848', 'consumidor_final', '448', 'Efectivo', '2025-09-09 00:58:48.864970'),
('PAY68bf806e66d87', 'PAY-20250909031838', 'FACT-20250909031838', 'consumidor_final', '268.8', 'Efectivo', '2025-09-09 01:18:38.421823'),
('PAY68bf8157a39a7', 'PAY-20250909032231', 'FACT-20250909032231', 'consumidor_final', '524.8', 'Efectivo', '2025-09-09 01:22:31.670692'),
('PAY68bf83ce34841', 'PAY-20250909033302', 'FACT-20250909033302', 'consumidor_final', '1172.48', 'Efectivo', '2025-09-09 01:33:02.215369'),
('PAY68bf8bf34c683', 'PAY-20250909040747', 'FACT-20250909040747', 'consumidor_final', '832', 'Efectivo', '2025-09-09 02:07:47.313044'),
('PAY68bf94fa367cb', 'PAY-20250909044618', 'FACT-20250909044618', 'consumidor_final', '448', 'Efectivo', '2025-09-09 02:46:18.223921'),
('PAY68c37c5537357', 'PAY-20250912035013', 'FACT-20250912035013', 'consumidor_final', '44.8', 'Efectivo', '2025-09-12 01:50:13.226400'),
('PAY68c3887d1c9fe', 'PAY-20250912044205', 'FACT-20250912044205', 'consumidor_final', '320', 'Efectivo', '2025-09-12 02:42:05.117434'),
('PAY68c388856720b', 'PAY-20250912044213', 'FACT-20250912044213', 'consumidor_final', '12.8', 'Efectivo', '2025-09-12 02:42:13.422480'),
('PAY68c49858da30a', 'PAY-20250913000200', 'FACT-20250913000200', 'consumidor_final', '448', 'Efectivo', '2025-09-12 22:02:00.909033');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rpos_products`
--

CREATE TABLE `rpos_products` (
  `prod_id` varchar(200) NOT NULL,
  `prod_code` varchar(200) NOT NULL,
  `prod_name` varchar(200) NOT NULL,
  `prod_img` varchar(200) NOT NULL,
  `prod_desc` longtext NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `tipo` enum('Comida','Bebida','Postre') DEFAULT NULL,
  `prod_price` varchar(200) NOT NULL,
  `created_at` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rpos_products`
--

INSERT INTO `rpos_products` (`prod_id`, `prod_code`, `prod_name`, `prod_img`, `prod_desc`, `categoria_id`, `tipo`, `prod_price`, `created_at`) VALUES
('0c4b5c0604', 'JRZN-9518', 'Spaghetti Bolognese', 'spaghetti_bolognese.jpg', 'Spaghetti bolognese consists of spaghetti (long strings of pasta) with an Italian ragÃ¹ (meat sauce) made with minced beef, bacon and tomatoes, served with Parmesan cheese. Spaghetti bolognese is one of the most popular pasta dishes eaten outside of Italy.', 1, 'Comida', '15', '2025-08-22 00:06:18.530150'),
('14c7b6370e', 'QZHM-0391', 'Reuben Sandwich', 'reubensandwich.jpg', 'The Reuben sandwich is a North American grilled sandwich composed of corned beef, Swiss cheese, sauerkraut, and Thousand Island dressing or Russian dressing, grilled between slices of rye bread. It is associated with kosher-style delicatessens, but is not kosher because it combines meat and cheese.', 1, 'Comida', '8', '2025-08-22 00:25:17.730502'),
('1e0fa41eee', 'ICFU-1406', 'Submarine Sandwich', 'submarine_sndwh.jpg', 'A submarine sandwich, commonly known as a sub, hoagie, hero, Italian, grinder, wedge, or a spuckie, is a type of American cold or hot sandwich made from a cylindrical bread roll split lengthwise and filled with meats, cheeses, vegetables, and condiments. It has many different names.', 1, 'Comida', '250', '2025-08-22 00:25:17.730502'),
('2b976e49a0', 'CEWV-9438', 'Cheeseburger', 'cheeseburgers.jpg', 'A cheeseburger is a hamburger topped with cheese. Traditionally, the slice of cheese is placed on top of the meat patty. The cheese is usually added to the cooking hamburger patty shortly before serving, which allows the cheese to melt. Cheeseburgers can include variations in structure, ingredients and composition.', 1, 'Comida', '350', '2025-08-22 00:06:18.530150'),
('2fdec9bdfb', 'UJAK-9614', 'Jambalaya', 'Jambalaya.jpg', 'Jambalaya is an American Creole and Cajun rice dish of French, African, and Spanish influence, consisting mainly of meat and vegetables mixed with rice.', 1, 'Comida', '9', '2025-08-22 00:25:17.730502'),
('3adfdee116', 'HIPF-5346', 'Enchiladas', 'enchiladas.jpg', 'An enchilada is a corn tortilla rolled around a filling and covered with a savory sauce. Originally from Mexican cuisine, enchiladas can be filled with various ingredients, including meats, cheese, beans, potatoes, vegetables, or combinations', 1, 'Comida', '10', '2025-08-22 00:06:18.530150'),
('3d19e0bf27', 'EMBH-6714', 'Cincinnati Chili', 'cincinnatichili.jpg', 'Cincinnati chili is a Mediterranean-spiced meat sauce used as a topping for spaghetti or hot dogs; both dishes were developed by immigrant restaurateurs in the 1920s. In 2013, Smithsonian named one local chili parlor one of the \"20 Most Iconic Food Destinations in America\".', 1, 'Comida', '900', '2025-08-22 00:06:18.530150'),
('43033bf27e', 'VJMN-2843', 'coca cola', 'coca-cola-background-5hygk24b722a1642.jpg', 'coca cola 20 oz', 2, 'Bebida', '50', '2025-08-22 00:25:17.730502'),
('5d66c79953', 'GOEW-9248', 'Cheese Curd', 'cheesecurd.jpg', 'Cheese curds are moist pieces of curdled milk, eaten either alone or as a snack, or used in prepared dishes. These are chiefly found in Quebec, in the dish poutine, throughout Canada, and in the northeastern, midwestern, mountain, and Pacific Northwestern United States, especially in Wisconsin and Minnesota.', 1, 'Comida', '650', '2025-08-22 00:06:18.530150'),
('5e0e4333cf', 'ZUKQ-2137', 'POLLO', '5e0e4333cf.png', 'SDSDSDSDSDS', 1, 'Comida', '350', '2025-08-22 00:25:17.730502'),
('6ff923966b', 'RNLT-9215', 'cafe ejemplo', 'coffe-2400874_1280.jpg', 'scscsc', 2, 'Bebida', '35', '2025-08-22 01:13:33.641524'),
('716b9e1c97', 'RJXG-7816', 'canada dry soda', '2131595-A_5.jpg', 'soda amarga 20 oz', 2, 'Bebida', '35', '2025-08-22 00:25:17.730502'),
('7cbb6d6373', 'XPRK-2806', 'pasta aurora', '', 'fvfvfvfvf', 1, 'Comida', '550', '2025-08-22 00:25:17.730502'),
('7ff7f41dca', 'GWDA-5273', 'jugo naturales', 'Jugos-21.jpg', 'fgdrgdrgrdgrgrgrg', 2, 'Bebida', '75', '2025-08-22 01:12:21.865741'),
('826e6f687f', 'AYFW-2683', 'Margherita Pizza', 'margherita-pizza0.jpg', 'Pizza margherita, as the Italians call it, is a simple pizza hailing from Naples. When done right, margherita pizza features a bubbly crust, crushed San Marzano tomato sauce, fresh mozzarella and basil, a drizzle of olive oil, and a sprinkle of salt.', 1, 'Comida', '12', '2025-08-22 00:06:18.530150'),
('97972e8d63', 'CVWJ-6492', 'Irish Coffee', 'irishcoffee.jpg', 'Irish coffee is a caffeinated alcoholic drink consisting of Irish whiskey, hot coffee, and sugar, stirred, and topped with cream The coffee is drunk through the cream', 2, 'Bebida', '11', '2025-08-22 00:06:18.530150'),
('a287b155f9', 'TFRN-1703', 'Carbonara', '68a5c634a9567.png', 'dcjnsdsnvjildnvnsjlknvklsd', 1, 'Comida', '250', '2025-08-22 00:25:17.730502'),
('a419f2ef1c', 'EPNX-3728', 'Chicken Nugget', 'chicnuggets.jpeg', 'A chicken nugget is a food product consisting of a small piece of deboned chicken meat that is breaded or battered, then deep-fried or baked. Invented in the 1950s, chicken nuggets have become a very popular fast food restaurant item, as well as widely sold frozen for home use', 4, 'Comida', '200', '2025-08-22 00:06:18.530150'),
('a5931158fe', 'ELQN-5204', 'Pulled Pork', 'pulledprk.jpeg', 'Pulled pork is an American barbecue dish, more specifically a dish of the Southern U.S., based on shredded barbecued pork shoulder. It is typically slow-smoked over wood; indoor variations use a slow cooker. The meat is then shredded manually and mixed with a sauce', 4, 'Comida', '8', '2025-08-22 00:06:18.530150'),
('b2f9c250fd', 'XNWR-2768', 'Strawberry Rhubarb Pie', 'rhuharbpie.jpg', 'Rhubarb pie is a pie with a rhubarb filling. Popular in the UK, where rhubarb has been cultivated since the 1600s, and the leaf stalks eaten since the 1700s. Besides diced rhubarb, it almost always contains a large amount of sugar to balance the intense tartness of the plant', 3, 'Comida', '7', '2025-08-22 00:06:18.530150'),
('bd200ef837', 'HEIY-6034', 'Turkish Coffee', 'turkshcoffee.jpg', 'Turkish coffee is a style of coffee prepared in a cezve using very finely ground coffee beans without filtering.', 2, 'Bebida', '8', '2025-08-22 00:06:18.530150'),
('d57cd89073', 'ZGQW-9480', 'Country Fried Steak', 'country_fried_stk.jpg', 'Chicken-fried steak, also known as country-fried steak or CFS, is an American breaded cutlet dish consisting of a piece of beefsteak coated with seasoned flour and either deep-fried or pan-fried. It is sometimes associated with the Southern cuisine of the United States.', 1, 'Comida', '10', '2025-08-22 00:06:18.530150'),
('d99f541ed9', 'MWQA-1643', 'arepa', 'th.jpeg', 'arepa de arina blanca', 1, 'Comida', '75', '2025-09-12 00:50:12.151269'),
('d9aed17627', 'FIKD-9703', 'Crab Cake', 'crabcakes.jpg', 'A crab cake is a variety of fishcake that is popular in the United States. It is composed of crab meat and various other ingredients, such as bread crumbs, mayonnaise, mustard, eggs, and seasonings. The cake is then sautÃ©ed, baked, grilled, deep fried, or broiled.', 4, 'Comida', '16', '2025-08-22 00:06:18.530150'),
('e2195f8190', 'HKCR-2178', 'Carbonara', 'carbonaraimgre.jpg', 'Carbonara is an Italian pasta dish from Rome made with eggs, hard cheese, cured pork, and black pepper. The dish arrived at its modern form, with its current name, in the middle of the 20th century. The cheese is usually Pecorino Romano, Parmigiano-Reggiano, or a combination of the two.', 1, 'Comida', '350', '2025-08-22 00:06:18.530150'),
('e2af35d095', 'IDLC-7819', 'Pepperoni Pizza', 'peperopizza.jpg', 'Pepperoni is an American variety of spicy salami made from cured pork and beef seasoned with paprika or other chili pepper. Prior to cooking, pepperoni is characteristically soft, slightly smoky, and bright red. Thinly sliced pepperoni is one of the most popular pizza toppings in American pizzerias.', 1, 'Comida', '7', '2025-08-22 00:25:17.730502'),
('e769e274a3', 'AHRW-3894', 'Frappuccino', 'frappuccino.jpg', 'Frappuccino is a line of blended iced coffee drinks sold by Starbucks. It consists of coffee or crÃ¨me base, blended with ice and ingredients such as flavored syrups and usually topped with whipped cream and or spices.', 2, 'Bebida', '3', '2025-08-22 00:06:18.530150'),
('ec18c5a4f0', 'PQFV-7049', 'Corn Dogs', 'corndog.jpg', 'A corn dog is a sausage on a stick that has been coated in a thick layer of cornmeal batter and deep fried. It originated in the United States and is commonly found in American cuisine', 4, 'Comida', '4', '2025-08-22 00:25:17.730502'),
('f4ce3927bf', 'EAHD-1980', 'Hot Dog', 'hotdog0.jpg', 'A hot dog is a food consisting of a grilled or steamed sausage served in the slit of a partially sliced bun. The term hot dog can also refer to the sausage itself. The sausage used is a wiener or a frankfurter. The names of these sausages also commonly refer to their assembled dish.', 1, 'Comida', '400', '2025-08-22 00:25:17.730502'),
('f9c2770a32', 'YXLA-2603', 'Whipped Milk Shake', 'milkshake.jpeg', 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra quis, feugiat a, tellus. Phasellus viverra nulla ut metus varius laoreet. Quisque rutrum. Aenean imperdiet. Etiam ultricies nisi vel augue. Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus. Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero, sit amet adipiscing sem neque sed ipsum. Nam quam nunc, blandit vel, luctus pulvinar, hendrerit id, lorem. Maecenas nec odio et ante tincidunt tempus. Donec vitae sapien ut libero venenatis faucibus. Nullam quis ante. Etiam sit amet orci eget eros faucibus tincidunt. Duis leo. Sed fringilla mauris sit amet nibh. Donec sodales sagittis magna. Sed consequat, leo eget bibendum sodales, augue velit cursus nunc,', 3, 'Comida', '8', '2025-09-20 13:38:14.292095'),
('fa278c009b', 'IAQV-6259', 'Sandwich rd', 'Captura de pantalla 2025-07-31 215108.png', 'khhll', 1, 'Comida', '350', '2025-08-22 00:25:17.730502');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rpos_reservas`
--

CREATE TABLE `rpos_reservas` (
  `reserva_id` varchar(200) NOT NULL,
  `mesa_id` varchar(200) NOT NULL,
  `customer_id` varchar(200) NOT NULL,
  `fecha_reserva` date NOT NULL,
  `hora_reserva` time NOT NULL,
  `num_personas` int(11) NOT NULL,
  `estado` enum('Confirmada','Cancelada','Completada') NOT NULL DEFAULT 'Confirmada',
  `notas` text DEFAULT NULL,
  `created_at` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rpos_secuenciales_comprobantes`
--

CREATE TABLE `rpos_secuenciales_comprobantes` (
  `id` int(11) NOT NULL,
  `tipo_comprobante` enum('B01','B02','B03','B04') NOT NULL,
  `prefijo` varchar(10) NOT NULL,
  `secuencial_actual` int(11) NOT NULL DEFAULT 0,
  `secuencial_final` int(11) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `estado` enum('Activo','Inactivo','Agotado') NOT NULL DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rpos_secuenciales_comprobantes`
--

INSERT INTO `rpos_secuenciales_comprobantes` (`id`, `tipo_comprobante`, `prefijo`, `secuencial_actual`, `secuencial_final`, `descripcion`, `estado`) VALUES
(1, 'B01', 'B01', 60, 300, 'Factura de Consumo - Ventas a clientes finales', 'Activo'),
(2, 'B02', 'B02', 104, 300, 'Factura con Crédito Fiscal - Ventas a empresas', 'Activo'),
(3, 'B03', 'B03', 0, 1002, 'Nota de Débito - Cargos adicionales', 'Activo'),
(4, 'B04', 'B04', 0, 1002, 'Nota de Crédito - Devoluciones o ajustes', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rpos_staff`
--

CREATE TABLE `rpos_staff` (
  `staff_id` int(20) NOT NULL,
  `staff_name` varchar(200) NOT NULL,
  `staff_number` varchar(200) NOT NULL,
  `staff_email` varchar(200) NOT NULL,
  `staff_password` varchar(200) NOT NULL,
  `created_at` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  `rol` enum('Mesero','Cocinero','Bartender','Delivery','Cajero','Administrador') DEFAULT 'Mesero',
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `estado` enum('Activo','Inactivo') DEFAULT 'Activo',
  `activation_key` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rpos_staff`
--

INSERT INTO `rpos_staff` (`staff_id`, `staff_name`, `staff_number`, `staff_email`, `staff_password`, `created_at`, `rol`, `telefono`, `direccion`, `estado`, `activation_key`) VALUES
(4, 'jose jose', 'WIUH-9516', 'prueba@gmail.com', '$2y$10$taKKpAPsE4OEJ1jbGDOMcuer39uU8Yal1NLsCBNEJgD2YWsDwy4GO', '2025-08-23 22:05:31.499307', 'Mesero', '8299437780', 'boulevar 1ro de noviembre punta cana', 'Activo', NULL),
(5, 'ejempplo', 'PRHS-7315', 'ejemplo@mail.com', '$2y$10$GJvO7DctfK/HeA3nLthTFOFBvsTdIAXzbZTR8VaYtT068eD1XQ4AC', '2025-08-23 22:47:46.933630', 'Delivery', '8299437780', 'boulevar 1ro de noviembre punta cana1', 'Activo', NULL),
(7, 'Empleado de prueba ', 'STXL-5073', 'ejemplo3@gmail.com', '$2y$10$aMoXrXauAjlnDO2NvUF73e3Dot69yAiZBxPxVSR1MthLnhhDDRc0W', '2025-08-23 23:04:19.386330', 'Cajero', '8090000000', 'ejemplo de empleado ', 'Activo', NULL),
(8, 'juan rodriguez', 'GDIY-7836', 'admin@mail.com', '$2y$10$jS1s6poi2hsugQSAos197.G8GEXlRk2R2GzqLc1CnYX603PQ7d87C', '2025-09-12 00:18:30.486989', 'Administrador', '8299437780', 'boulevar 1ro de noviembre punta cana', 'Activo', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categoria_tipo`
--
ALTER TABLE `categoria_tipo`
  ADD PRIMARY KEY (`nombre_categoria`);

--
-- Indices de la tabla `rpos_admin`
--
ALTER TABLE `rpos_admin`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indices de la tabla `rpos_caja`
--
ALTER TABLE `rpos_caja`
  ADD PRIMARY KEY (`caja_id`);

--
-- Indices de la tabla `rpos_categorias_productos`
--
ALTER TABLE `rpos_categorias_productos`
  ADD PRIMARY KEY (`categoria_id`);

--
-- Indices de la tabla `rpos_configuracion`
--
ALTER TABLE `rpos_configuracion`
  ADD PRIMARY KEY (`config_id`);

--
-- Indices de la tabla `rpos_customers`
--
ALTER TABLE `rpos_customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `customer_id` (`customer_id`),
  ADD UNIQUE KEY `customer_id_2` (`customer_id`);

--
-- Indices de la tabla `rpos_delivery_items`
--
ALTER TABLE `rpos_delivery_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `delivery_id` (`delivery_id`);

--
-- Indices de la tabla `rpos_delivery_orders`
--
ALTER TABLE `rpos_delivery_orders`
  ADD PRIMARY KEY (`delivery_id`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_fecha_creacion` (`created_at`),
  ADD KEY `idx_cliente` (`cliente_id`),
  ADD KEY `fk_delivery_factura` (`factura_id`),
  ADD KEY `fk_delivery_caja` (`caja_id`);

--
-- Indices de la tabla `rpos_estados_bar`
--
ALTER TABLE `rpos_estados_bar`
  ADD PRIMARY KEY (`estado_id`),
  ADD KEY `orden_bar` (`order_id`);

--
-- Indices de la tabla `rpos_estados_cocina`
--
ALTER TABLE `rpos_estados_cocina`
  ADD PRIMARY KEY (`estado_id`),
  ADD KEY `orden_cocina` (`order_id`);

--
-- Indices de la tabla `rpos_facturas`
--
ALTER TABLE `rpos_facturas`
  ADD PRIMARY KEY (`factura_id`),
  ADD KEY `mesa_factura` (`mesa_id`);

--
-- Indices de la tabla `rpos_factura_items`
--
ALTER TABLE `rpos_factura_items`
  ADD PRIMARY KEY (`factura_item_id`),
  ADD KEY `factura_id` (`factura_id`);

--
-- Indices de la tabla `rpos_mesas`
--
ALTER TABLE `rpos_mesas`
  ADD PRIMARY KEY (`mesa_id`),
  ADD UNIQUE KEY `numero_mesa` (`numero_mesa`);

--
-- Indices de la tabla `rpos_movimientos_caja`
--
ALTER TABLE `rpos_movimientos_caja`
  ADD PRIMARY KEY (`movimiento_id`);

--
-- Indices de la tabla `rpos_movimientos_log`
--
ALTER TABLE `rpos_movimientos_log`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `rpos_notificaciones`
--
ALTER TABLE `rpos_notificaciones`
  ADD PRIMARY KEY (`notificacion_id`),
  ADD KEY `mesa_id` (`mesa_id`);

--
-- Indices de la tabla `rpos_ordenes_mesas`
--
ALTER TABLE `rpos_ordenes_mesas`
  ADD PRIMARY KEY (`orden_mesa_id`),
  ADD KEY `orden_mesa` (`order_id`),
  ADD KEY `mesa_orden` (`mesa_id`);

--
-- Indices de la tabla `rpos_orders`
--
ALTER TABLE `rpos_orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `CustomerOrder` (`customer_id`),
  ADD KEY `ProductOrder` (`prod_id`);

--
-- Indices de la tabla `rpos_order_items`
--
ALTER TABLE `rpos_order_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indices de la tabla `rpos_pass_resets`
--
ALTER TABLE `rpos_pass_resets`
  ADD PRIMARY KEY (`reset_id`);

--
-- Indices de la tabla `rpos_payments`
--
ALTER TABLE `rpos_payments`
  ADD PRIMARY KEY (`pay_id`),
  ADD KEY `order` (`order_code`);

--
-- Indices de la tabla `rpos_products`
--
ALTER TABLE `rpos_products`
  ADD PRIMARY KEY (`prod_id`),
  ADD UNIQUE KEY `prod_id` (`prod_id`),
  ADD KEY `producto_categoria` (`categoria_id`);

--
-- Indices de la tabla `rpos_reservas`
--
ALTER TABLE `rpos_reservas`
  ADD PRIMARY KEY (`reserva_id`),
  ADD KEY `mesa_reserva` (`mesa_id`),
  ADD KEY `cliente_reserva` (`customer_id`);

--
-- Indices de la tabla `rpos_secuenciales_comprobantes`
--
ALTER TABLE `rpos_secuenciales_comprobantes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `rpos_staff`
--
ALTER TABLE `rpos_staff`
  ADD PRIMARY KEY (`staff_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `rpos_categorias_productos`
--
ALTER TABLE `rpos_categorias_productos`
  MODIFY `categoria_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `rpos_configuracion`
--
ALTER TABLE `rpos_configuracion`
  MODIFY `config_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `rpos_delivery_items`
--
ALTER TABLE `rpos_delivery_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=155;

--
-- AUTO_INCREMENT de la tabla `rpos_movimientos_log`
--
ALTER TABLE `rpos_movimientos_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT de la tabla `rpos_order_items`
--
ALTER TABLE `rpos_order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=723523;

--
-- AUTO_INCREMENT de la tabla `rpos_pass_resets`
--
ALTER TABLE `rpos_pass_resets`
  MODIFY `reset_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `rpos_secuenciales_comprobantes`
--
ALTER TABLE `rpos_secuenciales_comprobantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `rpos_staff`
--
ALTER TABLE `rpos_staff`
  MODIFY `staff_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `rpos_estados_bar`
--
ALTER TABLE `rpos_estados_bar`
  ADD CONSTRAINT `orden_bar` FOREIGN KEY (`order_id`) REFERENCES `rpos_orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `rpos_estados_cocina`
--
ALTER TABLE `rpos_estados_cocina`
  ADD CONSTRAINT `orden_cocina` FOREIGN KEY (`order_id`) REFERENCES `rpos_orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `rpos_facturas`
--
ALTER TABLE `rpos_facturas`
  ADD CONSTRAINT `mesa_factura` FOREIGN KEY (`mesa_id`) REFERENCES `rpos_mesas` (`mesa_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `rpos_factura_items`
--
ALTER TABLE `rpos_factura_items`
  ADD CONSTRAINT `rpos_factura_items_ibfk_1` FOREIGN KEY (`factura_id`) REFERENCES `rpos_facturas` (`factura_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `rpos_notificaciones`
--
ALTER TABLE `rpos_notificaciones`
  ADD CONSTRAINT `rpos_notificaciones_ibfk_1` FOREIGN KEY (`mesa_id`) REFERENCES `rpos_mesas` (`mesa_id`);

--
-- Filtros para la tabla `rpos_ordenes_mesas`
--
ALTER TABLE `rpos_ordenes_mesas`
  ADD CONSTRAINT `mesa_orden` FOREIGN KEY (`mesa_id`) REFERENCES `rpos_mesas` (`mesa_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `orden_mesas` FOREIGN KEY (`order_id`) REFERENCES `rpos_orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `rpos_orders`
--
ALTER TABLE `rpos_orders`
  ADD CONSTRAINT `CustomerOrder` FOREIGN KEY (`customer_id`) REFERENCES `rpos_customers` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ProductOrder` FOREIGN KEY (`prod_id`) REFERENCES `rpos_products` (`prod_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `rpos_order_items`
--
ALTER TABLE `rpos_order_items`
  ADD CONSTRAINT `rpos_order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `rpos_orders` (`order_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `rpos_products`
--
ALTER TABLE `rpos_products`
  ADD CONSTRAINT `fk_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `rpos_categorias_productos` (`categoria_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `rpos_reservas`
--
ALTER TABLE `rpos_reservas`
  ADD CONSTRAINT `cliente_reserva` FOREIGN KEY (`customer_id`) REFERENCES `rpos_customers` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `mesa_reserva` FOREIGN KEY (`mesa_id`) REFERENCES `rpos_mesas` (`mesa_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
