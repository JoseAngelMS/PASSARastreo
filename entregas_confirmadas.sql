-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 05-06-2026 a las 05:51:24
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
-- Base de datos: `passa_rastreo`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entregas_confirmadas`
--

CREATE TABLE `entregas_confirmadas` (
  `id_confirmacion` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_repartidor` int(11) NOT NULL,
  `latitud_real` decimal(10,8) NOT NULL,
  `longitud_real` decimal(11,8) NOT NULL,
  `distancia_metros` decimal(8,2) DEFAULT NULL,
  `url_foto_evidencia` varchar(255) DEFAULT NULL,
  `notas_repartidor` text DEFAULT NULL,
  `fecha_hora_entrega` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `entregas_confirmadas`
--
ALTER TABLE `entregas_confirmadas`
  ADD PRIMARY KEY (`id_confirmacion`),
  ADD UNIQUE KEY `id_pedido` (`id_pedido`),
  ADD KEY `id_repartidor` (`id_repartidor`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `entregas_confirmadas`
--
ALTER TABLE `entregas_confirmadas`
  MODIFY `id_confirmacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `entregas_confirmadas`
--
ALTER TABLE `entregas_confirmadas`
  ADD CONSTRAINT `entregas_confirmadas_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`),
  ADD CONSTRAINT `entregas_confirmadas_ibfk_2` FOREIGN KEY (`id_repartidor`) REFERENCES `usuarios` (`id_usuario`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
