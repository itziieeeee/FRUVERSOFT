-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 09-03-2026 a las 21:58:39
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
-- Base de datos: `fruver`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido_paterno` varchar(50) DEFAULT NULL,
  `apellido_materno` varchar(50) NOT NULL,
  `rfc` varchar(13) DEFAULT NULL,
  `tipo_cliente` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `nombre`, `apellido_paterno`, `apellido_materno`, `rfc`, `tipo_cliente`) VALUES
(1, 'Kenia ', 'Flores', 'Garcia', 'KNO8SE1NIA', 'Menudeo'),
(2, 'Jose', 'Hernandez', 'Owo', 'HEGL960101', 'Mayoreo'),
(3, 'Carlos', 'juarez', '', 'HEGL960101', 'Mayoreo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedido`
--

CREATE TABLE `detalle_pedido` (
  `id_detalle` int(11) NOT NULL,
  `id_pedido` int(11) DEFAULT NULL,
  `id_producto` int(11) DEFAULT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `precio_producto` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direccion_cliente`
--

CREATE TABLE `direccion_cliente` (
  `id_direccion` int(11) NOT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `calle` varchar(100) DEFAULT NULL,
  `colonia` varchar(100) DEFAULT NULL,
  `numero` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entrada`
--

CREATE TABLE `entrada` (
  `id_entrada` int(11) NOT NULL,
  `id_producto` int(11) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `cantidad` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `merma`
--

CREATE TABLE `merma` (
  `id_merma` int(11) NOT NULL,
  `id_producto` int(11) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `cantidad` decimal(10,2) DEFAULT NULL,
  `motivo` text DEFAULT NULL,
  `producto_nombre` varchar(400) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido`
--

CREATE TABLE `pedido` (
  `id_pedido` int(11) NOT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `id_repartidor` int(11) DEFAULT NULL,
  `fecha_apertura` datetime DEFAULT current_timestamp(),
  `fecha_entrega` datetime DEFAULT NULL,
  `estatus` varchar(20) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `total` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `id_producto` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `unidad_compra` enum('caja','kilo','tonelada','pieza') DEFAULT 'kilo',
  `unidad_venta` enum('caja','kilo','pieza','tonelada') DEFAULT 'kilo',
  `categoria` enum('frutas','verduras','hortalizas','legumbres') DEFAULT 'frutas',
  `stock` decimal(10,3) DEFAULT 0.000,
  `precio_compra` decimal(10,2) DEFAULT 0.00,
  `precio_venta` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`id_producto`, `nombre`, `descripcion`, `unidad_compra`, `unidad_venta`, `categoria`, `stock`, `precio_compra`, `precio_venta`, `created_at`, `updated_at`) VALUES
(1, 'Lechuga', 'Hortaliza de hoja verde utilizada en ensaladas. Producto fresco y perecedero que debe mantenerse en refrigeración para conservar su calidad.', 'kilo', 'kilo', 'frutas', 0.000, 0.00, 0.00, '2026-03-06 07:05:45', '2026-03-06 07:05:45'),
(2, 'Sandia', 'Roja grande y verde jugosa', 'caja', 'kilo', 'frutas', 100.000, 20.00, 37.00, '2026-03-06 07:49:26', '2026-03-06 07:49:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `repartidor`
--

CREATE TABLE `repartidor` (
  `id_repartidor` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `apellido` varchar(50) DEFAULT NULL,
  `curp` varchar(18) DEFAULT NULL,
  `rfc` varchar(13) DEFAULT NULL,
  `domicilio` varchar(150) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `userregistrado`
--

CREATE TABLE `userregistrado` (
  `id` int(11) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `nusuario` varchar(50) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `apellido1` varchar(70) NOT NULL,
  `apellido2` varchar(70) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `userregistrado`
--

INSERT INTO `userregistrado` (`id`, `nombre_completo`, `nusuario`, `contrasena`, `created_at`, `apellido1`, `apellido2`) VALUES
(2, 'Yorgy', 'yoyolover', '$2y$10$cufBjj7LoPd4628KbFIk7.1FWV7UCmCBYjHIIZ2jRaz9a9fM9aUAm', '2026-02-28 05:58:32', '0', NULL),
(3, 'Hannie', 'Quesito9', '$2y$10$vvMIs6urSkZKGMJbtP7NxuZo.UvT79SGzs660xM.0rDsEEuzBf.NW', '2026-02-28 06:57:53', '0', NULL),
(4, 'Hannie Itzel', 'cocacola', '$2y$10$bP8hTUWut/5TYc.H070/rOwesJLzWnbSgLiCiOeHv892ZmA2pDy.u', '2026-02-28 07:03:46', '0', NULL),
(5, 'Hannie Itzel', 'cocacola', '$2y$10$kCNzuchBPpZijtEWOMZoiOPZm3k.ZhxP/usTkk5nlBTFxmNi58A16', '2026-02-28 07:04:14', '0', NULL),
(6, 'Belen', 'QUESITO11', '$2y$10$4AjEUjpPTP517bcBYzIQmuhHGdA1zXOdSTeTlP6E6l9tiPzsO3RmW', '2026-03-04 05:21:32', '0', NULL),
(7, 'max', 'thoormax', '$2y$10$WdLNBDAOcNovhB6g3DMrkO0O7jkskHo.F0CXhH3PoByye1kETJyiS', '2026-03-04 19:43:42', '0', NULL),
(8, 'Itzel', 'JJJJ', '$2y$10$jbgNh7bsW.YjbRtah5KnTe6QFEU/OcGlRJk/mkQEChiV.tAAqwt2y', '2026-03-05 14:12:54', '0', NULL),
(9, 'Belen', 'JJJJ', '$2y$10$DuXIjdXEEpjmJpVEV1aqzeoB1/7egSPyzf8wO1fDVMxB0UD5PgatW', '2026-03-05 15:27:19', '0', NULL),
(10, 'Hannie', 'HannBau', '$2y$10$lJxh3oNzRbSqalkEX6sH3Ofy06nCHIKC/wa9.WeB3d2dOrvrQ./YK', '2026-03-05 18:42:28', '0', 'Gonzalez'),
(11, 'Itzel', 'HITZIEE', '$2y$10$a86lsyLF.Ww8u6rMFeVs../kg.t1IvvDNKMjiDGqtcKUKOE7bLulG', '2026-03-05 18:56:08', 'Bautista', 'Gonzalez'),
(12, 'Kenia', 'HITZIEE', '$2y$10$q1eLzzFjbDVFqz.Xy9TDsufaxXW5Y52QEGcWpyAwKAL7njUYwbVlO', '2026-03-05 19:11:41', 'Bautista', 'Gonzalez'),
(13, 'Kenia', 'HITZIEE', '$2y$10$Hie8awnLEqFS8HhvbTio.OEZWFOPL/tm4zCVoTDT1xjCk8AGTw.XC', '2026-03-05 19:23:07', 'Bautista', 'Gonzalez'),
(14, 'Kenia', 'KeniaOs', '$2y$10$edX7bdh7W.oiF9.9bBRteeRmmcTE2dRjI4d3XzsR4roZn/7smQs5u', '2026-03-06 03:05:24', 'Guadalupe', 'Flores');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`);

--
-- Indices de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_pedido` (`id_pedido`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `direccion_cliente`
--
ALTER TABLE `direccion_cliente`
  ADD PRIMARY KEY (`id_direccion`),
  ADD KEY `id_cliente` (`id_cliente`);

--
-- Indices de la tabla `entrada`
--
ALTER TABLE `entrada`
  ADD PRIMARY KEY (`id_entrada`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `merma`
--
ALTER TABLE `merma`
  ADD PRIMARY KEY (`id_merma`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `id_cliente` (`id_cliente`),
  ADD KEY `id_repartidor` (`id_repartidor`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`id_producto`);

--
-- Indices de la tabla `repartidor`
--
ALTER TABLE `repartidor`
  ADD PRIMARY KEY (`id_repartidor`);

--
-- Indices de la tabla `userregistrado`
--
ALTER TABLE `userregistrado`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `direccion_cliente`
--
ALTER TABLE `direccion_cliente`
  MODIFY `id_direccion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `entrada`
--
ALTER TABLE `entrada`
  MODIFY `id_entrada` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `merma`
--
ALTER TABLE `merma`
  MODIFY `id_merma` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pedido`
--
ALTER TABLE `pedido`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `repartidor`
--
ALTER TABLE `repartidor`
  MODIFY `id_repartidor` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `userregistrado`
--
ALTER TABLE `userregistrado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD CONSTRAINT `detalle_pedido_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedido` (`id_pedido`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_pedido_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`);

--
-- Filtros para la tabla `direccion_cliente`
--
ALTER TABLE `direccion_cliente`
  ADD CONSTRAINT `direccion_cliente_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON DELETE CASCADE;

--
-- Filtros para la tabla `entrada`
--
ALTER TABLE `entrada`
  ADD CONSTRAINT `entrada_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`);

--
-- Filtros para la tabla `merma`
--
ALTER TABLE `merma`
  ADD CONSTRAINT `merma_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`);

--
-- Filtros para la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD CONSTRAINT `pedido_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`),
  ADD CONSTRAINT `pedido_ibfk_2` FOREIGN KEY (`id_repartidor`) REFERENCES `repartidor` (`id_repartidor`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
