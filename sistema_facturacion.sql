-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 31-08-2025 a las 03:05:38
-- Versión del servidor: 10.1.38-MariaDB
-- Versión de PHP: 7.3.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistema_facturacion`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clients`
--

CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `ruc` varchar(20) DEFAULT NULL,
  `address` text,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `action_type` enum('created','updated','deleted') DEFAULT 'created'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `clients`
--

INSERT INTO `clients` (`id`, `name`, `ruc`, `address`, `phone`, `email`, `created_at`, `updated_at`, `created_by`, `updated_by`, `action_type`) VALUES
(1, 'Cliente Ejemplo', '98765432109', 'Calle Secundaria 456', '963852741', 'cliente@ejemplo.com', '2025-07-24 20:03:58', '2025-08-24 19:59:22', 1, 1, 'created'),
(3, 'Juan PÃ©rez', 'V-30073550', '', '0412-3456653', 'JUANPEREZ@GMAIL.COM', '2025-08-31 00:34:25', NULL, 5, 5, 'created'),
(4, 'Roberto Gomez', 'V-21273044', '', '0424-3678864', 'robetgod@gmail.com', '2025-08-31 00:34:58', NULL, 5, 5, 'created'),
(5, 'Mauricio MejÃ­as', 'V-20099120', '', '0424-3129088', 'Maumejias@gmail.com', '2025-08-31 00:36:29', NULL, 5, 5, 'created');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(20) NOT NULL,
  `client_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `tax` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `user_id` int(11) NOT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `action_type` enum('created','updated','deleted') DEFAULT 'created',
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `invoices`
--

INSERT INTO `invoices` (`id`, `invoice_number`, `client_id`, `date`, `subtotal`, `tax`, `total`, `user_id`, `updated_by`, `action_type`, `status`, `created_at`, `updated_at`) VALUES
(1, 'FAC-00001', 1, '2025-07-24', '100.00', '18.00', '118.00', 1, NULL, 'created', 'pending', '2025-07-25 00:33:58', NULL),
(2, 'FACT-000002', 1, '2025-07-24', '87.98', '14.08', '102.06', 1, NULL, 'created', 'pending', '2025-07-25 02:30:03', NULL),
(3, 'FACT-000003', 1, '2025-07-25', '53.49', '8.56', '62.05', 1, NULL, 'created', 'pending', '2025-07-25 02:53:00', NULL),
(4, 'FACT-000004', 1, '2025-08-24', '0.50', '0.08', '0.58', 5, NULL, 'created', 'pending', '2025-08-24 20:32:09', NULL),
(5, 'FACT-000005', 1, '2025-08-24', '88.48', '14.16', '102.64', 5, NULL, 'created', 'pending', '2025-08-24 20:44:12', NULL),
(6, 'FACT-000006', 1, '2025-08-24', '60.00', '9.60', '69.60', 5, NULL, 'created', 'pending', '2025-08-24 21:39:25', NULL),
(7, 'FACT-000007', 1, '2025-08-24', '25.00', '4.00', '29.00', 5, NULL, 'created', 'pending', '2025-08-24 21:49:19', NULL),
(8, 'FACT-000008', 1, '2025-08-25', '2.00', '0.32', '2.32', 5, NULL, 'created', 'pending', '2025-08-24 22:01:29', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `invoice_details`
--

CREATE TABLE `invoice_details` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` decimal(10,3) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `invoice_details`
--

