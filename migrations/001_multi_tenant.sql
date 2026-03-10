-- Multi-tenant SQL Schema
-- Agregar tenant_id a todas las tablas

-- Tabla de tenants (empresas)
CREATE TABLE IF NOT EXISTS `tenants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(50) NOT NULL COMMENT 'Identificador único para URL',
  `nombre` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `color_principal` varchar(7) DEFAULT '#1a73e8',
  `activo` tinyint(1) DEFAULT 1,
  `plan` enum('free','basic','pro') DEFAULT 'free',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Agregar tenant_id a remiseros
ALTER TABLE `remiseros` ADD COLUMN `tenant_id` int(11) NOT NULL AFTER `id`;
ALTER TABLE `remiseros` ADD KEY `tenant_id` (`tenant_id`);

-- Agregar tenant_id a pasajeros
ALTER TABLE `pasajeros` ADD COLUMN `tenant_id` int(11) NOT NULL AFTER `id`;
ALTER TABLE `pasajeros` ADD KEY `tenant_id` (`tenant_id`);

-- Agregar tenant_id a viajes
ALTER TABLE `viajes` ADD COLUMN `tenant_id` int(11) NOT NULL AFTER `id`;
ALTER TABLE `viajes` ADD KEY `tenant_id` (`tenant_id`);

-- Agregar tenant_id a notificaciones
ALTER TABLE `notificaciones` ADD COLUMN `tenant_id` int(11) NOT NULL AFTER `id_usuario`;
ALTER TABLE `notificaciones` ADD KEY `tenant_id` (`tenant_id`);
