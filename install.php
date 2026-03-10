<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/config/database.php';

$pdo = getConnection();

function tableExists($pdo, $table) {
    try {
        $pdo->query("SELECT 1 FROM $table LIMIT 1");
        return true;
    } catch (Exception $e) {
        return false;
    }
}

$tablesExist = tableExists($pdo, 'tenants');

if (!$tablesExist) {
    echo "<h3>Instalando base de datos...</h3>";
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS `tenants` (
        id INT AUTO_INCREMENT PRIMARY KEY,
        slug VARCHAR(50) NOT NULL UNIQUE,
        nombre VARCHAR(150) NOT NULL,
        email VARCHAR(150) NOT NULL,
        logo VARCHAR(255),
        color_principal VARCHAR(7) DEFAULT '#1a73e8',
        activo TINYINT(1) DEFAULT 1,
        plan ENUM('free','basic','pro') DEFAULT 'free',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "- Tabla tenants creada<br>";
    
    try { $pdo->exec("ALTER TABLE remiseros ADD COLUMN tenant_id INT NOT NULL DEFAULT 1"); } catch(Exception $e) {}
    try { $pdo->exec("ALTER TABLE pasajeros ADD COLUMN tenant_id INT NOT NULL DEFAULT 1"); } catch(Exception $e) {}
    try { $pdo->exec("ALTER TABLE viajes ADD COLUMN tenant_id INT NOT NULL DEFAULT 1"); } catch(Exception $e) {}
    try { $pdo->exec("ALTER TABLE notificaciones ADD COLUMN tenant_id INT NOT NULL DEFAULT 1"); } catch(Exception $e) {}
    echo "- Columnas tenant_id agregadas<br>";
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS super_admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(150),
        activo TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "- Tabla super_admins creada<br>";
    
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT IGNORE INTO tenants (slug, nombre, email, color_principal, activo, plan) VALUES ('demo', 'Remisería Demo', 'admin@demo.com', '#1a73e8', 1, 'pro')");
    $pdo->exec("INSERT IGNORE INTO super_admins (username, password, email, activo) VALUES ('superadmin', '$password', 'superadmin@remiseria.com', 1)");
    
    $passwordDemo = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT IGNORE INTO remiseros (tenant_id, nombre, username, password, rol, activo) VALUES (1, 'Admin Demo', 'admin', '$passwordDemo', 'admin', 1)");
    echo "- Usuario demo creado (admin / admin123)<br>";
    
    echo "<h3 style='color:green'>¡Instalación completada!</h3>";
    echo "<p><a href='/'>Ir al inicio</a></p>";
    exit;
}

echo "La base de datos ya está instalada. <a href='/'>Ir al inicio</a>";
