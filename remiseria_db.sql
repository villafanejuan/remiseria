-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 06-03-2026 a las 03:39:00
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
-- Base de datos: `remiseria_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pasajeros`
--

CREATE TABLE `pasajeros` (
  `id` int(11) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pasajeros`
--

INSERT INTO `pasajeros` (`id`, `apellido`, `nombre`, `telefono`, `direccion`, `created_at`) VALUES
(4, 'juanjo', '', '123412', '343546', '2026-03-05 12:10:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `remiseros`
--

CREATE TABLE `remiseros` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `rol` enum('admin','remisero') DEFAULT 'remisero',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `remiseros`
--

INSERT INTO `remiseros` (`id`, `nombre`, `username`, `password`, `telefono`, `activo`, `rol`, `created_at`) VALUES
(1, 'Administrador', 'admin', '$2y$10$rmhjQ.HtiR1Kf6qApy7NVeUsAjQXIpUrg1KoKAw5uiEr5b9r6/qii', '', 1, 'admin', '2026-03-03 23:38:58'),
(2, 'user', 'user', '$2y$10$WEfFeRuejOK.72E.b1D9RuOuHiyntxF9ethtTODeuf6Lb5pLFj5Oq', '1232312', 1, 'remisero', '2026-03-04 20:14:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `viajes`
--

CREATE TABLE `viajes` (
  `id` int(11) NOT NULL,
  `id_remisero` int(11) NOT NULL,
  `id_pasajero` int(11) DEFAULT NULL,
  `tipo` enum('local','larga_distancia') NOT NULL,
  `origen` varchar(255) NOT NULL,
  `destino` varchar(255) NOT NULL,
  `fecha_hora_solicitado` datetime DEFAULT current_timestamp(),
  `fecha_hora_viaje` datetime DEFAULT NULL,
  `estado` enum('buscando','en_curso','completado','cancelado') DEFAULT 'buscando',
  `observaciones` text DEFAULT NULL,
  `monto` decimal(10,2) DEFAULT 0.00,
  `metodo_pago` enum('efectivo','transferencia','pendiente') DEFAULT 'pendiente',
  `fecha_pago` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `viajes`
--

INSERT INTO `viajes` (`id`, `id_remisero`, `id_pasajero`, `tipo`, `origen`, `destino`, `fecha_hora_solicitado`, `fecha_hora_viaje`, `estado`, `observaciones`, `monto`, `metodo_pago`, `fecha_pago`, `created_at`) VALUES
(9, 2, 4, 'local', 'callefalsa123', '-', '2026-03-05 09:10:59', NULL, 'completado', '', 3000.00, 'transferencia', '2026-03-05 09:15:00', '2026-03-05 12:10:59'),
(10, 2, 4, 'local', '-', '-', '2026-03-05 10:02:07', NULL, 'buscando', '', 0.00, 'pendiente', NULL, '2026-03-05 13:02:07');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `pasajeros`
--
ALTER TABLE `pasajeros`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `remiseros`
--
ALTER TABLE `remiseros`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indices de la tabla `viajes`
--
ALTER TABLE `viajes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_remisero` (`id_remisero`),
  ADD KEY `id_pasajero` (`id_pasajero`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `pasajeros`
--
ALTER TABLE `pasajeros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `remiseros`
--
ALTER TABLE `remiseros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `viajes`
--
ALTER TABLE `viajes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `viajes`
--
ALTER TABLE `viajes`
  ADD CONSTRAINT `viajes_ibfk_1` FOREIGN KEY (`id_remisero`) REFERENCES `remiseros` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `viajes_ibfk_2` FOREIGN KEY (`id_pasajero`) REFERENCES `pasajeros` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