INSERT INTO `invoice_details` (`id`, `invoice_id`, `product_id`, `quantity`, `unit_price`, `total_price`) VALUES
(1, 1, 1, '2.000', '25.99', '51.98'),
(2, 1, 2, '1.000', '49.99', '49.99'),
(3, 2, 7, '1.000', '1.50', '1.50'),
(4, 2, 4, '1.000', '2.00', '2.00'),
(5, 2, 5, '1.000', '6.50', '6.50'),
(6, 2, 1, '1.000', '25.99', '25.99'),
(7, 2, 6, '1.000', '2.00', '2.00'),
(8, 2, 2, '1.000', '49.99', '49.99'),
(9, 3, 6, '1.000', '2.00', '2.00'),
(10, 3, 7, '1.000', '1.50', '1.50'),
(11, 3, 2, '1.000', '49.99', '49.99'),
(22, 4, 13, '1.000', '0.50', '0.50'),
(26, 5, 1, '1.000', '25.99', '25.99'),
(27, 5, 2, '1.000', '49.99', '49.99'),
(28, 5, 3, '1.000', '12.50', '12.50'),
(29, 6, 15, '12.000', '5.00', '60.00'),
(30, 7, 15, '5.000', '5.00', '25.00'),
(31, 8, 6, '1.000', '2.00', '2.00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `stock` decimal(10,3) NOT NULL DEFAULT '0.000',
  `stock_min` int(11) NOT NULL DEFAULT '5',
  `provider_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `action_type` enum('created','updated','deleted') DEFAULT 'created',
  `measure_unit` varchar(10) NOT NULL DEFAULT 'unidad',
  `min_stock` decimal(10,3) NOT NULL DEFAULT '5.000'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `products`
--

INSERT INTO `products` (`id`, `code`, `description`, `price`, `cost_price`, `stock`, `stock_min`, `provider_id`, `created_at`, `updated_at`, `created_by`, `updated_by`, `action_type`, `measure_unit`, `min_stock`) VALUES
(1, 'PROD001', 'Producto de Ejemplo 1', '25.99', '15.50', '97.000', 10, 1, '2025-07-25 00:33:58', '2025-08-24 20:44:12', NULL, 5, 'updated', 'unidad', '5.000'),
(2, 'PROD002', 'Producto de Ejemplo 2', '49.99', '30.00', '47.000', 5, 1, '2025-07-25 00:33:58', '2025-08-24 20:44:12', NULL, NULL, 'created', 'unidad', '5.000'),
(3, 'PROD003', 'Producto de Ejemplo 3', '12.50', '8.00', '1.000', 5, 1, '2025-07-25 00:33:58', '2025-08-24 20:44:12', NULL, 5, 'updated', 'unidad', '5.000'),
(4, '00001', 'Harina Pannnnn', '2.00', '0.00', '49.000', 5, 1, '2025-07-25 02:00:42', '2025-07-25 02:30:03', NULL, NULL, 'created', 'unidad', '5.000'),
(5, 'PROD-001', 'ACEITE VATEL', '6.50', NULL, '14.000', 5, 2, '2025-07-25 02:23:50', '2025-07-25 02:30:03', NULL, NULL, 'created', 'unidad', '5.000'),
(6, 'PROD-002', 'ARROZ MARY', '2.00', NULL, '46.000', 5, 2, '2025-07-25 02:24:13', '2025-08-24 22:01:29', NULL, 5, 'updated', 'unidad', '5.000'),
(7, 'PROD-003', 'HARINA KALY', '1.50', NULL, '48.000', 5, 2, '2025-07-25 02:24:35', '2025-07-25 02:53:00', NULL, NULL, 'created', 'unidad', '5.000'),
(13, 'PROD-004', 'VASOS PLASTICOS', '0.50', '0.00', '7.000', 5, 2, '2025-08-24 20:30:46', '2025-08-24 21:08:15', 5, 5, 'updated', 'unidad', '5.000'),
(15, 'PROD-006', 'MARISCOS', '5.00', '0.00', '43.000', 5, 1, '2025-08-24 21:35:24', '2025-08-24 21:49:19', 5, 5, 'updated', 'kg', '5.000'),
(16, 'PROD-007', 'SARDINA', '0.70', NULL, '50.000', 5, 5, '2025-08-31 00:11:36', NULL, 5, 5, 'created', 'kg', '5.000');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `providers`
--

CREATE TABLE `providers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `ruc` varchar(20) DEFAULT NULL,
  `address` text,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `action_type` enum('created','updated','deleted') DEFAULT 'created'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `providers`
--

INSERT INTO `providers` (`id`, `name`, `ruc`, `address`, `phone`, `email`, `created_at`, `updated_at`, `created_by`, `updated_by`, `action_type`) VALUES
(1, 'Proveedor Ejemplo', '12345678901', 'Av. Principal 123', '987654321', 'proveedor@ejemplo.com', '2025-07-24 20:03:58', '2025-08-24 19:59:22', 1, 1, 'created'),
(2, 'PESCADERÃA LUSAMAR C.A', 'J-45677221-5', 'Ocumare', '04128850522', 'PLUSAMAR15@GMAIL.COM', '2025-07-24 21:32:09', '2025-08-24 19:59:22', 1, 1, 'created'),
(4, 'Grupo Lamar, C.A.', 'J-08016155-6', '', '0412-2578529', 'jrincon@grupo-lamar.com', '2025-08-31 00:01:38', NULL, 5, 5, 'created'),
(5, 'Distribuidora Global Fish', 'J-29997663-7', '', '0412-4811767', 'globalfish2022@gmail.com', '2025-08-31 00:02:30', NULL, 5, 5, 'created'),
(6, 'Pescaven, C.A.', 'J-12345578-9', '', '0293-9350237', 'pescavenvnzla@gmail.com', '2025-08-31 00:03:05', NULL, 5, 5, 'created'),
(8, 'Distribuidora Facaven, C.A.', 'J-40093113-4', '', '+58 212-9511947', 'info@dfacaven.com', '2025-08-31 00:09:52', NULL, 5, 5, 'created');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2025-07-24 20:03:58'),
(2, 'bgreiso', '30073550', 'admin', '2025-07-24 22:27:24'),
(5, 'ggreiso', 'f865b53623b121fd34ee5426c792e5c33af8c227', 'admin', '2025-07-24 22:47:08');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ruc` (`ruc`),
  ADD KEY `fk_clients_created_by` (`created_by`),
  ADD KEY `fk_clients_updated_by` (`updated_by`);

--
-- Indices de la tabla `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_invoices_updated_by` (`updated_by`);

--
-- Indices de la tabla `invoice_details`
--
ALTER TABLE `invoice_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indices de la tabla `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `provider_id` (`provider_id`),
  ADD KEY `fk_products_created_by` (`created_by`),
  ADD KEY `fk_products_updated_by` (`updated_by`);

--
-- Indices de la tabla `providers`
--
ALTER TABLE `providers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ruc` (`ruc`),
  ADD KEY `fk_providers_created_by` (`created_by`),
  ADD KEY `fk_providers_updated_by` (`updated_by`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `invoice_details`
--
ALTER TABLE `invoice_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `providers`
--
ALTER TABLE `providers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `clients`
--
ALTER TABLE `clients`
  ADD CONSTRAINT `fk_clients_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_clients_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `fk_invoices_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  ADD CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `invoice_details`
--
ALTER TABLE `invoice_details`
  ADD CONSTRAINT `invoice_details_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoice_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Filtros para la tabla `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_products_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `providers`
--
ALTER TABLE `providers`
  ADD CONSTRAINT `fk_providers_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_providers_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
