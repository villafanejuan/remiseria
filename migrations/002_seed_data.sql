-- Insertar tenant inicial
INSERT INTO `tenants` (`id`, `slug`, `nombre`, `email`, `color_principal`, `activo`, `plan`) VALUES
(1, 'demo', 'Remisería Demo', 'admin@demo.com', '#1a73e8', 1, 'pro');

-- Actualizar remiseros con tenant_id
UPDATE remiseros SET tenant_id = 1 WHERE tenant_id = 0 OR tenant_id IS NULL;

-- Agregar constraint para que no haya registros sin tenant_id
ALTER TABLE `remiseros` MODIFY COLUMN `tenant_id` int(11) NOT NULL;
ALTER TABLE `pasajeros` MODIFY COLUMN `tenant_id` int(11) NOT NULL;
ALTER TABLE `viajes` MODIFY COLUMN `tenant_id` int(11) NOT NULL;
ALTER TABLE `notificaciones` MODIFY COLUMN `tenant_id` int(11) NOT NULL;

-- Tabla para super-admin (gestión de tenants)
CREATE TABLE IF NOT EXISTS `super_admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(150) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insertar super-admin por defecto (password: admin123)
INSERT INTO `super_admins` (`id`, `username`, `password`, `email`, `activo`) VALUES
(1, 'superadmin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin@remiseria.com', 1);
