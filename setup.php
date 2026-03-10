<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/database.php';

$pdo = getConnection();

echo "<h2>Configurando base de datos...</h2>";

try {
    $pdo->exec("ALTER TABLE remiseros ADD COLUMN tenant_id INT NOT NULL DEFAULT 1");
    echo "- Columna tenant_id en remiseros: OK<br>";
} catch (Exception $e) { echo "- remiseros: " . $e->getMessage() . "<br>"; }

try {
    $pdo->exec("ALTER TABLE pasajeros ADD COLUMN tenant_id INT NOT NULL DEFAULT 1");
    echo "- Columna tenant_id en pasajeros: OK<br>";
} catch (Exception $e) { echo "- pasajeros: " . $e->getMessage() . "<br>"; }

try {
    $pdo->exec("ALTER TABLE viajes ADD COLUMN tenant_id INT NOT NULL DEFAULT 1");
    echo "- Columna tenant_id en viajes: OK<br>";
} catch (Exception $e) { echo "- viajes: " . $e->getMessage() . "<br>"; }

try {
    $pdo->exec("ALTER TABLE notificaciones ADD COLUMN tenant_id INT NOT NULL DEFAULT 1");
    echo "- Columna tenant_id en notificaciones: OK<br>";
} catch (Exception $e) { echo "- notificaciones: " . $e->getMessage() . "<br>"; }

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS tenants (
        id INT AUTO_INCREMENT PRIMARY KEY,
        slug VARCHAR(50) NOT NULL UNIQUE,
        nombre VARCHAR(150) NOT NULL,
        email VARCHAR(150),
        color_principal VARCHAR(7) DEFAULT '#1a73e8',
        activo TINYINT(1) DEFAULT 1,
        plan ENUM('free','basic','pro') DEFAULT 'free',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "- Tabla tenants: OK<br>";
} catch (Exception $e) { echo "- tenants: " . $e->getMessage() . "<br>"; }

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS super_admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(150),
        activo TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "- Tabla super_admins: OK<br>";
} catch (Exception $e) { echo "- super_admins: " . $e->getMessage() . "<br>"; }

$password = password_hash('admin123', PASSWORD_DEFAULT);
$pdo->exec("INSERT IGNORE INTO tenants (slug, nombre, email, color_principal, activo, plan) VALUES ('demo', 'Remisería Demo', 'admin@demo.com', '#1a73e8', 1, 'pro')");
$pdo->exec("INSERT IGNORE INTO super_admins (username, password, email, activo) VALUES ('superadmin', '$password', 'superadmin@remiseria.com', 1)");
$pdo->exec("INSERT IGNORE INTO remiseros (tenant_id, nombre, username, password, rol, activo) VALUES (1, 'Admin Demo', 'admin', '$password', 'admin', 1)");

echo "<h3 style='color:green'>¡Listo!</h3>";
echo "<p><b>Credenciales:</b></p>";
echo "- Super Admin: superadmin / admin123<br>";
echo "- Tenant Demo: admin / admin123<br>";
echo "<br><a href='http://localhost/remiseria/'>Ir al login</a>";
