-- Base de datos para Sistema de Remiseria

CREATE DATABASE IF NOT EXISTS remiseria_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE remiseria_db;

-- Tabla remiseros
CREATE TABLE IF NOT EXISTS remiseros (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    activo TINYINT(1) DEFAULT 1,
    rol ENUM('admin', 'remisero') DEFAULT 'remisero',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla pasajeros
CREATE TABLE IF NOT EXISTS pasajeros (
    id INT PRIMARY KEY AUTO_INCREMENT,
    apellido VARCHAR(100) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    direccion VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla viajes
CREATE TABLE IF NOT EXISTS viajes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_remisero INT NOT NULL,
    id_pasajero INT,
    tipo ENUM('local', 'larga_distancia') NOT NULL,
    origen VARCHAR(255) NOT NULL,
    destino VARCHAR(255) NOT NULL,
    fecha_hora_solicitado DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_hora_viaje DATETIME,
    estado ENUM('buscando', 'en_curso', 'completado', 'cancelado') DEFAULT 'buscando',
    observaciones TEXT,
    monto DECIMAL(10,2) DEFAULT 0,
    metodo_pago ENUM('efectivo', 'transferencia', 'pendiente') DEFAULT 'pendiente',
    fecha_pago DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_remisero) REFERENCES remiseros(id) ON DELETE CASCADE,
    FOREIGN KEY (id_pasajero) REFERENCES pasajeros(id) ON DELETE SET NULL
);

-- Insertar usuario admin inicial (password: admin)
-- Password hasheado con: password_hash('admin', PASSWORD_DEFAULT)
INSERT INTO remiseros (nombre, username, password, telefono, rol) VALUES
('Administrador', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '', 'admin');
